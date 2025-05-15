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

    $properties = $query->latest()->paginate(12)->withQueryString();


    $userId = Auth::id();
    if ($userId) {

        $propertyIdsOnPage = collect($properties->items())->pluck('id')->toArray();

        $favoritePropertyIds = Favorite::where('user_id', $userId)
                                       ->whereIn('property_id', $propertyIdsOnPage)
                                       ->pluck('property_id')
                                       ->toArray();


        foreach ($properties->items() as $property) {
            $property->is_favorited = in_array($property->id, $favoritePropertyIds);
        }
    } else {
        foreach ($properties->items() as $property) {
            $property->is_favorited = false;
        }
    }


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

    return view('frontend.pricing', compact('plans'));
}
    
}