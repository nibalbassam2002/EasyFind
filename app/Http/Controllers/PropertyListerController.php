<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Category;
use App\Models\Governorate;
use App\Models\Area;
use App\Models\Subscription; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Arr; 
use Illuminate\Support\Facades\DB;

class PropertyListerController extends Controller
{
     private function getActiveSubscriptionForCurrentUser()
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }
        // جلب الاشتراك النشط مع تحميل الخطة (وميزاتها إذا كانت معرفة في $with بموديل Plan)
        return $user->activeSubscription()->with('plan')->first();
    }

    public function index()
    {
        $user = Auth::user();
        // لا حاجة لجلب الاشتراك هنا إذا كانت الداشبورد الرئيسية هي التي تعرض معلومات الاشتراك
        // ولكن إذا كانت هذه الصفحة هي الداشبورد الأساسية للمعلن, يمكنك جلب الاشتراك وتمريره
        $activeSubscription = $this->getActiveSubscriptionForCurrentUser();

        $properties = Property::where('user_id', $user->id)
                              ->with('listarea', 'category', 'subCategory')
                              ->latest()
                              ->paginate(10);

        // مرر الاشتراك للـ view ليتمكن من عرض معلومات الاشتراك أو تقييد زر الإضافة
        return view('dashboard.property_lister.index', compact('properties', 'activeSubscription'));
    }


    public function create()
    {
        $user = Auth::user();
        $activeSubscription = $this->getActiveSubscriptionForCurrentUser();

        if (!$activeSubscription || !$activeSubscription->plan) {
            return redirect()->route('frontend.pricing')
                             ->with('error', 'You need an active subscription to list properties. Please choose a plan.');
        }

        // جلب ميزات الخطة من الاشتراك (إما من metadata أو من علاقة plan)
        // الأفضل الاعتماد على $activeSubscription->plan->features إذا كان plan مُحمل
        $planFeatures = $activeSubscription->plan->features ?? ($activeSubscription->metadata ?? []);
        $maxProperties = $planFeatures['max_properties'] ?? 0;

        if ($maxProperties > 0 && $activeSubscription->properties_listed_count >= $maxProperties) {
             return redirect()->route('lister.properties.index') // أو dashboard.index
                             ->with('error', "You have reached your limit of {$maxProperties} properties for the {$activeSubscription->plan->name} plan. Please upgrade your plan to add more.");
        }

        $categoriesQuery = Category::whereNull('parent_id')->orderBy('name');
        if (isset($planFeatures['allowed_types'])) {
            $allowedTypes = array_map('strtolower', (array)$planFeatures['allowed_types']);
            if (!in_array('all', $allowedTypes) && !empty($allowedTypes)) {
                // افترض أن لديك عمود 'slug' أو 'name' في categories للمقارنة
                // وإذا كانت allowed_types هي IDs, غير الاستعلام
                $categoriesQuery->whereIn(DB::raw('LOWER(slug)'), $allowedTypes); // أو LOWER(name)
            }
        } else {
            // إذا لم يتم تحديد allowed_types, قد يعني عدم السماح بأي شيء أو خطأ في الإعدادات
            return redirect()->route('lister.properties.index')
                             ->with('error', 'Property type restrictions are not properly configured for your plan.');
        }
        $categories = $categoriesQuery->get();

        if ($categories->isEmpty() && (isset($planFeatures['allowed_types']) && !in_array('all', array_map('strtolower', (array)$planFeatures['allowed_types'])))) {
             return redirect()->route('lister.properties.index')
                              ->with('error', 'No allowed property types found for your current plan based on plan settings.');
        }


        $subCategories = Category::whereNotNull('parent_id')->orderBy('name')->get();
        $governorates = Governorate::with('areas')->orderBy('name')->get();
        $purpose = ['rent', 'sale', 'lease'];
        $currencies = ['ILS', 'USD', 'JOD'];

        return view('dashboard.property_lister.create', compact('categories', 'subCategories', 'governorates', 'purpose', 'currencies', 'activeSubscription'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $activeSubscription = $this->getActiveSubscriptionForCurrentUser();

        if (!$activeSubscription || !$activeSubscription->plan) {
            return redirect()->back()->withInput()->with('error', 'No active subscription found. Cannot add property.');
        }

        $planFeatures = $activeSubscription->plan->features ?? ($activeSubscription->metadata ?? []);
        $maxProperties = $planFeatures['max_properties'] ?? 0;

        if ($maxProperties > 0 && $activeSubscription->properties_listed_count >= $maxProperties) {
            return redirect()->route('lister.properties.index') // أو dashboard.index
                             ->with('error', "Property limit reached ({$activeSubscription->properties_listed_count}/{$maxProperties}) for your plan ({$activeSubscription->plan->name}). Please upgrade.");
        }

        $selectedCategoryId = $request->input('category_id');
        $selectedCategory = Category::find($selectedCategoryId);

        if (!$selectedCategory) {
            return redirect()->back()->withInput()->with('error', 'Invalid property category selected.');
        }

        $allowedTypes = isset($planFeatures['allowed_types']) ? array_map('strtolower', (array)$planFeatures['allowed_types']) : [];
        $selectedCategorySlug = strtolower($selectedCategory->slug ?? $selectedCategory->name); // استخدم slug إذا كان متاحًا

        if (!in_array('all', $allowedTypes) && !empty($allowedTypes) && !in_array($selectedCategorySlug, $allowedTypes)) {
            return redirect()->back()->withInput()
                             ->with('error', "The property type '{$selectedCategory->name}' is not allowed under your current '{$activeSubscription->plan->name}' plan.");
        }

        $maxImagesPerProperty = $planFeatures['max_images_per_property'] ?? 5; // قيمة افتراضية إذا لم تكن محددة

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:categories,id',
            'sub_category_id' => 'required_if:category_id,'.config('app.commercial_category_id', 5).'|nullable|integer|exists:categories,id',
            'description' => 'required|string',
            'purpose' => 'required|in:rent,sale,lease',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|in:ILS,USD,JOD',
            //'governorate_id' => 'required|exists:governorates,id', // ليس ضروريًا إذا كان area_id كافيًا
            'area_id' => 'required|integer|exists:areas,id',
            'address' => 'required|string|max:255',
            'area' => 'required|integer|min:1',
            'land_area' => 'nullable|integer|min:0',
            'property_condition' => 'nullable|string|in:new,used,needs_renovation',
            'finishing_type' => 'nullable|string|in:full,semi,none',
            'rooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'floors' => 'nullable|integer|min:0',
            'apartment_floor_num' => 'nullable|integer|min:0',
            'view_type' => 'nullable|string|max:255',
            'land_type' => 'nullable|string|max:100',
            'commercial_type' => 'nullable|string|max:100',
            'commercial_purpose' => 'nullable|string|max:255',
            'tent_type' => 'nullable|string|max:100',
            'caravan_type' => 'nullable|string|max:100',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string|max:50',
            'additional_details' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'images' => "nullable|array|max:{$maxImagesPerProperty}",
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'video_url' => 'nullable|url|max:255',
        ]);

        $validatedData['user_id'] = $user->id;
        $validatedData['status'] = 'pending';
        $validatedData['code'] = 'PROP-' . date('Y') . '-' . Str::random(5);
        $validatedData['amenities'] = isset($validatedData['amenities']) ? json_encode($validatedData['amenities']) : null;

        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('properties_images', $imageName, 'public');
                $imagePaths[] = $path;
            }
            $validatedData['images'] = json_encode($imagePaths);
        } else {
            $validatedData['images'] = null;
        }

        $property = Property::create($validatedData);

        if ($property) {
            $activeSubscription->increment('properties_listed_count');
        }

        return redirect()->route('lister.properties.index')->with('success', 'Property submitted for review successfully!');
    }

