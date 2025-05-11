<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController; // تأكد من استيراد الكنترولر
use App\Http\Controllers\ManagementController;
use App\Http\Controllers\PropertyListerController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\ModeratorController;
use App\Http\Controllers\FavoriteController;
use App\Models\Governorate; // تأكد من استيراد المودل
use Illuminate\Support\Facades\Auth;

// --- المسارات العامة (لا تتطلب تسجيل دخول) ---
Route::get('/', [FrontendController::class, 'index'])->name('frontend.home');
Route::get('/properties', [FrontendController::class, 'properties'])->name('frontend.properties');
Route::get('/properties/{property}', [FrontendController::class, 'showProperty'])->name('frontend.property.show');

// --- مسارات تسجيل الدخول / التسجيل ---
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');


// --- مجموعة المسارات التي تتطلب تسجيل الدخول ---
// --- كل المسارات هنا تتطلب أن يكون المستخدم مسجل الدخول ---
Route::middleware(['auth'])->group(function () {

    // --- تسجيل الخروج ---
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // --- صفحة حساب العميل (Customer Account Page) ---
    Route::get('/my-account', function () {
        // التحقق من الدور (اختياري هنا إذا كان هناك middleware آخر للأدوار)
        if (Auth::user()->role !== 'customer') {
            // يمكن إعادة توجيهه للداشبورد أو إظهار خطأ 403
            return redirect()->route('dashboard');
        }
        // جلب بيانات المستخدم والمحافظات
        $user = Auth::user()->load('area.governorate');
        $governorates = Governorate::with('areas')->orderBy('name')->get();
        // إرجاع الـ view مع البيانات
        return view('frontend.account', [
            'user' => $user,
            'governorates' => $governorates
        ]);
        // --- لا تضع تعريفات مسارات أخرى هنا ---
    })->name('frontend.account'); // لم يعد بحاجة لـ ->middleware('auth') هنا

    // تحديث بيانات حساب العميل (يشمل الصورة)
    Route::patch('/my-account/update', [ProfileController::class, 'updateCustomerProfile'])
          ->name('frontend.account.update');

    // تغيير كلمة مرور العميل
    Route::patch('/my-account/change-password', [ProfileController::class, 'changeCustomerPassword']) // استخدم اسم الدالة المتفق عليه
          ->name('frontend.account.changepassword');

 
    Route::delete('/profile/image/delete', [ProfileController::class, 'deleteImage'])
          ->name('profile.deleteImage');


    Route::get('/my-favourites', [FrontendController::class, 'favorites'])->name('frontend.favorites');
    Route::post('/favorites/toggle', [FavoriteController::class, 'toggle'])->name('favorites.toggle'); 
    Route::delete('/favorites/remove/{property}', [FavoriteController::class, 'remove'])->name('favorites.remove');

 
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData'])->name('dashboard.chartData');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index'); // عرض بروفايل الداشبورد
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update'); // تحديث بروفايل الداشبورد
    // Route::delete('/profile/image/delete', ...) // مسار الحذف معرف أعلاه
    Route::patch('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.changePassword'); // تغيير كلمة سر الداشبورد
    Route::get('/pricing-plans', [App\Http\Controllers\FrontendController::class, 'showPricingPlans'])->name('frontend.pricing');

    // --- مجموعة مسارات الإدارة الخاصة بالـ Admin ---
    Route::middleware(['role:admin'])
        ->prefix('dashboard/admin')
        ->name('admin.')
        ->group(function () {
            Route::resource('users', ManagementController::class)->except(['show']);
        });

    // --- مجموعة مسارات Lister ---
    Route::middleware(['role:property_lister'])
        ->prefix('my-properties')
        ->name('lister.')
        ->group(function () {
            Route::resource('properties', PropertyListerController::class);
        });


    Route::middleware(['role:admin,content_moderator']) 
        ->prefix('dashboard/moderator')
        ->name('moderator.')
        ->group(function () {
            Route::get('pending-properties', [ModeratorController::class, 'pendingProperties'])->name('properties.pending');
            Route::patch('properties/{property}/approve', [ModeratorController::class, 'approveProperty'])->name('properties.approve');
            Route::patch('properties/{property}/reject', [ModeratorController::class, 'rejectProperty'])->name('properties.reject');
        });

});


Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});