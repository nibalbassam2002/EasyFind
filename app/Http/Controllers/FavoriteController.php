<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use App\Models\Property;          
use App\Models\Favorite; 

class FavoriteController extends Controller
{
    public function toggle(Request $request)
    {
        
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);
        }
        $validated = $request->validate([
            'property_id' => 'required|integer|exists:properties,id' // تأكد أن العقار موجود
        ]);

        $userId = Auth::id();
        $propertyId = $validated['property_id'];

        try {
            $existingFavorite = Favorite::where('user_id', $userId)
                                      ->where('property_id', $propertyId)
                                      ->first();

            $isFavorited = false; 
            if ($existingFavorite) {
                $existingFavorite->delete();
                $message = 'Property removed from favorites.';
                $isFavorited = false;
            } else {
                Favorite::create([
                    'user_id' => $userId,
                    'property_id' => $propertyId,
                ]);
                $message = 'Property added to favorites.';
                $isFavorited = true;
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'is_favorited' => $isFavorited 
            ]);

        } catch (\Exception $e) {
             logger("Error toggling favorite: " . $e->getMessage());
             return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }

     public function remove(Property $property) 
     {
          if (!Auth::check()) {
             return redirect()->back()->with('error', 'Authentication required.');
         }

         $userId = Auth::id();

         try {
             $deleted = Favorite::where('user_id', $userId)
                                ->where('property_id', $property->id)
                                ->delete(); 

             if ($deleted) {
                  return redirect()->route('frontend.favorites')->with('success', 'Property removed from favorites.');
             } else {
                  return redirect()->route('frontend.favorites')->with('warning', 'Property was not in your favorites.');
             }

         } catch (\Exception $e) {
              logger("Error removing favorite: " . $e->getMessage());
              return redirect()->route('frontend.favorites')->with('error', 'An error occurred while removing the property.');
         }
     }
}
