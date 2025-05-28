<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User; // تأكد من وجود هذا
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // لإضافة التسجيل إذا أردت

class SubscriptionController extends Controller
{
    public function showPaymentMethod(Request $request, $plan_slug)
    {
        Log::info("SubscriptionController@showPaymentMethod: Called for plan_slug '{$plan_slug}' by User ID: " . (Auth::id() ?? 'Guest'));

        $plan = Plan::where('slug', $plan_slug)->firstOrFail();
        $user = Auth::user(); // Middleware 'auth' يجب أن يضمن أن المستخدم مسجل

        if (!$user) {
            // هذا يجب ألا يحدث إذا كان Middleware يعمل
            Log::error("SubscriptionController@showPaymentMethod: User not authenticated despite auth middleware for plan '{$plan_slug}'.");
            return redirect()->route('login')->with('error', 'Please login to subscribe to a plan.');
        }

        // --- التعامل مع الخطة المجانية ---
        if ($plan->price == 0.00) { // أو يمكنك التحقق من $plan->slug === 'free-plan-slug'
            Log::info("SubscriptionController@showPaymentMethod: Plan '{$plan->name}' is Free. Checking previous subscriptions for User ID: {$user->id}.");

            // 1. تحقق أولاً إذا كان المستخدم لديه اشتراك نشط حاليًا (لأي خطة)
            $activeExistingSubscription = $user->subscriptions()
                                       ->where('status', 'active')
                                       ->where(function ($query) {
                                           $query->whereNull('ends_at')
                                                 ->orWhere('ends_at', '>', now());
                                       })
                                       ->first();
            if ($activeExistingSubscription) {
                Log::warning("SubscriptionController@showPaymentMethod: User ID {$user->id} already has an active subscription: '{$activeExistingSubscription->plan->name}'. Redirecting for plan '{$plan->name}'.");
                return redirect()->route('dashboard')
                                 ->with('info', "You already have an active subscription: {$activeExistingSubscription->plan->name}. You cannot subscribe to another plan while one is active.");
            }

            // 2. تحقق إذا كان المستخدم قد اشترك *سابقًا* في هذه الخطة المجانية المحددة
            $hasUsedThisSpecificFreePlan = $user->subscriptions()
                                              ->where('plan_id', $plan->id)
                                              ->exists();

            if ($hasUsedThisSpecificFreePlan) {
                Log::warning("SubscriptionController@showPaymentMethod: User ID {$user->id} has already used the free plan '{$plan->name}'. Redirecting.");
                return redirect()->route('frontend.pricing')
                                 ->with('error', "You have already used the '{$plan->name}'. Please choose a paid plan to continue.");
            }

            // إذا لم يكن لديه اشتراك نشط ولم يستخدم هذه الخطة المجانية من قبل، قم بالاشتراك
            Log::info("SubscriptionController@showPaymentMethod: User ID {$user->id} is eligible for free plan '{$plan->name}'. Proceeding to create subscription.");
            return $this->createSubscriptionForUser($user, $plan);
        }

        // --- التعامل مع الخطط المدفوعة (للمستقبل) ---
        Log::info("SubscriptionController@showPaymentMethod: Plan '{$plan->name}' is Paid. Redirecting to pricing with info for User ID: {$user->id}.");
        return redirect()->route('frontend.pricing')
                         ->with('info', "Payment processing for the '{$plan->name}' plan is not yet implemented. Please choose the Free plan if available.");
    }

