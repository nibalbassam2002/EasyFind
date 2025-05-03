<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Category;
use App\Models\Area;       
use App\Models\Governorate; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
class FrontendController extends Controller
{
    
    public function index()
    {
        $latestProperties = Property::where('status', 'approved')
                                  ->with('area.governorate')
                                  ->latest()
                                  ->take(8)
                                  ->get();


        return view('frontend.index', compact('latestProperties' /*, 'categories', 'governorates' */));
    }


    public function properties(Request $request)
    {
        $query = Property::query()->where('status', 'approved')
                           ->with(['area.governorate', 'category']); 

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('address', 'LIKE', "%{$searchTerm}%");
            });
        }

        if ($request->filled('purpose')) {
            $purpose = $request->input('purpose');
            // تحويل 'buy' إلى 'sale' إذا جاء من الواجهة الأمامية بهذا الشكل
            if ($purpose === 'buy') {
                $purpose = 'sale';
            }

            if (in_array($purpose, ['sale', 'rent', 'lease'])) {
                $query->where('purpose', $purpose);
            }
        }


        if ($request->filled('area_id')) {
             $areaId = $request->input('area_id');
             if (is_numeric($areaId) && $areaId > 0) {
                 $query->where('area_id', $areaId);
             }
        }

        elseif ($request->filled('governorate_id')) {
             $governorateId = $request->input('governorate_id');
             if (is_numeric($governorateId) && $governorateId > 0) {
             
                 $query->whereHas('area', function ($areaQuery) use ($governorateId) {
                     $areaQuery->where('governorate_id', $governorateId);
                 });
             }
        }
       

       
         if ($request->filled('category_id')) {
            $categoryId = $request->input('category_id');
             if (is_numeric($categoryId) && $categoryId > 0) {
                 $query->where('category_id', $categoryId);
              
             }
        }

         // فلتر السعر (مثال: الحد الأدنى والأقصى)
        if ($request->filled('min_price')) {
            $minPrice = $request->input('min_price');
            if (is_numeric($minPrice)) {
                $query->where('price', '>=', $minPrice);
            }
        }
         if ($request->filled('max_price')) {
            $maxPrice = $request->input('max_price');
             if (is_numeric($maxPrice)) {
                $query->where('price', '<=', $maxPrice);
            }
        }


        
        $properties = $query->latest()->paginate(12)->withQueryString();
        $governorates = Governorate::with('areas')->orderBy('name')->get(); 
        $categories = Category::whereNull('parent_id')->orderBy('name')->get();
        return view('frontend.properties', compact('properties', 'governorates', 'categories')); 
    }


    /**
     * عرض صفحة تفاصيل عقار واحد.
     */
    public function showProperty(Property $property) // يستخدم Route Model Binding
    {
        // تأكد أن العقار معتمد أو أن المستخدم الحالي هو المالك أو الأدمن (اختياري)
        if ($property->status !== 'approved' /* && optional(auth()->user())->role !== 'admin' && optional(auth()->user())->id !== $property->user_id */) {
            abort(404); // أو عرض رسالة خطأ مناسبة
        }

        // تحميل العلاقات اللازمة للعرض
        // $property->load(['user', 'city', 'category', 'subCategory']); // <--- غير هذا
        $property->load(['user', 'area.governorate', 'category', 'subCategory']); // <--- إلى هذا

        // زيادة عداد المشاهدات (يمكن تحسينه لمنع الزيادة المتكررة من نفس المستخدم)
        $property->increment('views_count');

        // جلب عقارات مشابهة (بناءً على المنطقة أو التصنيف الفرعي)
        $similarProperties = Property::where('status', 'approved')
                                     ->where('id', '!=', $property->id) // استبعاد العقار الحالي
                                     ->where(function($q) use ($property) {
                                         // الأولوية للمنطقة نفسها
                                         $q->where('area_id', $property->area_id)
                                           // أو نفس التصنيف الفرعي في نفس المحافظة (لزيادة الصلة)
                                           ->orWhere(function($q2) use ($property) {
                                               if ($property->area?->governorate_id) { // تأكد من وجود المحافظة
                                                   $q2->where('sub_category_id', $property->sub_category_id)
                                                      ->whereHas('area', fn($aq) => $aq->where('governorate_id', $property->area->governorate_id));
                                               } else {
                                                    // إذا لم تكن هناك محافظة للعقار الحالي، اعتمد على التصنيف الفرعي فقط
                                                   $q2->where('sub_category_id', $property->sub_category_id);
                                               }
                                           });
                                           // أو نفس التصنيف الرئيسي في نفس المحافظة (كخيار ثالث)
                                            // ->orWhere(function($q3) use ($property) {
                                            //     if ($property->area?->governorate_id) {
                                            //         $q3->where('category_id', $property->category_id)
                                            //            ->whereHas('area', fn($aq) => $aq->where('governorate_id', $property->area->governorate_id));
                                            //     } else {
                                            //         $q3->where('category_id', $property->category_id);
                                            //     }
                                            // });
                                     })
                                     ->with('area.governorate') // تحميل الموقع للعقارات المشابهة أيضاً
                                     ->inRandomOrder() // عرضها بترتيب عشوائي
                                     ->take(4)         // جلب 4 عقارات مشابهة كحد أقصى
                                     ->get();

      
        return view('frontend.property-detail', compact('property', 'similarProperties'));
    }
    public function favorites()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('warning', 'Please log in to view your favorites.');
        }

        $user = Auth::user();

        $favoriteProperties = $user->favoriteProperties() 
                                  ->where('status', 'approved') 
                                  ->with('area.governorate')   
                                  ->latest('favorites.created_at') 
                                  ->paginate(10); 

        
        $recommendedProperties = Property::where('status', 'approved')
                                     ->whereNotIn('id', $user->favoriteProperties()->pluck('properties.id')) // استبعاد المفضلة
                                     ->with('area.governorate')
                                     ->inRandomOrder()
                                     ->take(4)
                                     ->get();


        return view('frontend.favorites', compact('favoriteProperties', 'recommendedProperties'));
    }
}