<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
        public function showPaymentMethod(Request $request, $plan_slug)
    {
        Log::info("SubscriptionController@showPaymentMethod: Called for plan_slug '{$plan_slug}' by User ID: " . (Auth::id() ?? 'Guest'));

        // 1. جلب الخطة والمستخدم المسجل
        $plan = Plan::where('slug', $plan_slug)->firstOrFail();
        $user = Auth::user(); // Middleware 'auth' يجب أن يضمن أن المستخدم مسجل

        // تحقق إضافي (على الرغم من أن middleware 'auth' يجب أن يعتني بهذا)
        if (!$user) {
            Log::error("SubscriptionController@showPaymentMethod: User not authenticated despite auth middleware for plan '{$plan_slug}'.");
            return redirect()->route('login')->with('error', 'Please login to subscribe to a plan.');
        }

        // 2. التعامل مع الخطة المجانية
        if ($plan->price == 0.00) { // أو يمكنك التحقق من $plan->slug === config('plans.free_plan_slug')
            Log::info("SubscriptionController@showPaymentMethod: Plan '{$plan->name}' (ID: {$plan->id}) is Free. Checking conditions for User ID: {$user->id}.");

            // أ. تحقق أولاً إذا كان المستخدم لديه اشتراك نشط حاليًا (لأي خطة)
            $activeExistingSubscription = $user->activeSubscriptionWithPlan(); // افترض وجود هذه الدالة في موديل User
            if ($activeExistingSubscription) {
                Log::warning("SubscriptionController@showPaymentMethod: User ID {$user->id} already has an active subscription: '{$activeExistingSubscription->plan->name}'. Cannot subscribe to free plan '{$plan->name}'.");
                return redirect()->route('dashboard') // أو frontend.pricing
                                 ->with('info', "You already have an active subscription: {$activeExistingSubscription->plan->name}. You cannot subscribe to another plan while one is active.");
            }

        
            $hasUsedThisSpecificFreePlan = $user->subscriptions()
                                              ->where('plan_id', $plan->id)
                                              ->exists();
            if ($hasUsedThisSpecificFreePlan) {
                Log::warning("SubscriptionController@showPaymentMethod: User ID {$user->id} has ALREADY USED the free plan '{$plan->name}'. Redirecting to pricing.");
                return redirect()->route('frontend.pricing')
                                 ->with('error', "You have already used the '{$plan->name}'. Please choose a paid plan to continue.");
            }

            // إذا لم يكن لديه اشتراك نشط آخر ولم يستخدم هذه الخطة المجانية من قبل، قم بالاشتراك
            Log::info("SubscriptionController@showPaymentMethod: User ID {$user->id} is ELIGIBLE for free plan '{$plan->name}'. Proceeding to createSubscriptionForUser.");
            return $this->createSubscriptionForUser($user, $plan); // دالة createSubscriptionForUser تعالج إنشاء الاشتراك وتغيير الدور
        }

        // 3. التعامل مع الخطط المدفوعة: التفاعل مع "لحظة" API
        Log::info("SubscriptionController@showPaymentMethod: Processing PAID plan '{$plan->name}' for User ID: {$user->id} using Lahza /page endpoint.");

        try {
            
            $amountInSmallestUnit = (int) round($plan->price * 100); 

         
            $payload = [
                'amount' => $amountInSmallestUnit,
                'currency' => strtolower($plan->currency), // مثال: "usd", "sar", "ils"
                'description' => "Subscription: EasyFind - {$plan->name} Plan",
                'name' => "Payment for {$plan->name}",
                'success_url' => route('lahza.payment.success', [], true), // يجب أن يكون absolute URL
                'failure_url' => route('lahza.payment.cancel', [], true),   // يجب أن يكون absolute URL
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $user->phone, // (اختياري، أرسله إذا كان متوفرًا)
                'send_email_receipt' => true,    // أو false حسب رغبتك
                'metadata' => [                  // بيانات إضافية مهمة لاسترجاعها في الـ Webhook
                    'user_id' => (string) $user->id,
                    'plan_id' => (string) $plan->id,
                    'plan_slug' => $plan->slug,
                    // يمكنك إضافة أي بيانات أخرى مفيدة هنا
                ],
                
            ];
            Log::info("SubscriptionController@showPaymentMethod: Payload to Lahza (/page): ", $payload);

            // إرسال الطلب إلى "لحظة" API
            $response = Http::withToken(config('lahza.secret_key')) // Bearer Token Authentication
                ->contentType('application/json') // التأكيد على إرسال JSON
                ->acceptJson()                   // طلب استجابة JSON
                ->post(config('lahza.base_uri', 'https://api.lahza.io/v1') . '/page', $payload); // الـ Endpoint لإنشاء صفحة دفع

            // تحليل الاستجابة
                    if ($response->successful() && isset($response->json()['data']['slug'])) { // تحقق من وجود slug
            $paymentPageSlug = $response->json()['data']['slug'];
            $paymentId = $response->json()['data']['id'] ?? null;

            
            $lahzaPaymentPageBaseUrl = config('lahza.payment_page_base_url', 'https://checkout.lahza.io/'); // مثال، أضف هذا لـ config/lahza.php
            $paymentUrl = rtrim($lahzaPaymentPageBaseUrl, '/') . '/' . $paymentPageSlug;
           

            Log::info("SubscriptionController@showPaymentMethod: Lahza Payment Page created. User ID {$user->id}. Payment ID: {$paymentId}. Slug: {$paymentPageSlug}. Constructed URL: {$paymentUrl}");

            return redirect()->away($paymentUrl); // توجيه المستخدم لصفحة دفع "لحظة"

        } else {
        
            $errorData = $response->json() ?? ['raw_body' => $response->body(), 'status_code' => $response->status()];
            Log::error("SubscriptionController@showPaymentMethod: Lahza Create Page Error (or slug not found) for User ID {$user->id}. Status: {$response->status()}. Response: ", $errorData);
            $errorMessage = $errorData['message'] ?? 'Could not retrieve payment page details from Lahza.';
            if (is_array($errorMessage) && isset($errorMessage[0])) $errorMessage = $errorMessage[0];
            return redirect()->route('frontend.pricing')->with('error', (string)$errorMessage);
        }
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('SubscriptionController@showPaymentMethod: Lahza API Request Exception for User ID ' . $user->id . ': ' . $e->getMessage(), ['response_body' => $e->response ? $e->response->body() : 'N/A']);
            return redirect()->route('frontend.pricing')->with('error', 'Error connecting to the payment service. Please try again later.');
        } catch (\Exception $e) {
            Log::error('SubscriptionController@showPaymentMethod: General Exception for User ID ' . $user->id . ': ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->route('frontend.pricing')->with('error', 'An unexpected error occurred while preparing your payment. Please contact support.');
        }
    }
    public function handleLahzaSuccess(Request $request)
    {
        Log::info("Lahza Payment Success Callback Received. Request data: ", $request->all());
        
        $transactionId = $request->query('transaction_id'); // أو أي اسم بارامتر يرسله "لحظة"
        

        return redirect()->route('dashboard') // أو صفحة مخصصة لنجاح الدفع
            ->with('success', 'Thank you! Your payment is being processed. Your subscription will be activated shortly.');
    }

    public function handleLahzaCancel(Request $request)
    {
        Log::info("Lahza Payment Cancel/Failure Callback Received. Request data: ", $request->all());
        return redirect()->route('frontend.pricing')
            ->with('error', 'Your payment was cancelled or failed. Please try again or choose a different payment method.');
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