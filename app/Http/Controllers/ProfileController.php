<?php

namespace App\Http\Controllers;

use App\Models\Governorate;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator; // تأكد من وجود هذا
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\File; // قد لا يكون هذا مستخدماً الآن
use Illuminate\Support\Facades\Storage; // مهم جداً للصورة
use Illuminate\Http\JsonResponse; // قد لا يكون هذا مستخدماً الآن إذا لم يكن هناك AJAX

class ProfileController extends Controller
{
    /**
     * عرض صفحة الملف الشخصي في الداشبورد (للأدوار الأخرى)
     */
    public function index()
    {
        $user = Auth::user()->load('area.governorate'); // تحميل العلاقات
        $governorates = Governorate::with('areas')->orderBy('name')->get();

        return view('Auth.profile', [ // تأكد من أن هذا هو الـ view الصحيح للداشبورد
            'user' => $user,
            'governorates' => $governorates,
        ]);
    }

    /**
     * تحديث الملف الشخصي من الداشبورد (للأدوار الأخرى)
     * هذه الدالة تبقى كما هي للتعامل مع تحديثات الداشبورد
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'description' => ['nullable', 'string', 'max:1000'], // حقل الوصف إذا كان موجوداً في الداشبورد
            'area_id' => ['nullable', 'integer', 'exists:areas,id'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($user->id)], // التحقق من الهاتف هنا أيضاً
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);

        // معالجة رفع الصورة (إذا تم رفعها من الداشبورد)
        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete('images/' . $user->profile_image);
            }
            $image = $request->file('profile_image');
            $imageName = time() . '_' . $user->id . '.' . $image->getClientOriginalExtension(); // اسم فريد أكثر
            $path = $image->storeAs('images', $imageName, 'public');
            $validatedData['profile_image'] = $imageName;
        }

        // لا نحدث كلمة المرور من هنا عادةً، تكون في دالة منفصلة
        // if ($request->filled('password')) { ... }

        $user->update($validatedData);
        // $user->save(); // update تقوم بالحفظ، لا حاجة لـ save بعدها

        // إعادة التوجيه لصفحة الملف الشخصي في الداشبورد
        return redirect()->route('profile.index')->with('profile_success', 'تم تحديث الملف الشخصي بنجاح!');
    }

    /**
     * حذف الصورة الشخصية (قد تستخدم AJAX من الداشبورد أو صفحة العميل)
     * هذه الدالة تبدو جيدة للاستخدام مع AJAX
     */
    public function deleteImage()
    {
        $user = Auth::user();

        if ($user->profile_image) {
            Storage::disk('public')->delete('images/' . $user->profile_image);
            $user->profile_image = null;
            $user->save();

            return response()->json([
                'message' => 'تم حذف الصورة الشخصية بنجاح!',
                // قد تحتاج لتحديد صورة افتراضية مختلفة لصفحة العميل والداشبورد
                'default_image' => asset('assets/img/profile.jpg') // تأكد من صحة هذا المسار
            ]);
        }

        return response()->json(['message' => 'لا توجد صورة شخصية لحذفها.'], 404);
    }

    /**
     * تغيير كلمة المرور من الداشبورد (للأدوار الأخرى)
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->new_password);
        $user->save();

        // إعادة التوجيه للصفحة السابقة (غالباً تبويب تغيير كلمة المرور في الداشبورد)
        return redirect()->back()->with('success', 'تم تغيير كلمة السر بنجاح!');
    }

    // ================================================================
    // == الدوال الخاصة بصفحة حساب العميل (frontend.account) ==
    // ================================================================

    /**
     * تحديث بيانات حساب العميل (بما في ذلك الصورة) من صفحة /my-account
     * هذه هي الدالة التي ستستقبل الطلب من الفورم الموحد
     */
    public function updateCustomerProfile(Request $request)
    {
        $user = Auth::user();

        // 1. قواعد التحقق (تشمل الصورة كـ nullable)
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'address' => ['nullable', 'string', 'max:255'],
            'area_id' => ['nullable', 'integer', 'exists:areas,id'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'], // <-- التحقق من الصورة هنا
        ]);

        // 2. معالجة رفع الصورة (فقط إذا تم اختيار ملف جديد)
        if ($request->hasFile('profile_image')) {
            // حذف الصورة القديمة إذا كانت موجودة
            if ($user->profile_image) {
                Storage::disk('public')->delete('images/' . $user->profile_image);
            }
            // رفع الصورة الجديدة وتسميتها
            $image = $request->file('profile_image');
            $imageName = time() . '_' . $user->id . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('images', $imageName, 'public'); // تخزين في storage/app/public/images

            // إضافة اسم الصورة الجديدة إلى البيانات التي سيتم تحديثها
            $validatedData['profile_image'] = $imageName;
        }

        try {
            // $user->update() ستقوم بتحديث الحقول الموجودة في $validatedData فقط
            $user->update($validatedData);

            // إعادة التوجيه إلى صفحة حساب العميل مع رسالة نجاح
            return redirect()->route('frontend.account')->with('success', 'تم تحديث بيانات حسابك بنجاح!');

        } catch (\Exception $e) {
            logger("Customer Account Update Error for user {$user->id}: " . $e->getMessage());
            // إعادة التوجيه للصفحة السابقة مع عرض الأخطاء وإعادة ملء الفورم بالبيانات القديمة
            return redirect()->back()->withInput()->with('error', 'حدث خطأ أثناء تحديث البيانات. يرجى المحاولة مرة أخرى.');
        }
    }

    public function changeCustomerPassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'], 
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->new_password);
        $user->save();

      
        return redirect()->route('frontend.account')->with('success', 'تم تغيير كلمة السر بنجاح!');
    }
}