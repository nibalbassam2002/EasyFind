<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; 
use Laravel\Socialite\Facades\Socialite;
use Exception;
use Laravel\Socialite\Two\InvalidStateException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Str;        

class SocialiteController extends Controller
{
    private $defaultRole = 'customer';

    private $supportedProviders = ['google', 'facebook']; 

    public function redirectToProvider(string $provider)
    {
        Log::info("Socialite - redirectToProvider: Attempting redirect for [{$provider}]");

        // *** تعديل التحقق ليشمل كل المزودات المدعومة ***
        if (!in_array(strtolower($provider), $this->supportedProviders)) {
            Log::warning("Socialite - redirectToProvider: Unsupported provider [{$provider}]");
            return redirect()->route('register')->with('error', 'Login with ' . ucfirst($provider) . ' is not supported.');
        }

        try {
            // *** استخدام $provider ديناميكيًا ***
            return Socialite::driver(strtolower($provider))->redirect();
        } catch (Exception $e) {
            Log::error("Socialite - redirectToProvider: Exception for [{$provider}]", ['message' => $e->getMessage()]);
            return redirect()->route('register')->with('error', 'Error connecting to ' . ucfirst($provider) . '. Please try again.');
        }
    }

    public function handleProviderCallback(string $provider)
    {
        Log::info("Socialite - handleProviderCallback: Starting for [{$provider}]");

        // *** تعديل التحقق ليشمل كل المزودات المدعومة ***
        if (!in_array(strtolower($provider), $this->supportedProviders)) {
            Log::warning("Socialite - handleProviderCallback: Unsupported provider callback [{$provider}]");
            return redirect()->route('register')->with('error', 'Login with ' . ucfirst($provider) . ' is not supported.');
        }

        try {
            // *** استخدام $provider ديناميكيًا ***
            $socialUser = Socialite::driver(strtolower($provider))->user();
            Log::info("Socialite - handleProviderCallback: Fetched user from " . ucfirst($provider) . ".", [
                'id' => $socialUser->getId(),
                'email' => $socialUser->getEmail(),
                'name' => $socialUser->getName()
            ]);

            if (empty($socialUser->getEmail())) {
                Log::warning("Socialite - handleProviderCallback: Email not provided by " . ucfirst($provider) . ".", ['provider_user_id' => $socialUser->getId()]);
                return redirect()->route('register')->with('error', ucfirst($provider) . ' did not provide an email. Please register manually or try a different ' . ucfirst($provider) . ' account that shares its email address.');
            }

            // *** تعديل اسم الميثود واستدعائها ***
            $user = $this->findOrCreateSocialUser($socialUser, strtolower($provider)); // <--- تمرير المزود

            if (!$user) {
                Log::critical("Socialite - handleProviderCallback: findOrCreateSocialUser returned null for provider [{$provider}]. This should not happen.");
                return redirect()->route('register')->with('error', 'An unexpected error occurred while processing your account. Please try registering manually or contact support.');
            }

            Auth::login($user, true);
            Log::info("Socialite - handleProviderCallback: User logged in successfully via [{$provider}].", ['user_id' => $user->id, 'email' => $user->email, 'role' => $user->role]);

            return redirect()->intended($this->redirectTo($user));

        } catch (InvalidStateException $e) {
            Log::error("Socialite - handleProviderCallback: InvalidStateException for [{$provider}].", ['exception_message' => $e->getMessage()]);
            return redirect()->route('register')->with('error', 'Invalid authentication state. Please try the login process again.');
        } catch (QueryException $e) {
            // ... (كود معالجة QueryException لديك جيد، فقط تأكدي من رسائل الخطأ إذا أردت) ...
             $errorCode = $e->errorInfo[1] ?? null;
            $errorMessage = $e->errorInfo[2] ?? $e->getMessage();
            Log::error("Socialite - handleProviderCallback: QueryException for [{$provider}].", [
                'email_from_provider' => isset($socialUser) ? $socialUser->getEmail() : 'N/A',
                'error_code' => $errorCode,
                'db_error_message' => $errorMessage,
                'full_exception_message' => $e->getMessage()
            ]);
            if ($errorCode == 1062) {
                return redirect()->route('login')->with('error', 'This email or social account is already linked to an existing user. Please log in or use a different account.');
            }
            return redirect()->route('register')->with('error', 'A database error occurred while setting up your account. Please try again or register manually.');
        } catch (Exception $e) {
            Log::error("Socialite - handleProviderCallback: Generic Exception for [{$provider}].", [
                'email_from_provider' => isset($socialUser) ? $socialUser->getEmail() : 'N/A',
                'exception_message' => $e->getMessage(),
                'trace_excerpt' => substr($e->getTraceAsString(), 0, 500)
            ]);
            return redirect()->route('register')->with('error', 'An unexpected error occurred with '.ucfirst($provider).'. Please try again. If the problem persists, contact support.');
        }
    }

