<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Category; 
use App\Models\Governorate;
use App\Models\Area;    
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; 
class PropertyListerController extends Controller
{
    
    public function index()
    {
        // جلب فقط عقارات المستخدم المسجل دخوله، مع الترقيم
        $properties = Property::where('user_id', Auth::id())
                              ->with('area.governorate', 'category', 'subCategory') // <-- إلى هذا (لجلب المنطقة والمحافظة)
                              ->latest()
                              ->paginate(10);

          return view('dashboard.property_lister.index', compact('properties'));
    }

    // --- 2. عرض نموذج إضافة عقار جديد ---
    public function create()
    {
        // جلب البيانات اللازمة للنماذج (التصنيفات، المدن، إلخ)
        $categories = Category::whereNull('parent_id')->orderBy('name')->get(); // التصنيفات الرئيسية فقط
        $subCategories = Category::whereNotNull('parent_id')->orderBy('name')->get(); // التصنيفات الفرعية
        $governorates = Governorate::with('areas')->orderBy('name')->get();
        $purposes = ['rent', 'sale', 'lease']; // أغراض العقار
        $currencies = ['ILS', 'USD', 'JOD']; // العملات

        return view('dashboard.property_lister.create', compact('categories', 'subCategories', 'governorates', 'purposes', 'currencies'));
    }