    protected function createSubscriptionForUser(User $user, Plan $plan, array $paymentData = [])
    {
        Log::info("SubscriptionController@createSubscriptionForUser: Attempting to create subscription for User ID {$user->id} to Plan '{$plan->name}' (ID: {$plan->id}).");

        // إعادة التحقق (احتياطي)، خاصة للخطة المجانية
        if ($plan->price == 0.00) {
            $hasUsedThisFreePlan = $user->subscriptions()
                                       ->where('plan_id', $plan->id)
                                       ->exists();
            if ($hasUsedThisFreePlan) {
                Log.error("SubscriptionController@createSubscriptionForUser: CRITICAL - User ID {$user->id} somehow bypassed initial check and is trying to re-subscribe to free plan '{$plan->name}'. Aborting.");
                return redirect()->route('frontend.pricing')->with('error', "Subscription to '{$plan->name}' failed. You may have already used this plan.");
            }
        }
        // (يمكن إضافة تحقق من وجود اشتراك نشط آخر هنا أيضًا كطبقة أمان إضافية إذا لم تكن واثقًا من تدفق showPaymentMethod)


        $planFeatures = $plan->features ?? [];
        $metadata = [
            'max_properties' => $planFeatures['max_properties'] ?? 0,
            'allowed_types' => $planFeatures['allowed_types'] ?? [],
            'featured_slots' => $planFeatures['featured_slots'] ?? 0,
            'max_images_per_property' => $planFeatures['max_images_per_property'] ?? 5,
        ];

        $newSubscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => $plan->duration_in_days > 0 ? now()->addDays($plan->duration_in_days) : null,
            'status' => 'active',
            'properties_listed_count' => 0,
            'metadata' => $metadata,
            'payment_gateway' => $paymentData['gateway'] ?? null,
            'payment_transaction_id' => $paymentData['transaction_id'] ?? null,
            'payment_details' => $paymentData['details'] ?? null,
        ]);

        if ($newSubscription) {
            Log::info("SubscriptionController@createSubscriptionForUser: Successfully created Subscription ID {$newSubscription->id} for User ID {$user->id} to Plan '{$plan->name}'.");

            // ▼▼▼ النقطة الأهم: تغيير دور المستخدم إذا كانت الخطة مجانية وكان دوره customer ▼▼▼
            if ($user->role === 'customer' && $plan->price == 0.00) {
                $originalRole = $user->role;
                $user->role = 'property_lister'; // أو 'seller' أو أي دور تستخدمه للبائعين
                $user->save();
                Log::info("SubscriptionController@createSubscriptionForUser: User ID {$user->id} role changed from '{$originalRole}' to '{$user->role}' after subscribing to free plan '{$plan->name}'.");
            }
            // ▲▲▲ نهاية تغيير الدور ▲▲▲

            $successMessage = "You have successfully subscribed to the {$plan->name} plan!";
            if ($user->role === 'property_lister' && $plan->price == 0.00) {
                $successMessage .= " You can now start listing your properties.";
            }
            return redirect()->route('dashboard')->with('success', $successMessage);

        } else {
            Log::error("SubscriptionController@createSubscriptionForUser: Failed to create subscription for User ID {$user->id} to Plan '{$plan->name}'.");
            return redirect()->route('frontend.pricing')->with('error', 'Failed to subscribe to the plan. Please try again.');
        }
    }

    // دالة subscribeViaDirectRoute (إذا كنت تستخدمها) يجب أن تحتوي على نفس منطق التحقق الموجود في showPaymentMethod
    public function subscribeViaDirectRoute(Request $request, Plan $plan)
    {
        Log::info("SubscriptionController@subscribeViaDirectRoute: Called for plan '{$plan->name}' by User ID: " . (Auth::id() ?? 'Guest'));
        $user = Auth::user();

        if (!$user) {
            Log::error("SubscriptionController@subscribeViaDirectRoute: User not authenticated for plan '{$plan->name}'.");
            return redirect()->route('login')->with('error', 'Please login to subscribe.');
        }

        if ($plan->price == 0.00) {
            Log::info("SubscriptionController@subscribeViaDirectRoute: Plan '{$plan->name}' is Free. Checking previous subscriptions for User ID: {$user->id}.");
             $activeExistingSubscription = $user->subscriptions() /* ... نفس تحقق الاشتراك النشط ... */ ->first();
             if ($activeExistingSubscription) {
                 Log::warning("SubscriptionController@subscribeViaDirectRoute: User ID {$user->id} already has an active subscription. Redirecting.");
                 return redirect()->route('dashboard')->with('info', "You already have an active subscription: {$activeExistingSubscription->plan->name}.");
             }
            $hasUsedThisSpecificFreePlan = $user->subscriptions()->where('plan_id', $plan->id)->exists();
            if ($hasUsedThisSpecificFreePlan) {
                Log::warning("SubscriptionController@subscribeViaDirectRoute: User ID {$user->id} has already used the free plan '{$plan->name}'. Redirecting.");
                return redirect()->route('frontend.pricing')->with('error', "You have already used the '{$plan->name}'. Please choose a paid plan.");
            }
            Log::info("SubscriptionController@subscribeViaDirectRoute: User ID {$user->id} is eligible for free plan '{$plan->name}'. Proceeding to create subscription.");
            return $this->createSubscriptionForUser($user, $plan);
        } else {
            Log::info("SubscriptionController@subscribeViaDirectRoute: Plan '{$plan->name}' is Paid. Redirecting to payment method for User ID: {$user->id}.");
            // للخطط المدفوعة, من الأفضل توجيهه لـ showPaymentMethod لإمكانية إضافة خطوات دفع مستقبلية
            return redirect()->route('frontend.checkout.payment_method', ['plan_slug' => $plan->slug]);
        }
    }
}