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
use App\Http\Controllers\ChatController;
use App\Models\Governorate; 
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\SocialiteController;
use Illuminate\Support\Facades\Auth; 
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\AccountController; 

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// ============================================
// == Routes for Frontend (Publicly Accessible)
// ============================================
Route::get('/', [FrontendController::class, 'index'])->name('frontend.home');
Route::get('/properties', [FrontendController::class, 'properties'])->name('frontend.properties');
// ▼▼▼ هذا هو المسار لعرض تفاصيل العقار في الواجهة الأمامية ▼▼▼
Route::get('/properties/{property}', [FrontendController::class, 'showProperty'])->name('frontend.property.show');
Route::get('/pricing-plans', [FrontendController::class, 'showPricingPlans'])->name('frontend.pricing');
Route::view('/help-center', 'frontend.help-center')->name('frontend.help_center');
Route::get('/terms-and-conditions', function () { return view('Auth.terms'); })->name('legal.terms');
Route::get('/privacy', function () { return view('Auth.privacy'); })->name('privacy');
Route::get('/about-us', function () { return view('frontend.about'); })->name('frontend.about');

// ============================================
// == Authentication Routes
// ============================================
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

Route::get('/auth/{provider}/redirect', [SocialiteController::class, 'redirectToProvider'])->name('socialite.redirect');
Route::get('/auth/{provider}/callback', [SocialiteController::class, 'handleProviderCallback'])->name('socialite.callback');

// Password Reset Routes
Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->middleware('guest')->name('password.request');
Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('guest')->name('password.email');
Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->middleware('guest')->name('password.reset');
Route::post('reset-password', [ResetPasswordController::class, 'reset'])->middleware('guest')->name('password.update');


