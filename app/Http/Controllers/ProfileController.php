<?php

namespace App\Http\Controllers;

use App\Models\Governorate;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\File; 
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $governorates = Governorate::with('areas')->orderBy('name')->get();
        
        return view('Auth.profile', [
            'user' => $user,
            'governorates' => $governorates,
        ]);

    }

    public function update(Request $request)
    {
        $user = Auth::user();
    
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'area_id' => ['nullable', 'integer', 'exists:areas,id'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg|max:2048'],
        ]);
    
        // معالجة رفع الصورة
        if ($request->hasFile('profile_image')) {
            // حذف الصورة القديمة إذا كانت موجودة
            if ($user->profile_image) {
                Storage::disk('public')->delete('images/' . $user->profile_image);
            }
    
            // رفع الصورة الجديدة
            $image = $request->file('profile_image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('images', $imageName, 'public'); // تخزين الصورة في مجلد public/images
            $validatedData['profile_image'] = $imageName; // تخزين اسم الصورة في الـ validatedData

        }
 
        if ($request->filled('password')) {
            $validatedData['password'] = Hash::make($request->password);
        }
        $user->update($validatedData);
    
         
    
        $user->save(); // حفظ التغييرات
    
        return redirect()->route('profile.index')->with('profile_success', 'تم تحديث الملف الشخصي بنجاح!');
    }

    public function deleteImage()
    {
        $user = Auth::user();
    
        if ($user->profile_image) {
            Storage::disk('public')->delete('images/' . $user->profile_image);
            $user->profile_image = null;
            $user->save();
    
            return response()->json([
                'message' => 'تم حذف الصورة الشخصية بنجاح!',
                'default_image' => asset('assets/img/profile.jpg') // إرسال رابط الصورة الافتراضية
            ]);
        }
    
        return response()->json(['message' => 'لا توجد صورة شخصية لحذفها.'], 404);
    }
    public function changePassword(Request $request)
{
    $request->validate([
        'current_password' => ['required', 'current_password'], // يتأكد من تطابق كلمة السر الحالية
        'new_password' => ['required', 'min:8', 'confirmed'], // يتأكد من تطابق كلمة السر الجديدة مع التأكيد
    ]);

    $user = Auth::user();
    $user->password = Hash::make($request->new_password);
    $user->save();

    return redirect()->back()->with('success', 'تم تغيير كلمة السر بنجاح!');
}
}