    /**
     * Find an existing user by Social Provider ID or Email, or create a new one.
     * Assigns 'customer' role by default to new users.
     * *** تم إعادة تسمية الميثود وتعديلها لتكون عامة ***
     */
    protected function findOrCreateSocialUser(\Laravel\Socialite\Contracts\User $socialProviderUser, string $providerName): ?User
    {
        // $providerName يتم تمريره الآن (مثلاً 'google' أو 'facebook')
        $providerId = $socialProviderUser->getId();
        $email = $socialProviderUser->getEmail();
        $name = $socialProviderUser->getName();
        $avatar = $socialProviderUser->getAvatar();

        // 1. ابحث عن المستخدم عن طريق provider_name و provider_id
        $user = User::where('provider_name', $providerName)
                    ->where('provider_id', $providerId)
                    ->first();

        if ($user) {
            Log::info("Socialite - findOrCreateSocialUser: User found by provider_id for [{$providerName}].", ['user_id' => $user->id]);
            // تحديث الاسم والصورة إذا لزم الأمر
            $user->name = $name ?? $user->name;
            $user->provider_avatar = $avatar ?? $user->provider_avatar; // تأكدي أن لديك عمود provider_avatar
            if ($user->isDirty()) {
                $user->save();
                Log::info("Socialite - findOrCreateSocialUser: Updated existing user's name/avatar for [{$providerName}].", ['user_id' => $user->id]);
            }
            return $user;
        }

        // 2. إذا لم يوجد بـ provider_id، ابحث عن طريق البريد الإلكتروني
        Log::info("Socialite - findOrCreateSocialUser: User not found by provider_id for [{$providerName}]. Checking email: {$email}");
        $user = User::where('email', $email)->first();

        if ($user) {
            Log::info("Socialite - findOrCreateSocialUser: User found by email. Linking [{$providerName}] account.", ['user_id' => $user->id]);

            if ($user->provider_name && $user->provider_name !== $providerName) {
                Log::warning("Socialite - findOrCreateSocialUser: Email [{$email}] already linked to [{$user->provider_name}]. Cannot link to [{$providerName}].");
                // يمكنك هنا إما رمي استثناء أو إعادة توجيه مع رسالة خطأ
                // سأرمي استثناء ليتم التقاطه في handleProviderCallback
                throw new Exception("This email is already associated with an account using " . ucfirst($user->provider_name) . ". Please log in with that method or use a different email with " . ucfirst($providerName) . ".");
            }
             // إذا لم يكن للمستخدم provider_name (سجل يدويًا) أو كان نفس المزود (نادر الحدوث هنا)
            $user->provider_name = $providerName;
            $user->provider_id = $providerId;
            $user->provider_avatar = $avatar ?? $user->provider_avatar;
            $user->name = $name ?? $user->name;
            $user->email_verified_at = $user->email_verified_at ?? now();
            $user->save();
            Log::info("Socialite - findOrCreateSocialUser: Linked [{$providerName}] account to existing user by email.", ['user_id' => $user->id]);
            return $user;
        }

        // 3. مستخدم جديد تمامًا
        Log::info("Socialite - findOrCreateSocialUser: Creating new user for email [{$email}] with provider [{$providerName}].");
        $userDataToCreate = [
            'name' => $name,
            'email' => $email,
            'email_verified_at' => now(),
            'password' => null, // *** الخيار المفضل: اجعل المستخدم يعينها لاحقًا ***
            // أو: 'password' => Hash::make(Str::random(16)), // إذا أردت إنشاء كلمة مرور عشوائية
            'provider_name' => $providerName,
            'provider_id' => $providerId,
            'provider_avatar' => $avatar, // تأكدي أن لديك هذا العمود في جدول users
            'has_set_password' => false, // إذا كانت كلمة المرور null
            'role' => $this->defaultRole,
            'status' => 'active',
        ];
        Log::info("Socialite - findOrCreateSocialUser: Data for new user creation with [{$providerName}]:", $userDataToCreate);
        $createdUser = User::create($userDataToCreate);
        Log::info("Socialite - findOrCreateSocialUser: New user created successfully with [{$providerName}].", ['user_id' => $createdUser->id]);
        return $createdUser;
    }

    protected function redirectTo(User $user)
    {
        // ... (هذه الميثود تبدو جيدة كما هي لديك) ...
        $intendedUrl = session()->pull('url.intended');
        $defaultRedirect = route('frontend.home');

        if ($intendedUrl) {
            $path = parse_url($intendedUrl, PHP_URL_PATH);
            $loginPath = parse_url(route('login', [], false), PHP_URL_PATH);
            $registerPath = parse_url(route('register', [], false), PHP_URL_PATH);

            if ($path && !in_array($path, [$loginPath, $registerPath])) {
                Log::info("Socialite - redirectTo: Redirecting to intended URL: {$intendedUrl}");
                return $intendedUrl;
            }
        }
        Log::info("Socialite - redirectTo: Redirecting to default customer path: {$defaultRedirect}");
        return $defaultRedirect;
    }
}