    // --- 3. حفظ العقار الجديد ---
    public function store(Request $request)
    {
        // --- التحقق من صحة البيانات (مثال أولي، يجب توسيعه ليشمل كل الحقول) ---
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:categories,id',
            'sub_category_id' => 'required_if:category_id,4|nullable|integer|exists:categories,id', 
            'description' => 'required|string',
            'purpose' => 'required|in:rent,sale,lease',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|in:ILS,USD,JOD',
            'governorate_id' => 'required|exists:governorates,id',
            'area_id' => 'required|integer|exists:areas,id',
            'address' => 'required|string|max:255',
            'area' => 'required|integer|min:1',
            'rooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'floors' => 'nullable|integer|min:0',
            'land_type' => 'nullable|string|max:100', // يجب أن تكون هذه الحقول مطلوبة بناءً على التصنيف
            'tent_type' => 'nullable|string|max:100',
            'caravan_type' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255', // يمكنك استخدام validation خاص بالإحداثيات
            'images' => 'nullable|array|max:5', // السماح بـ 5 صور كحد أقصى (كمثال)
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // التحقق من كل صورة
            'video_url' => 'nullable|url|max:255',
        ]);

        // إضافة البيانات التي لم تأتِ من الفورم
        $validatedData['user_id'] = Auth::id(); // ربط العقار بالمستخدم الحالي
        $validatedData['status'] = 'pending'; // جعل الحالة الافتراضية "قيد المراجعة"
        // توليد كود فريد (مثال بسيط)
        $validatedData['code'] = 'PROP-' . date('Y') . '-' . Str::random(5); // يجب ضمان التفرد بشكل أفضل ربما

        // --- معالجة رفع الصور ---
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                // توليد اسم فريد للصورة وتخزينها
                $imageName = time() . '_' . $image->getClientOriginalName();
                // تحديد المسار داخل مجلد storage/app/public
                $path = $image->storeAs('properties_images', $imageName, 'public');
                $imagePaths[] = $path; // تخزين المسار النسبي
            }
            // تخزين مصفوفة المسارات كـ JSON في قاعدة البيانات
            $validatedData['images'] = json_encode($imagePaths);
        } else {
            $validatedData['images'] = null; // أو json_encode([])
        }


        // إنشاء العقار في قاعدة البيانات
        Property::create($validatedData);

        return redirect()->route('lister.properties.index')->with('success', 'Property submitted for review successfully!');
    }

    // --- 4. عرض تفاصيل عقار محدد (للمستخدم نفسه) ---
    public function show(Property $property)
    {
        // التحقق من أن المستخدم هو صاحب العقار
        if (Auth::id() !== $property->user_id) {
            abort(403, 'Unauthorized action.');
        }
        // جلب العلاقات اللازمة للعرض
        $property->load('area.governorate', 'category', 'subCategory');
        return view('dashboard.property_lister.show', compact('property')); // ستحتاج لإنشاء هذا الـ view
    }

    // --- 5. عرض نموذج تعديل عقار ---
    public function edit(Property $property)
    {
        // التحقق من أن المستخدم هو صاحب العقار
        if (Auth::id() !== $property->user_id) {
            abort(403, 'Unauthorized action.');
        }

        // جلب البيانات اللازمة للنماذج
        $categories = Category::whereNull('parent_id')->orderBy('name')->get();
        $subCategories = Category::whereNotNull('parent_id')->orderBy('name')->get();
        $governorates = Governorate::with('areas')->orderBy('name')->get();
        $purposes = ['rent', 'sale', 'lease'];
        $currencies = ['ILS', 'USD', 'JOD'];

        // فك ترميز مسارات الصور من JSON إلى مصفوفة لتسهيل التعامل معها في الـ view
        $currentImages = $property->images ? json_decode($property->images, true) : [];


        return view('dashboard.property_lister.edit', compact('property', 'categories', 'subCategories', 'governorates', 'purposes', 'currencies', 'currentImages'));
    }

    // --- 6. تحديث العقار ---
    public function update(Request $request, Property $property)
    {
        // التحقق من أن المستخدم هو صاحب العقار
        if (Auth::id() !== $property->user_id) {
            abort(403, 'Unauthorized action.');
        }

        // التحقق من صحة البيانات (مشابه لـ store ولكن قد تختلف بعض القواعد)
         $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:categories,id',
            'sub_category_id' => 'required_if:category_id,4|nullable|integer|exists:categories,id', 
            'description' => 'required|string',
            'purpose' => 'required|in:rent,sale,lease',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|in:ILS,USD,JOD',
            'area_id' => 'required|integer|exists:areas,id',
            'address' => 'required|string|max:255',
            'area' => 'required|integer|min:1',
            'rooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'floors' => 'nullable|integer|min:0',
            'land_type' => 'nullable|string|max:100',
            'tent_type' => 'nullable|string|max:100',
            'caravan_type' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'images' => 'nullable|array|max:5', // الحد الأقصى للصور الجديدة المرفوعة
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'video_url' => 'nullable|url|max:255',
             // (اختياري) إضافة حقل لحذف الصور القديمة
            'delete_images' => 'nullable|array', // مصفوفة بمسارات الصور المراد حذفها
        ]);

         // --- معالجة حذف الصور القديمة ---
         $currentImages = $property->images ? json_decode($property->images, true) : [];
         $imagesToDelete = $request->input('delete_images', []);
         $remainingImages = [];
         foreach ($currentImages as $imagePath) {
             if (in_array($imagePath, $imagesToDelete)) {
                 // حذف الصورة من التخزين
                 Storage::disk('public')->delete($imagePath);
             } else {
                 $remainingImages[] = $imagePath; // إضافة الصور المتبقية
             }
         }

         // --- معالجة رفع الصور الجديدة وإضافتها ---
         if ($request->hasFile('images')) {
             foreach ($request->file('images') as $image) {
                 $imageName = time() . '_' . $image->getClientOriginalName();
                 $path = $image->storeAs('properties_images', $imageName, 'public');
                 // التأكد من عدم تجاوز الحد الإجمالي للصور (مثلاً 5)
                 if (count($remainingImages) < 5) {
                    $remainingImages[] = $path;
                 }
             }
         }
        // تحديث حقل الصور بمصفوفة المسارات الجديدة
        $validatedData['images'] = json_encode($remainingImages);
        // لا نحتاج لتحديث delete_images في قاعدة البيانات
        unset($validatedData['delete_images']);


        // (اختياري) إعادة تعيين الحالة إلى "قيد المراجعة" بعد التعديل
         $validatedData['status'] = 'pending';

        // تحديث العقار (باستثناء user_id و code)
        $property->update(Arr::except($validatedData, ['user_id', 'code']));

        return redirect()->route('lister.properties.index')->with('success', 'Property updated and submitted for review!');
    }

    // --- 7. حذف العقار ---
    public function destroy(Property $property)
    {
         // التحقق من أن المستخدم هو صاحب العقار
        if (Auth::id() !== $property->user_id) {
            abort(403, 'Unauthorized action.');
        }

        // حذف الصور المرتبطة بالعقار من التخزين
        if ($property->images) {
            $imagePaths = json_decode($property->images, true);
            foreach ($imagePaths as $imagePath) {
                 Storage::disk('public')->delete($imagePath);
            }
        }

        // حذف العقار نفسه
        $property->delete();

        return redirect()->route('lister.properties.index')->with('success', 'Property deleted successfully!');
    }
}