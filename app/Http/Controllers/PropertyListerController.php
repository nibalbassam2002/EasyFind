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
        
        $properties = Property::where('user_id', Auth::id())
                              ->with('listarea', 'category', 'subCategory') 
                              ->latest()
                              ->paginate(10);
          return view('dashboard.property_lister.index', compact('properties'));
    }

   
    public function create()
    {
        
        $categories = Category::whereNull('parent_id')->orderBy('name')->get(); 
        $subCategories = Category::whereNotNull('parent_id')->orderBy('name')->get(); 
        $governorates = Governorate::with('areas')->orderBy('name')->get();
        $purposes = ['rent', 'sale', 'lease']; 
        $currencies = ['ILS', 'USD', 'JOD']; 

        return view('dashboard.property_lister.create', compact('categories', 'subCategories', 'governorates', 'purposes', 'currencies'));
    }

    public function store(Request $request)
    {
        
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
            'land_type' => 'nullable|string|max:100',  
            'tent_type' => 'nullable|string|max:100',
            'caravan_type' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255', 
            'images' => 'nullable|array|max:5', 
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'video_url' => 'nullable|url|max:255',
        ]);

        
        $validatedData['user_id'] = Auth::id(); 
        $validatedData['status'] = 'pending'; 
        $validatedData['code'] = 'PROP-' . date('Y') . '-' . Str::random(5); 

        
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


        Property::create($validatedData);

        return redirect()->route('lister.properties.index')->with('success', 'Property submitted for review successfully!');
    }

    
    public function show(Property $property)
    {
        
        if (Auth::id() !== $property->user_id) {
            abort(403, 'Unauthorized action.');
        }
        
        $property->load('listarea', 'category', 'subCategory');
        return view('dashboard.property_lister.show', compact('property')); 
    }

   
    public function edit(Property $property)
    {
        
        if (Auth::id() !== $property->user_id) {
            abort(403, 'Unauthorized action.');
        }

        
        $categories = Category::whereNull('parent_id')->orderBy('name')->get();
        $subCategories = Category::whereNotNull('parent_id')->orderBy('name')->get();
        $governorates = Governorate::with('areas')->orderBy('name')->get();
        $purposes = ['rent', 'sale', 'lease'];
        $currencies = ['ILS', 'USD', 'JOD'];

        
        $currentImages = $property->images ? json_decode($property->images, true) : [];


        return view('dashboard.property_lister.edit', compact('property', 'categories', 'subCategories', 'governorates', 'purposes', 'currencies', 'currentImages'));
    }

    
    public function update(Request $request, Property $property)
    {
       
        if (Auth::id() !== $property->user_id) {
            abort(403, 'Unauthorized action.');
        }

        
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
            'images' => 'nullable|array|max:5', 
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'video_url' => 'nullable|url|max:255',
            'delete_images' => 'nullable|array', 
        ]);

      
         $currentImages = $property->images ? json_decode($property->images, true) : [];
         $imagesToDelete = $request->input('delete_images', []);
         $remainingImages = [];
         foreach ($currentImages as $imagePath) {
             if (in_array($imagePath, $imagesToDelete)) {
               
                 Storage::disk('public')->delete($imagePath);
             } else {
                 $remainingImages[] = $imagePath; 
             }
         }

         
         if ($request->hasFile('images')) {
             foreach ($request->file('images') as $image) {
                 $imageName = time() . '_' . $image->getClientOriginalName();
                 $path = $image->storeAs('properties_images', $imageName, 'public');
                
                 if (count($remainingImages) < 5) {
                    $remainingImages[] = $path;
                 }
             }
         }
      
        $validatedData['images'] = json_encode($remainingImages);
      
        unset($validatedData['delete_images']);


        
         $validatedData['status'] = 'pending';

        
        $property->update(Arr::except($validatedData, ['user_id', 'code']));

        return redirect()->route('lister.properties.index')->with('success', 'Property updated and submitted for review!');
    }

 
    public function destroy(Property $property)
    {
        
        if (Auth::id() !== $property->user_id) {
            abort(403, 'Unauthorized action.');
        }

        
        if ($property->images) {
            $imagePaths = json_decode($property->images, true);
            foreach ($imagePaths as $imagePath) {
                 Storage::disk('public')->delete($imagePath);
            }
        }

       
        $property->delete();

        return redirect()->route('lister.properties.index')->with('success', 'Property deleted successfully!');
    }
}