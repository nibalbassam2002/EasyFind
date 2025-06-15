<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Category;
use App\Models\Area;       
use App\Models\Governorate; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Favorite;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\Plan;
class FrontendController extends Controller
{
    
    public function index()
{
    $latestProperties = Property::where('status', 'approved')
                              ->with('listarea')
                              ->orderBy('is_featured', 'desc') 
                              ->latest()
                              ->take(8)
                              ->get();

    $userId = Auth::id(); 

    
    if ($userId) {
       
        $favoritePropertyIds = Favorite::where('user_id', $userId)
                                       ->whereIn('property_id', $latestProperties->pluck('id'))
                                       ->pluck('property_id')
                                       ->toArray();

        $latestProperties->each(function ($property) use ($favoritePropertyIds) {
            $property->is_favorited = in_array($property->id, $favoritePropertyIds);
        });
    } else {
     
        $latestProperties->each(function ($property) {
            $property->is_favorited = false;
        });
    }

    $categories = Category::whereNull('parent_id')->orderBy('name')->get(); 
    $governorates = Governorate::with('areas')->orderBy('name')->get(); 

    return view('frontend.index', compact('latestProperties', 'categories', 'governorates'));
}


public function properties(Request $request)
{
    $query = Property::query()->where('status', 'approved')
                   ->with(['listarea', 'category']);

    // ▼▼▼ هذا هو الجزء الجديد والمحسّن للفلترة ▼▼▼

    // 1. فلترة حسب الكلمة المفتاحية (Keyword Search)
    if ($request->filled('search')) {
        $searchTerm = $request->input('search');
        $query->where(function ($q) use ($searchTerm) {
            $q->where('title', 'LIKE', "%{$searchTerm}%")
              ->orWhere('address', 'LIKE', "%{$searchTerm}%")
              ->orWhere('description', 'LIKE', "%{$searchTerm}%");
        });
    }

    // 2. فلترة حسب الغرض (Purpose)
    if ($request->filled('purpose')) {
        $query->where('purpose', $request->input('purpose'));
    }

    // 3. فلترة حسب نوع العقار (Property Type)
    if ($request->filled('category_slug')) {
        $query->whereHas('category', function($q) use ($request) {
            $q->where('slug', $request->input('category_slug'));
        });
    }

    // 4. فلترة حسب المنطقة (Area) - هذا هو الأهم
    if ($request->filled('area_id')) {
        $query->where('area_id', $request->input('area_id'));
    } 
    // 5. فلترة حسب المحافظة (Governorate) - إذا لم يتم اختيار منطقة
    elseif ($request->filled('governorate_id')) {
        $query->whereHas('listarea', function($q) use ($request) {
            $q->where('governorate_id', $request->input('governorate_id'));
        });
    }

    // 6. فلترة حسب السعر (Price Range)
    if ($request->filled('min_price')) {
        $query->where('price', '>=', $request->input('min_price'));
    }
    if ($request->filled('max_price')) {
        $query->where('price', '<=', $request->input('max_price'));
    }

    // 7. فلترة حسب عدد الغرف والحمامات
    if ($request->filled('min_rooms') && $request->input('min_rooms') > 0) {
        $query->where('rooms', '>=', $request->input('min_rooms'));
    }
    if ($request->filled('min_bathrooms') && $request->input('min_bathrooms') > 0) {
        $query->where('bathrooms', '>=', $request->input('min_bathrooms'));
    }

    // ▲▲▲ نهاية جزء الفلترة ▲▲▲

    // جلب النتائج مع الترتيب والترقيم
    $properties = $query->orderBy('is_featured', 'desc')
                        ->latest()
                        ->paginate(12)
                        ->withQueryString();

    // ... الكود الحالي لجلب المفضلة يبقى كما هو ...
    $userId = Auth::id();
    if ($userId) {
        $propertyIdsOnPage = collect($properties->items())->pluck('id')->toArray();
        $favoritePropertyIds = Favorite::where('user_id', $userId)
                                       ->whereIn('property_id', $propertyIdsOnPage)
                                       ->pluck('property_id')->toArray();
        foreach ($properties->items() as $property) {
            $property->is_favorited = in_array($property->id, $favoritePropertyIds);
        }
    } else {
        foreach ($properties->items() as $property) {
            $property->is_favorited = false;
        }
    }

    // جلب بيانات الفلاتر
    $governorates = Governorate::with('areas')->orderBy('name')->get();
    $categories = Category::whereNull('parent_id')->orderBy('name')->get();

    return view('frontend.properties', compact('properties', 'governorates', 'categories'));
}


public function showProperty(Property $property)
{
    if ($property->status !== 'approved') {
        abort(404);
    }
    $property->load(['user', 'listarea', 'category', 'subCategory']);
    $property->increment('views_count');

    $userId = Auth::id();
    if ($userId) {
        $property->is_favorited = Favorite::where('user_id', $userId)
                                          ->where('property_id', $property->id)
                                          ->exists();
    } else {
        $property->is_favorited = false;
    }
    $similarProperties = Property::where('status', 'approved')
                                 ->where('id', '!=', $property->id)
                                 ->with('listarea')
                                 ->inRandomOrder()
                                 ->take(4)
                                 ->get();

    if ($userId) {
        $similarPropertyIds = $similarProperties->pluck('id')->toArray();
        $favoriteSimilarIds = Favorite::where('user_id', $userId)
                                      ->whereIn('property_id', $similarPropertyIds)
                                      ->pluck('property_id')
                                      ->toArray();
        $similarProperties->each(function ($simProp) use ($favoriteSimilarIds) {
            $simProp->is_favorited = in_array($simProp->id, $favoriteSimilarIds);
        });
    } else {
        $similarProperties->each(function ($simProp) {
            $simProp->is_favorited = false;
        });
    }

    return view('frontend.property-detail', compact('property', 'similarProperties'));
}
public function favorites()
{
    if (!Auth::check()) {
        return redirect()->route('login')->with('warning', 'Please log in to view your favorites.');
    }

    $user = Auth::user();
    $userId = $user->id;

    $favoriteProperties = $user->favoriteProperties()
                              ->where('status', 'approved')
                              ->with('listarea')
                              ->latest('favorites.created_at')
                              ->paginate(10);

    $favoriteProperties->each(function($favProperty){
        $favProperty->is_favorited = true;
    });

    $recommendedProperties = Property::where('status', 'approved')
                                 ->whereNotIn('id', $user->favoriteProperties()->pluck('properties.id'))
                                 ->with('listarea')
                                 ->inRandomOrder()
                                 ->take(4)
                                 ->get();

    if ($userId) {
        $recommendedPropertyIds = $recommendedProperties->pluck('id')->toArray();
        $favoritedRecommendedIds = Favorite::where('user_id', $userId)
                                       ->whereIn('property_id', $recommendedPropertyIds)
                                       ->pluck('property_id')
                                       ->toArray();
        $recommendedProperties->each(function ($recProperty) use ($favoritedRecommendedIds) {
            $recProperty->is_favorited = in_array($recProperty->id, $favoritedRecommendedIds);
        });
    } else {
        $recommendedProperties->each(function ($recProperty) {
            $recProperty->is_favorited = false;
        });
    }


    return view('frontend.favorites', compact('favoriteProperties', 'recommendedProperties'));
}
public function showPricingPlans()
{

   $plans = Plan::where('is_active', true)
                     ->orderBy('price', 'asc')
                     ->get();

        $userHasUsedFreePlan = false;
        $freePlanSlug = 'free';

        if (Auth::check()) {
            $user = Auth::user();
            $freePlanModel = Plan::where('slug', $freePlanSlug)->first();

            if ($freePlanModel) {
                $userHasUsedFreePlan = $user->subscriptions()
                                          ->where('plan_id', $freePlanModel->id)
                                          ->exists();
            }
        }

        return view('frontend.pricing', compact('plans', 'userHasUsedFreePlan', 'freePlanSlug'));
    }
}
    
