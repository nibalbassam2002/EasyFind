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
use Illuminate\Support\Facades\Log;
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

    public function index(Request $request)
    {
        $user = Auth::user();
        $activeSubscription = $this->getActiveSubscriptionForCurrentUser();

        $propertiesQuery = Property::where('user_id', $user->id)
                              ->with('listarea', 'category', 'subCategory');

    // فلترة حسب الحالة إذا كانت موجودة في الرابط
    if ($request->filled('status')) {
        $propertiesQuery->where('status', $request->input('status'));
    }

    // فلترة حسب الغرض (purpose) إذا كانت موجودة في الرابط
    // مثل: ?purpose=sale, ?purpose=rent
    if ($request->filled('purpose')) {
        $propertiesQuery->where('purpose', $request->input('purpose'));
    }


    $properties = $propertiesQuery->latest()->paginate(10);

        // مرر الاشتراك للـ view ليتمكن من عرض معلومات الاشتراك أو تقييد زر الإضافة
        return view('dashboard.property_lister.index', compact('properties', 'activeSubscription'));
    }


    public function create(Request $request)
    {
        $user = Auth::user();
        $activeSubscription = $this->getActiveSubscriptionForCurrentUser();

        if (!$activeSubscription || !$activeSubscription->plan) {
            return redirect()->route('frontend.pricing')
                             ->with('error', 'You need an active subscription to list properties. Please choose a plan.');
        }

        if (!$activeSubscription->isActive()) {
        return redirect()->route('frontend.pricing')
                         ->with('error', "Your '{$activeSubscription->plan->name}' plan has expired. Please renew or choose a new plan to continue.");
    }
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
        $purpose_options = ['rent', 'sale', 'lease'];
        $currencies = ['ILS', 'USD', 'JOD'];
        $selected_purpose = $request->input('purpose', '');

        return view('dashboard.property_lister.create', compact('categories', 'subCategories', 'governorates', 'purpose_options', 'currencies', 'activeSubscription', 'selected_purpose'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $activeSubscription = $this->getActiveSubscriptionForCurrentUser();

        if (!$activeSubscription || !$activeSubscription->plan) {
            return redirect()->back()->withInput()->with('error', 'No active subscription found. Cannot add property.');
        }
        if (!$activeSubscription->isActive()) {
        return redirect()->back()->withInput()->with('error', "Your subscription has expired. You cannot add new properties.");
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

public function edit(Property $property) 
{
    
    $user = Auth::user();

    // 1. التحقق من أن المستخدم يملك هذا العقار أو أنه أدمن
    if ($property->user_id !== $user->id && $user->role !== 'admin') { // تصحيح: استخدام $user->role
        Log::warning("PropertyListerController@edit: Unauthorized attempt to edit property ID {$property->id} by User ID {$user->id}.");
        abort(403, 'Unauthorized action to edit this property.');
    }

    // 2. شرط السماح بالتعديل: البائع يمكنه التعديل إذا كانت الحالة 'pending' أو 'rejected'. الأدمن يمكنه دائمًا.
    $allowedToEditStatusesByLister = ['pending', 'rejected'];
    if (!in_array(strtolower($property->status), $allowedToEditStatusesByLister) && $user->role !== 'admin') { // تصحيح: استخدام $user->role
        Log::warning("PropertyListerController@edit: Attempt to edit property ID {$property->id} with status '{$property->status}' by non-admin User ID {$user->id}. Redirecting.");
        return redirect()->route('lister.properties.index')
                         ->with('error', "This property (status: {$property->status}) cannot be edited by you at this time. Only pending or rejected properties can be updated by listers.");
    }
    
    // 3. جلب الاشتراك النشط وميزات الخطة
    $activeSubscription = $user->activeSubscriptionWithPlan(); // تأكد أن هذه الدالة موجودة في موديل User
    if (!$activeSubscription || !$activeSubscription->plan) {
        Log::warning("PropertyListerController@edit: User ID {$user->id} has no active subscription or plan. Redirecting to pricing for property ID {$property->id}.");
        return redirect()->route('frontend.pricing')
                         ->with('error', 'An active subscription is required to manage properties.');
    }
    if (!$activeSubscription->isActive()) { 
        return redirect()->route('frontend.pricing')
                         ->with('error', "Your '{$activeSubscription->plan->name}' plan has expired. Please renew or choose a new plan to continue.");
    }
    $planFeatures = $activeSubscription->plan->features ?? ($activeSubscription->metadata ?? []);

    // 4. فلترة التصنيفات المسموح بها بناءً على الخطة الحالية
    $allowedTypesFromPlan = $planFeatures['allowed_types'] ?? [];
    if (!is_array($allowedTypesFromPlan)) {
        $allowedTypesFromPlan = $allowedTypesFromPlan ? [(string)$allowedTypesFromPlan] : [];
    }
    $allowedTypesFromPlan = array_map('strtolower', $allowedTypesFromPlan);

    $categoriesQuery = Category::whereNull('parent_id')->orderBy('name');
    if (!empty($allowedTypesFromPlan) && !in_array('all', $allowedTypesFromPlan)) {
        $categoriesQuery->whereIn(DB::raw('LOWER(slug)'), $allowedTypesFromPlan); // تأكد من وجود عمود slug
    }
    $categories = $categoriesQuery->get();
    // يمكنك إضافة تحقق هنا إذا كانت $categories فارغة بعد الفلترة (إذا لم تكن 'all')

    // 5. جلب باقي البيانات اللازمة للنموذج
    $subCategories = Category::whereNotNull('parent_id')->orderBy('name')->get();
    $governorates = Governorate::with('areas')->orderBy('name')->get();
    $purpose = ['rent', 'sale', 'lease'];
    $currencies = ['ILS', 'USD', 'JOD'];
    $isFreePlan = ($activeSubscription->plan->price == 0.00);
    $maxImages = $planFeatures['max_images_per_property'] ?? 5;
    $availableAmenitiesForPlan = $planFeatures['available_amenities'] ?? array_keys($this->getAllPossibleAmenities()); // افترض وجود دالة تجلب كل المرافق الممكنة

    // جلب الصور الحالية للعقار
    $currentImages = is_string($property->images) ? json_decode($property->images, true) : ($property->images ?? []);
    if (!is_array($currentImages)) {
        $currentImages = [];
    }

    Log::info("PropertyListerController@edit: Preparing to show edit view for property ID {$property->id} for User ID {$user->id}.");
    return view('dashboard.property_lister.edit', compact(
        'property',
        'categories',
        'subCategories',
        'governorates',
        'purpose',   
        'currencies',
        'isFreePlan',
        'maxImages',
        'availableAmenitiesForPlan',
        'currentImages'
    ));
}

// دالة مساعدة مقترحة لوضعها في الكنترولر أو كـ helper
private function getAllPossibleAmenities()
{
    return [
        'elevator' => 'Elevator', 'parking' => 'Parking', 'pool' => 'Swimming Pool',
        'garden' => 'Garden', 'security' => 'Security', 'ac' => 'Air Conditioning',
        'main_road_access' => 'Main Road Access', 'electricity' => 'Electricity Available',
        'water' => 'Water Available', 'sewage' => 'Sewage Available'
    ];
}


public function update(Request $request, Property $property) // Route Model Binding
{
    $user = Auth::user();

    // 1. التحقق من الملكية أو إذا كان المستخدم أدمن
    if ($property->user_id !== $user->id && $user->role !== 'admin') {
        Log::warning("PropertyListerController@update: Unauthorized attempt to update property ID {$property->id} by User ID {$user->id}.");
        abort(403, 'You are not authorized to update this property.');
    }

    // 2. شرط السماح بالتحديث
    $allowedToEditStatusesByLister = ['pending', 'rejected'];
    if (!in_array(strtolower($property->status), $allowedToEditStatusesByLister) && $user->role !== 'admin') {
        Log::warning("PropertyListerController@update: Attempt to update property ID {$property->id} with status '{$property->status}' by non-admin User ID {$user->id}.");
        return redirect()->route('lister.properties.index')
                        ->with('error', "This property (status: {$property->status}) cannot be updated by you at this time. Only pending or rejected properties can be updated.");
    }

    // 3. جلب الاشتراك النشط وميزات الخطة
    $activeSubscription = $user->activeSubscriptionWithPlan();
    if (!$activeSubscription || !$activeSubscription->plan) {
        return redirect()->route('frontend.pricing')->with('error', 'An active subscription is required to manage properties.');
    }
    $planFeatures = $activeSubscription->plan->features ?? ($activeSubscription->metadata ?? []);
    $maxImagesPerProperty = $planFeatures['max_images_per_property'] ?? 5;

    // 4. التحقق من صحة البيانات المدخلة
    // (نفس قواعد التحقق الموجودة في دالة store لديك، مع التأكد من اسم حقل 'purpose')
    $validatedData = $request->validate([
        'title' => 'required|string|max:255',
        'category_id' => 'required|integer|exists:categories,id',
        'sub_category_id' => 'required_if:category_id,'.config('app.commercial_category_id', 5).'|nullable|integer|exists:categories,id',
        'description' => 'required|string',
        'purpose' => 'required|in:rent,sale,lease', // <--- تأكد أن اسم الحقل في النموذج هو 'purpose'
        'price' => 'required|numeric|min:0',
        'currency' => 'required|in:ILS,USD,JOD',
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
        'latitude' => ['nullable', 'numeric', 'between:-90,90'],
        'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        'images' => "nullable|array", // سيتم التحقق من العدد يدويًا
        'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'delete_images' => 'nullable|array',
        'delete_images.*' => 'string',
        'video_url' => 'nullable|url|max:255',
    ]);

    // 5. التحقق من نوع التصنيف المختار
    $selectedCategory = Category::find($validatedData['category_id']);
    if ($selectedCategory) {
        $allowedTypes = isset($planFeatures['allowed_types']) ? array_map('strtolower', (array)$planFeatures['allowed_types']) : [];
        $selectedCategorySlug = strtolower($selectedCategory->slug ?? $selectedCategory->name);
        if (!in_array('all', $allowedTypes) && !empty($allowedTypes) && !in_array($selectedCategorySlug, $allowedTypes)) {
            return redirect()->back()->withInput()->with('error', "The property type '{$selectedCategory->name}' is not allowed under your current '{$activeSubscription->plan->name}' plan.");
        }
    } else {
        return redirect()->back()->withInput()->with('error', 'Invalid property category selected.');
    }

    // 6. معالجة الصور
    $currentImagePathsFromDb = is_array($property->images) ? $property->images : (json_decode($property->images, true) ?? []);
    $imagesToDelete = $request->input('delete_images', []);
    $finalImagePaths = [];

    foreach ($currentImagePathsFromDb as $path) {
        if (!in_array($path, $imagesToDelete)) {
            $finalImagePaths[] = $path;
        } else {
            Storage::disk('public')->delete($path);
        }
    }

    if ($request->hasFile('images')) {
        if ((count($finalImagePaths) + count($request->file('images'))) > $maxImagesPerProperty) {
            return redirect()->back()->withInput()->with('error', "Image limit exceeded. Your plan allows up to {$maxImagesPerProperty} images in total.");
        }
        foreach ($request->file('images') as $image) {
            $imageName = time() . '_' . Str::random(5) . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('properties_images', $imageName, 'public');
            $finalImagePaths[] = $path;
        }
    }
    $validatedData['images'] = !empty($finalImagePaths) ? json_encode($finalImagePaths) : null;
    unset($validatedData['delete_images']); // لا نحتاج لحفظ هذا في قاعدة البيانات

    // 7. تحديث بيانات العقار
    $originalStatus = $property->status;
    $property->fill($validatedData);
    $property->amenities = isset($validatedData['amenities']) ? json_encode($validatedData['amenities']) : null;

    // 8. منطق إعادة التقديم للمراجعة
    $message = 'Property updated successfully.';
    if ($originalStatus === 'rejected') {
        $property->status = 'pending';
        $property->moderated_by = null;
        $property->moderated_at = null;
        $property->rejection_reason = null;
        $message = 'Property updated and resubmitted for review!';
        Log::info("Property ID {$property->id} (was 'rejected') resubmitted as 'pending' by User ID {$user->id}.");
    }
    // يمكنك إضافة منطق آخر هنا إذا قام الأدمن بتعديل عقار موافق عليه، هل يجب أن يبقى موافق عليه؟
    // أو إذا قام البائع بتعديل عقار موافق عليه (إذا سمحت بذلك)، هل يجب أن يعود لـ pending؟

    $property->save();

    Log::info("Property ID {$property->id} updated by User ID {$user->id}. New status: {$property->status}.");
    return redirect()->route('lister.properties.index')->with('success', $message);
}
    public function destroy(Property $property)
    {
        $user = Auth::user();
        if ($property->user_id !== $user->id && $user->role !== 'admin') { // اسمح للمالك أو الأدمن فقط
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
    if ($property->user_id !== Auth::id() && !Auth::user()->role == 'admin') {
        abort(403, 'Unauthorized action.');
    }

    $property->load(['category', 'subCategory', 'listarea.governorate', 'user']); // تحميل العلاقات اللازمة

    // يمكنك تمرير بيانات إضافية إذا أردت، مثل حالة الاشتراك
    $activeSubscription = Auth::user()->activeSubscriptionWithPlan();

    return view('dashboard.property_lister.show', compact('property', 'activeSubscription'));
}
public function featureProperty(Property $property)
{
    // 1. تحقق من الملكية والحالة
    if ($property->user_id !== Auth::id()) {
        abort(403, 'Unauthorized');
    }
    if ($property->status !== 'approved') {
        return redirect()->back()->with('error', 'Only approved properties can be featured.');
    }
    if ($property->is_featured) {
        return redirect()->back()->with('info', 'This property is already featured.');
    }

    // 2. تحقق من الاشتراك ورصيد التمييز
    $activeSubscription = $this->getActiveSubscriptionForCurrentUser();
    if (!$activeSubscription || !$activeSubscription->isActive()) {
        return redirect()->back()->with('error', 'You need an active subscription to feature properties.');
    }
    
    $planSlots = $activeSubscription->plan->features['featured_slots'] ?? 0;
    $usedSlots = $activeSubscription->featured_slots_used;

    if ($usedSlots >= $planSlots) {
        return redirect()->back()->with('error', 'You have used all your available featured slots. Please upgrade your plan.');
    }

    // 3. تنفيذ عملية التمييز
    DB::transaction(function () use ($property, $activeSubscription) {
        // تمييز العقار
        $property->update([
            'is_featured' => true,
            'featured_at' => now(),
        ]);
        // استهلاك فرصة من الرصيد
        $activeSubscription->increment('featured_slots_used');
    });

    return redirect()->back()->with('success', "'{$property->title}' has been successfully featured!");
}
}