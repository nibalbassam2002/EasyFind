<?php

namespace App\Http\Controllers;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function store(Request $request, Property $property)
    {
        
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

       
        if ($property->user_id == Auth::id()) {
            return back()->with('error', 'You cannot review your own property.');
        }

        try {
           
            $property->reviews()->create([
                'user_id' => Auth::id(),
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

            return back()->with('success', 'Your review has been submitted successfully!');

        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1062) { 
                return back()->with('error', 'You have already submitted a review for this property.');
            }
           
            return back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }
}
