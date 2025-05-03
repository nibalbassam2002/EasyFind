<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ManagementController;
use App\Http\Controllers\PropertyListerController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\ModeratorController; 
use App\Http\Controllers\FavoriteController;


Route::get('/', [FrontendController::class, 'index'])->name('frontend.home');
Route::get('/properties', [FrontendController::class, 'properties'])->name('frontend.properties');
Route::get('/properties/{property}', [FrontendController::class, 'showProperty'])->name('frontend.property.show');

// --- مسارات تسجيل الدخول / التسجيل ---
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

// --- مجموعة المسارات التي تتطلب تسجيل الدخول ---
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // --- Dashboard ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData'])->name('dashboard.chartData');

    // --- Profile ---
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/image/delete', [ProfileController::class, 'deleteImage'])->name('profile.deleteImage');
    Route::patch('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.changePassword');

    // --- Favorites ---
    Route::get('/my-favourites', [FrontendController::class, 'favorites'])->name('frontend.favorites');

    // --- مجموعة مسارات الإدارة الخاصة بالـ admin ---
    Route::middleware(['role:admin'])
        ->prefix('dashboard/admin')
        ->name('admin.')
        ->group(function () {
            Route::resource('users', ManagementController::class)->except(['show']);
    
        });

    // --- مجموعة مسارات مدير العقارات (Property Lister) ---
    Route::middleware(['role:property_lister'])
        ->prefix('my-properties')
        ->name('lister.')
        ->group(function () {
            Route::resource('properties', PropertyListerController::class);
            
        });

    // --- مجموعة مسارات مشرف المحتوى (Content Moderator) ---
    Route::middleware(['role:admin,content_moderator']) // السماح للأدمن والمشرف بالوصول
        ->prefix('dashboard/moderator')
        ->name('moderator.')
        ->group(function () {
            // عرض العقارات المعلقة للمراجعة
            Route::get('pending-properties', [ModeratorController::class, 'pendingProperties'])
                 ->name('properties.pending'); 

            // الموافقة على عقار
            Route::patch('properties/{property}/approve', [ModeratorController::class, 'approveProperty'])
                 ->name('properties.approve'); 

            // رفض عقار
            Route::patch('properties/{property}/reject', [ModeratorController::class, 'rejectProperty'])
                 ->name('properties.reject'); // الاسم: moderator.properties.reject

        });
    

}); 

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});