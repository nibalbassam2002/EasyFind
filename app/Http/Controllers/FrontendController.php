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
use App\Models\User;
class FrontendController extends Controller
{
    
    public function index()
{
    $latestProperties = Property::where('status', 'approved')
                              ->with('listarea')
                              ->withCount('reviews')
                              ->withAvg('reviews', 'rating')
                              ->orderBy('is_featured', 'desc') 
                              ->latest()
                              ->take(8)
                              ->get();

    if ($latestProperties->isEmpty()) {
        $latestProperties = $this->getMockProperties();
    }

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
 public function publicSearch(Request $request)
    {
        $query = Property::query()->where('status', 'approved')->with('listarea');

        // بحث بسيط جداً
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('address', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                  ->orWhereHas('listarea', function($areaQuery) use ($searchTerm){
                      $areaQuery->where('name', 'LIKE', "%{$searchTerm}%");
                  })
                  ->orWhereHas('listarea.governorate', function($govQuery) use ($searchTerm){
                      $govQuery->where('name', 'LIKE', "%{$searchTerm}%");
                  });
            });
        }

        $properties = $query->orderBy('is_featured', 'desc')
                            ->latest()
                            ->paginate(12)
                            ->withQueryString();

        
        $properties->each(function ($property) {
            $property->is_favorited = false;
        });

        
        return view('frontend.properties', compact('properties'));
    }