public function edit(Property $property) // استخدام Route Model Binding
    {
        // 1. التحقق من أن المستخدم يملك هذا العقار أو أنه أدمن
        if ($property->user_id !== Auth::id() && Auth::user()->role != 'admin') {
            abort(403, 'Unauthorized action.');
        }

        // 2. شرط: يمكن التعديل فقط إذا كانت الحالة 'pending' (ما لم يكن المستخدم أدمن)
        if ($property->status !== 'pending' && Auth::user()->role != 'admin') {
            return redirect()->route('lister.properties.index')
                             ->with('error', 'This property cannot be edited as it is no longer pending review.');
        }
        

        $user = Auth::user();
        // استخدم دالة مساعدة من موديل User لجلب الاشتراك النشط مع الخطة
        $activeSubscription = $user->activeSubscriptionWithPlan();

        if (!$activeSubscription || !$activeSubscription->plan) {
            return redirect()->route('frontend.pricing')
                             ->with('error', 'An active subscription is required to manage properties.');
        }

        $planFeatures = $activeSubscription->plan->features ?? ($activeSubscription->metadata ?? []);

        // فلترة التصنيفات المسموح بها بناءً على الخطة الحالية
        $allowedTypesFromPlan = $planFeatures['allowed_types'] ?? [];
        if (!is_array($allowedTypesFromPlan)) {
            $allowedTypesFromPlan = $allowedTypesFromPlan ? [(string)$allowedTypesFromPlan] : [];
        }
        $allowedTypesFromPlan = array_map('strtolower', $allowedTypesFromPlan);

        $categoriesQuery = Category::whereNull('parent_id')->orderBy('name');
        if (!empty($allowedTypesFromPlan) && !in_array('all', $allowedTypesFromPlan)) {
            $categoriesQuery->whereIn(DB::raw('LOWER(slug)'), $allowedTypesFromPlan);
        }
        $categories = $categoriesQuery->get();

        $subCategories = Category::whereNotNull('parent_id')->orderBy('name')->get();
        $governorates = Governorate::with('areas')->orderBy('name')->get();
        $purpose = ['rent', 'sale', 'lease'];
        $currencies = ['ILS', 'USD', 'JOD'];
        $isFreePlan = ($activeSubscription->plan->price == 0.00);
        $maxImages = $planFeatures['max_images_per_property'] ?? 5;

        $currentImages = is_string($property->images) ? json_decode($property->images, true) : ($property->images ?? []);
        if (!is_array($currentImages)) $currentImages = [];

        return view('dashboard.property_lister.edit', compact(
            'property',
            'categories',
            'subCategories',
            'governorates',
            'purpose',
            'currencies',
            'activeSubscription', // قد لا تحتاجه مباشرة في _form إذا مررت isFreePlan و maxImages
            'isFreePlan',         // مهم للـ _form
            'maxImages',          // مهم للـ _form
            'currentImages'
        ));
    }
    // ▲▲▲ نهاية دالة edit ▲▲▲


    public function update(Request $request, Property $property)
    {
        // ... (كود دالة update كما هو لديك، مع التأكد من إضافة شرط عدم التحديث إذا لم تكن الحالة pending) ...
         // 1. التحقق من الملكية
        if ($property->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403);
        }

        // 2. شرط: يمكن التحديث فقط إذا كانت الحالة 'pending' (ما لم يكن المستخدم أدمن)
        if ($property->status !== 'pending' && !Auth::user()->hasRole('admin')) {
            return redirect()->route('lister.properties.index')
                            ->with('error', 'This property cannot be updated as it is no longer pending review.');
        }
    }
    public function destroy(Property $property)
    {
        $user = Auth::user();
        if ($property->user_id !== $user->id && !$user->hasRole('admin')) { // اسمح للمالك أو الأدمن فقط
            abort(403);
        }

        // حذف صور العقار
        if ($property->images) {
            $imagePaths = json_decode($property->images, true);
            if (is_array($imagePaths)) {
                foreach ($imagePaths as $imagePath) {
                    Storage::disk('public')->delete($imagePath);
                }
            }
        }
        $property->delete(); // الحذف الفعلي أو الناعم

        // إنقاص عداد العقارات في الاشتراك النشط للمستخدم
        $activeSubscription = $this->getActiveSubscriptionForCurrentUser();
        if ($activeSubscription && $activeSubscription->properties_listed_count > 0) {
            // تأكد أن العقار تم إنشاؤه ضمن فترة هذا الاشتراك (منطق أكثر تعقيدًا إذا كان هناك اشتراكات متعددة تاريخيًا)
            // للتبسيط الآن, سنفترض أنه ينتمي للاشتراك النشط الحالي
            $activeSubscription->decrement('properties_listed_count');
        }
        return redirect()->route('lister.properties.index')->with('success', 'Property deleted successfully.');
    }

    public function show(Property $property)
{
    // تأكد أن هذا العقار يخص المستخدم المسجل أو أن المستخدم أدمن
    if ($property->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
        abort(403, 'Unauthorized action.');
    }

    $property->load(['category', 'subCategory', 'listarea.governorate', 'user']); // تحميل العلاقات اللازمة

    // يمكنك تمرير بيانات إضافية إذا أردت، مثل حالة الاشتراك
    $activeSubscription = Auth::user()->activeSubscriptionWithPlan();

    return view('dashboard.property_lister.show', compact('property', 'activeSubscription'));
}
}