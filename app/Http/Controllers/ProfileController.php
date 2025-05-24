<?php

namespace App\Http\Controllers;

use App\Models\Governorate;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage; 
use Illuminate\Http\JsonResponse; 

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load('area.governorate'); 
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
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($user->id)], 
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);

    
        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete('images/' . $user->profile_image);
            }
            $image = $request->file('profile_image');
            $imageName = time() . '_' . $user->id . '.' . $image->getClientOriginalExtension(); 
            $path = $image->storeAs('images', $imageName, 'public');
            $validatedData['profile_image'] = $imageName;
        }

        $user->update($validatedData);

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
                'default_image' => asset('assets/img/profile.jpg') 
            ]);
        }

        return response()->json(['message' => 'لا توجد صورة شخصية لحذفها.'], 404);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->back()->with('success', 'تم تغيير كلمة السر بنجاح!');
    }
        public function showCustomerAccountPage(Request $request)
    {
        $user = Auth::user()->load('area.governorate');
        $governorates = Governorate::with('areas')->orderBy('name')->get();

        $defaultTab = 'profile-overview';
        if (Auth::user()->password === null) {
            $defaultTab = 'profile-set-initial-password';
        }

        if (session()->has('errors')) {
            if (session('errors')->hasBag('updateProfile')) {
                $activeTab = 'profile-edit';
            } elseif (session('errors')->hasBag('storeInitialPassword')) {
                $activeTab = 'profile-set-initial-password';
            } elseif (session('errors')->hasBag('changePassword')) {
                $activeTab = 'profile-change-password';
            } else {
                // إذا لا يوجد خطأ محدد، استخدم التبويب من الجلسة أو الطلب أو الافتراضي
                $activeTab = $request->query('tab', session('active_tab', $defaultTab));
            }
        } else {
           
            $activeTab = $request->query('tab', session('active_tab', $defaultTab));
        }


        if ($activeTab === 'profile-set-initial-password' && Auth::user()->password !== null) {
            $activeTab = 'profile-overview'; 
             session()->flash('info', 'Password already set. Use "Change Password" if needed.');
        }
        else if ($activeTab === 'profile-change-password' && Auth::user()->password === null) {
            $activeTab = 'profile-set-initial-password';
            session()->flash('warning', 'Please set your initial password first.');
        }

        session(['active_tab' => $activeTab]); 

        return view('frontend.account', compact('user', 'governorates', 'activeTab'));
    }

    public function updateCustomerProfile(Request $request)
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'address' => ['nullable', 'string', 'max:255'],
            'area_id' => ['nullable', 'integer', 'exists:areas,id'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'], 
        ]);

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete('images/' . $user->profile_image);
            }
            $image = $request->file('profile_image');
            $imageName = time() . '_' . $user->id . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('images', $imageName, 'public'); 
            $validatedData['profile_image'] = $imageName;
        }

        try {
            $user->update($validatedData);

            return redirect()->route('frontend.account')->with('success', 'تم تحديث بيانات حسابك بنجاح!');

        } catch (\Exception $e) {
            logger("Customer Account Update Error for user {$user->id}: " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'حدث خطأ أثناء تحديث البيانات. يرجى المحاولة مرة أخرى.');
        }
    }

    public function changeCustomerPassword(Request $request)
    {
        if (Auth::user()->password === null) {
            session(['active_tab' => 'profile-set-initial-password']);
            return redirect()->route('frontend.account')
                             ->with('warning', 'Please set your initial password first.');
        }

        // *** استخدام Error Bag وقواعد Password ***
        $request->validateWithBag('changePassword', [
            'current_password' => ['required', function ($attribute, $value, $fail) {
                if (!Hash::check($value, Auth::user()->password)) {
                    $fail(__('The provided current password does not match your actual current password.'));
                }
            }],
            'password' => ['required', 'confirmed', 'min:8'], // <-- *** التعديل هنا ***
        ], [], [
            'password' => 'new password'
        ]);
        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->save();

        session(['active_tab' => 'profile-change-password']); // *** تحديد التبويب النشط بعد النجاح ***
        return redirect()->route('frontend.account')->with('password_change_success', 'Password changed successfully!');
    }
    
    public function showSetInitialPasswordForm()
    {
        // Middleware 'password.notset' يجب أن يحمي هذا المسار
        if (Auth::user()->password !== null) {
            session(['active_tab' => 'profile-overview']);
            return redirect()->route('frontend.account')->with('info', 'You have already set your password.');
        }
        session(['active_tab' => 'profile-set-initial-password']);
        return redirect()->route('frontend.account'); // سيعرض صفحة الحساب مع التبويب الصحيح نشطًا
    }

    // *** دالة لحفظ كلمة المرور الأولية ***
    public function storeInitialPassword(Request $request)
    {
        // Middleware 'password.notset' يجب أن يحمي هذا المسار
        if (Auth::user()->password !== null) {
            session(['active_tab' => 'profile-overview']);
            return redirect()->route('frontend.account')->with('info', 'You have already set your password.');
        }

        // *** استخدام Error Bag وقواعد Password ***
        $request->validateWithBag('storeInitialPassword', [
            'password' => ['required', 'confirmed', 'min:8'], // <-- *** التعديل هنا ***
        ], [], [
            'password' => 'new password'
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->save();

        session(['active_tab' => 'profile-overview']); // *** تحديد التبويب النشط بعد النجاح ***
        return redirect()->route('frontend.account')->with('initial_password_success', 'Your password has been set successfully!'); // *** رسالة نجاح مخصصة ***
    }
}