// ============================================
// == Authenticated User Routes
// ============================================
Route::middleware(['auth'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // --- Customer Specific Routes (Frontend Account) ---
    Route::get('/my-account', function () {
        if (Auth::user()->role !== 'customer') {
            return redirect()->route('dashboard');
        }
        $user = Auth::user()->load('area.governorate');
        $governorates = Governorate::with('areas')->orderBy('name')->get();
        return view('frontend.account', compact('user', 'governorates'));
    })->name('frontend.account');

    Route::patch('/my-account/update', [ProfileController::class, 'updateCustomerProfile'])->name('frontend.account.update');
    Route::patch('/my-account/change-password', [ProfileController::class, 'changeCustomerPassword'])->name('frontend.account.changepassword');
    
    Route::get('/my-account/set-initial-password', [ProfileController::class, 'showSetInitialPasswordForm'])
        ->name('profile.show_set_initial_password_form')
        ->middleware('password.notset'); // middleware لفحص إذا كانت كلمة السر لم تُعين

    Route::post('/my-account/set-initial-password', [ProfileController::class, 'storeInitialPassword'])
        ->name('profile.store_initial_password')
        ->middleware('password.notset');
    Route::post('/profile/store-initial-password-dashboard', [ProfileController::class, 'storeInitialPasswordDashboard'])
     ->name('profile.store_initial_password_dashboard');;

    // --- Favorites ---
    Route::get('/my-favourites', [FrontendController::class, 'favorites'])->name('frontend.favorites');
    Route::post('/favorites/toggle', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::delete('/favorites/remove/{property}', [FavoriteController::class, 'remove'])->name('favorites.remove');

    // --- Dashboard (Common for authenticated users, content differs by role) ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData'])->name('dashboard.chartData');

    // --- Profile (Dashboard Profile) ---
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.changePassword');
    Route::delete('/profile/image/delete', [ProfileController::class, 'deleteImage'])->name('profile.deleteImage');


    // --- Subscription Routes ---
    Route::get('/checkout/{plan_slug}/payment-method', [SubscriptionController::class, 'showPaymentMethod'])->name('frontend.checkout.payment_method');
    Route::get('/subscribe/{plan:slug}', [SubscriptionController::class, 'subscribeViaDirectRoute']) // تم تعديل اسم الدالة هنا ليتطابق
         ->name('subscriptions.subscribe');


    // --- Admin Specific Routes ---
    Route::middleware(['role:admin'])
        ->prefix('dashboard/admin')
        ->name('admin.')
        ->group(function () {
            Route::resource('users', ManagementController::class)->except(['show']);
            // يمكنك إضافة مسارات أخرى خاصة بالأدمن هنا
        });

    // --- Property Lister Specific Routes ---
    Route::middleware(['role:property_lister'])
        ->prefix('my-properties') // هذه هي البادئة الصحيحة
        ->name('lister.')
        ->group(function () {
            // Route::resource('properties', PropertyListerController::class); // هذا جيد
            // أو إذا أردت تعريف كل مسار على حدة للتحكم الكامل:
            Route::get('properties', [PropertyListerController::class, 'index'])->name('properties.index');
            Route::get('properties/create', [PropertyListerController::class, 'create'])->name('properties.create');
            Route::post('properties', [PropertyListerController::class, 'store'])->name('properties.store');
            // ▼▼▼ هذا هو المسار المهم لعرض تفاصيل العقار في الداشبورد ▼▼▼
            Route::get('properties/{property}', [PropertyListerController::class, 'show'])->name('properties.show');
            Route::get('properties/{property}/edit', [PropertyListerController::class, 'edit'])->name('properties.edit');
            Route::put('properties/{property}', [PropertyListerController::class, 'update'])->name('properties.update');
            Route::delete('properties/{property}', [PropertyListerController::class, 'destroy'])->name('properties.destroy');
            
        });


    // --- Moderator Specific Routes (Admin can also access these) ---
    Route::middleware(['role:admin,content_moderator'])
        ->prefix('dashboard/moderator')
        ->name('moderator.')
        ->group(function () {
            Route::get('pending-properties', [ModeratorController::class, 'pendingProperties'])->name('properties.pending');
            Route::patch('properties/{property}/approve', [ModeratorController::class, 'approveProperty'])->name('properties.approve');
            Route::patch('properties/{property}/reject', [ModeratorController::class, 'rejectProperty'])->name('properties.reject');
            // Feedback management for moderators/admin
            Route::get('feedback', [FeedbackController::class, 'indexAdminFeedbacks'])->name('feedback.index');
            Route::get('feedback/{feedback}', [FeedbackController::class, 'showAdminFeedback'])->name('feedback.show');
            Route::post('feedback/{feedback}/reply', [FeedbackController::class, 'replyToFeedback'])->name('feedback.reply');
            Route::patch('feedback/{feedback}/status', [FeedbackController::class, 'updateFeedbackStatus'])->name('feedback.updateStatus');
        });

    // --- Chat Routes ---
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/{activeConversation?}', [ChatController::class, 'index'])->name('index');
        Route::post('/conversations/{conversation}/messages', [ChatController::class, 'sendMessage'])->name('messages.store');
        Route::get('/start/{recipient}', [ChatController::class, 'createOrFindConversation'])->name('conversation.start');
        Route::get('/conversations/{conversation}/messages', [ChatController::class, 'fetchMessages'])->name('messages.fetch');
    });

    // --- User Feedback Submission ---
    Route::post('/feedback', [FeedbackController::class, 'storeUserFeedback'])->name('feedback.store');

    // --- Notifications ---
    Route::patch('/notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread.count');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index'); // Dashboard notifications
    Route::get('/my-notifications', [NotificationController::class, 'index'])->name('frontend.notifications.index')->defaults('view_path', 'frontend.notifications.index'); // Frontend notifications

    // Set Initial Password (if using AccountController)
    // تأكد من أن AccountController موجود ومُعرف بشكل صحيح
    // Route::get('/set-initial-password', [AccountController::class, 'showSetInitialPasswordForm'])->name('frontend.social.set_initial_password_form');
    // Route::post('/set-initial-password', [AccountController::class, 'processSetInitialPassword'])->name('frontend.social.process_initial_password');

});


Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});