public function properties(Request $request)
{
    $query = Property::query()->where('status', 'approved')
                   ->with(['listarea', 'category'])
                   ->withCount('reviews')
                   ->withAvg('reviews', 'rating');

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

   

     // جلب النتائج مع الترتيب والترقيم
     $properties = $query->orderBy('is_featured', 'desc')
                         ->latest()
                         ->paginate(12)
                         ->withQueryString();

     if ($properties->isEmpty() && Property::where('status', 'approved')->count() === 0) {
         $mockList = $this->getMockProperties();
         
         if ($request->filled('purpose')) {
             $mockList = $mockList->where('purpose', $request->input('purpose'));
         }
         if ($request->filled('category_slug')) {
             $mockList = $mockList->filter(function($p) use ($request) {
                 return $p->category && $p->category->slug == $request->input('category_slug');
             });
         }
         
         $properties = new \Illuminate\Pagination\LengthAwarePaginator(
             $mockList->values(),
             $mockList->count(),
             12,
             1,
             ['path' => $request->url(), 'query' => $request->query()]
         );
     }

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


public function showProperty($property_id)
{
    if ($property_id < 0) {
        $mockProperties = $this->getMockProperties();
        $property = $mockProperties->firstWhere('id', $property_id);
        if (!$property) {
            abort(404);
        }
        $property->views_count++;
        
        $avgRating = 0;
        $reviewsCount = 0;
        $similarProperties = $mockProperties->where('id', '!=', $property_id)->take(4);

        $userId = Auth::id();
        if ($userId) {
            $property->is_favorited = Favorite::where('user_id', $userId)
                                              ->where('property_id', $property->id)
                                              ->exists();
            
            $similarPropertyIds = $similarProperties->pluck('id')->toArray();
            $favoriteSimilarIds = Favorite::where('user_id', $userId)
                                          ->whereIn('property_id', $similarPropertyIds)
                                          ->pluck('property_id')
                                          ->toArray();
            $similarProperties->each(function ($simProp) use ($favoriteSimilarIds) {
                $simProp->is_favorited = in_array($simProp->id, $favoriteSimilarIds);
            });
        } else {
            $property->is_favorited = false;
            $similarProperties->each(function ($simProp) {
                $simProp->is_favorited = false;
            });
        }

        return view('frontend.property-detail', compact(
            'property', 
            'similarProperties', 
            'avgRating', 
            'reviewsCount'
        ));
    }

    $property = Property::findOrFail($property_id);
    if ($property->status !== 'approved') {
        abort(404);
    }
    $property->load(['user', 'listarea.governorate', 'category', 'subCategory', 'reviews.user']);
    $property->increment('views_count');
    $avgRating = $property->reviews()->avg('rating');
    $reviewsCount = $property->reviews()->count();


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

     return view('frontend.property-detail', compact(
        'property', 
        'similarProperties', 
        'avgRating', 
        'reviewsCount'
    ));
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

    private function getMockProperties()
    {
        $catApartments = new Category(['id' => 1, 'name' => 'Apartments', 'slug' => 'apartments']);
        $catHouses = new Category(['id' => 2, 'name' => 'Houses', 'slug' => 'houses']);
        $catCaravans = new Category(['id' => 3, 'name' => 'Caravans', 'slug' => 'caravans']);
        $catTents = new Category(['id' => 4, 'name' => 'Tents', 'slug' => 'tents']);

        $govGaza = new Governorate(['id' => 1, 'name' => 'Gaza']);
        $areaRimal = new Area(['id' => 1, 'name' => 'Al-Rimal', 'governorate_id' => 1]);
        $areaRimal->setRelation('governorate', $govGaza);

        $govDeir = new Governorate(['id' => 2, 'name' => 'Deir al-Balah']);
        $areaDeir = new Area(['id' => 2, 'name' => 'Central Deir', 'governorate_id' => 2]);
        $areaDeir->setRelation('governorate', $govDeir);

        $govKhan = new Governorate(['id' => 3, 'name' => 'Khan Younis']);
        $areaKhan = new Area(['id' => 3, 'name' => 'East Sector', 'governorate_id' => 3]);
        $areaKhan->setRelation('governorate', $govKhan);

        $govRafah = new Governorate(['id' => 4, 'name' => 'Rafah']);
        $areaRafah = new Area(['id' => 4, 'name' => 'Al-Mawasi', 'governorate_id' => 4]);
        $areaRafah->setRelation('governorate', $govRafah);

        $userGaza = new User(['id' => 1, 'name' => 'Gaza Properties Agency']);
        $userDeir = new User(['id' => 2, 'name' => 'Al-Ola Real Estate']);
        $userKhan = new User(['id' => 3, 'name' => 'Khan Lands Co.']);
        $userRafah = new User(['id' => 4, 'name' => 'Rafah shelter systems']);

        $p1 = new Property([
            'title' => 'Spacious Family Apartment in Al-Rimal (Sample)',
            'description' => 'A beautiful, spacious apartment located in the heart of Al-Rimal, Gaza. Close to shopping centers and schools. 3 bedrooms, 2 bathrooms, modern kitchen, and a spacious balcony.',
            'purpose' => 'rent',
            'price' => 350.00,
            'currency' => 'USD',
            'area_id' => 1,
            'address' => 'Al-Rimal Street, near Al-Katiba Park',
            'rooms' => 3,
            'bathrooms' => 2,
            'area' => 140,
            'floors' => 1,
            'images' => ['frontend/assets/h6.jpeg', 'frontend/assets/h2.webp'],
            'views_count' => 142,
            'status' => 'approved',
            'is_featured' => true,
        ]);
        $p1->id = -1;
        $p1->created_at = now()->subDays(2);
        $p1->setRelation('listarea', $areaRimal);
        $p1->setRelation('category', $catApartments);
        $p1->setRelation('user', $userGaza);
        $p1->setRelation('reviews', collect());
        $p1->reviews_count = 0;
        $p1->reviews_avg_rating = 0;

        $p2 = new Property([
            'title' => 'Luxurious Modern Villa with Garden (Sample)',
            'description' => 'Gorgeous villa with high-end finishing, private garden, and spacious courtyard. Situated in a peaceful neighborhood of Deir al-Balah. Perfect for large families.',
            'purpose' => 'sale',
            'price' => 125000.00,
            'currency' => 'USD',
            'area_id' => 2,
            'address' => 'Coastal Road, Deir al-Balah',
            'rooms' => 5,
            'bathrooms' => 4,
            'area' => 350,
            'floors' => 2,
            'images' => ['frontend/assets/h11.jpg', 'frontend/assets/home.jpg'],
            'views_count' => 285,
            'status' => 'approved',
            'is_featured' => true,
        ]);
        $p2->id = -2;
        $p2->created_at = now()->subDays(5);
        $p2->setRelation('listarea', $areaDeir);
        $p2->setRelation('category', $catHouses);
        $p2->setRelation('user', $userDeir);
        $p2->setRelation('reviews', collect());
        $p2->reviews_count = 0;
        $p2->reviews_avg_rating = 0;

        $p3 = new Property([
            'title' => 'Spacious Agricultural Land for Sale (Sample)',
            'description' => 'Prime agricultural land available for purchase in Khan Younis. Fertile soil, equipped with water well access, and perfect for farming or custom residential construction.',
            'purpose' => 'sale',
            'price' => 75000.00,
            'currency' => 'USD',
            'area_id' => 3,
            'address' => 'East Khan Younis agricultural sector',
            'rooms' => 0,
            'bathrooms' => 0,
            'area' => 1000,
            'floors' => 0,
            'images' => ['frontend/assets/h9.jpg', 'frontend/assets/home.jpg'],
            'views_count' => 98,
            'status' => 'approved',
            'is_featured' => false,
        ]);
        $p3->id = -3;
        $p3->created_at = now()->subDays(7);
        $p3->setRelation('listarea', $areaKhan);
        $p3->setRelation('category', $catApartments);
        $p3->setRelation('user', $userKhan);
        $p3->setRelation('reviews', collect());
        $p3->reviews_count = 0;
        $p3->reviews_avg_rating = 0;

        $p4 = new Property([
            'title' => 'Secure Caravan for Temporary Shelter (Sample)',
            'description' => 'Fully equipped modern caravan for temporary accommodation in Rafah. Secure neighborhood, features 2 rooms, fully functional bathroom, electricity connection, and water storage.',
            'purpose' => 'rent',
            'price' => 150.00,
            'currency' => 'USD',
            'area_id' => 4,
            'address' => 'Al-Mawasi district, Rafah',
            'rooms' => 2,
            'bathrooms' => 1,
            'area' => 45,
            'floors' => 1,
            'images' => ['frontend/assets/h2.webp', 'frontend/assets/h10.jpg'],
            'views_count' => 412,
            'status' => 'approved',
            'is_featured' => false,
        ]);
        $p4->id = -4;
        $p4->created_at = now()->subDays(1);
        $p4->setRelation('listarea', $areaRafah);
        $p4->setRelation('category', $catCaravans);
        $p4->setRelation('user', $userRafah);
        $p4->setRelation('reviews', collect());
        $p4->reviews_count = 0;
        $p4->reviews_avg_rating = 0;

        return collect([$p1, $p2, $p3, $p4]);
    }
}
    
