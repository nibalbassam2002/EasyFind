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
use Illuminate\Support\Facades\Auth;


Route::get('/', [FrontendController::class, 'index'])->name('frontend.home');
Route::get('/properties', [FrontendController::class, 'properties'])->name('frontend.properties');
Route::get('/properties/{property}', [FrontendController::class, 'showProperty'])->name('frontend.property.show');


Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');



Route::middleware(['auth'])->group(function () {

    
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

  
    Route::get('/my-account', function () {
      
        if (Auth::user()->role !== 'customer') {
           
            return redirect()->route('dashboard');
        }
        
        $user = Auth::user()->load('area.governorate');
        $governorates = Governorate::with('areas')->orderBy('name')->get();
       
        return view('frontend.account', [
            'user' => $user,
            'governorates' => $governorates
        ]);
        
    })->name('frontend.account'); 

    
    Route::patch('/my-account/update', [ProfileController::class, 'updateCustomerProfile'])
          ->name('frontend.account.update');

    
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
   
    Route::patch('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.changePassword'); // تغيير كلمة سر الداشبورد
    Route::get('/pricing-plans', [FrontendController::class, 'showPricingPlans'])->name('frontend.pricing');
    Route::get('/checkout/{plan_slug}/payment-method', [SubscriptionController::class, 'showPaymentMethod'])
    ->name('frontend.checkout.payment_method');
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
    Route::prefix('chat')->name('chat.')->group(function () {
        
    Route::get('/{activeConversation?}', [ChatController::class, 'index'])
             ->name('index');

        
    Route::post('/conversations/{conversation}/messages', [ChatController::class, 'sendMessage'])
             ->name('messages.store');

        
    Route::get('/start/{recipient}', [ChatController::class, 'createOrFindConversation'])
             ->name('conversation.start'); 

        
    Route::get('/conversations/{conversation}/messages', [ChatController::class, 'fetchMessages'])
              ->name('messages.fetch');
    });
    Route::post('/feedback', [FeedbackController::class, 'storeUserFeedback'])->name('feedback.store');
    Route::middleware(['role:admin,content_moderator'])
    ->prefix('dashboard/moderator')
    ->name('moderator.')
    ->group(function () {
        Route::get('pending-properties', [ModeratorController::class, 'pendingProperties'])->name('properties.pending');
        Route::patch('properties/{property}/approve', [ModeratorController::class, 'approveProperty'])->name('properties.approve');
        Route::patch('properties/{property}/reject', [ModeratorController::class, 'rejectProperty'])->name('properties.reject');
        
    });
    
    Route::view('/help-center', 'frontend.help-center')->name('frontend.help_center');

    
});
Route::middleware(['auth', 'role:admin,content_moderator']) 
    ->prefix('dashboard/moderator')
    ->name('moderator.')
    ->group(function () {
        
        Route::get('feedback', [FeedbackController::class, 'indexAdminFeedbacks'])->name('feedback.index'); 
        Route::get('feedback/{feedback}', [FeedbackController::class, 'showAdminFeedback'])->name('feedback.show'); 
        Route::post('feedback/{feedback}/reply', [FeedbackController::class, 'replyToFeedback'])->name('feedback.reply'); 
        Route::patch('feedback/{feedback}/status', [FeedbackController::class, 'updateFeedbackStatus'])->name('feedback.updateStatus');
    });

    Route::patch('/notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread.count'); // لجلب عدد الإشعارات غير المقروءة
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index'); 
    Route::get('/my-notifications', [NotificationController::class, 'index'])->name('frontend.notifications.index')->defaults('view_path', 'frontend.notifications.index');
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});