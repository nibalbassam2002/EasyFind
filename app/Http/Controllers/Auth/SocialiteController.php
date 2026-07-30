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

        if (!in_array(strtolower($provider), $this->supportedProviders)) {
            Log::warning("Socialite - redirectToProvider: Unsupported provider [{$provider}]");
            return redirect()->route('register')->with('error', 'Login with ' . ucfirst($provider) . ' is not supported.');
        }

        try {
            return Socialite::driver(strtolower($provider))->redirect();
        } catch (Exception $e) {
            Log::error("Socialite - redirectToProvider: Exception for [{$provider}]", ['message' => $e->getMessage()]);
            return redirect()->route('register')->with('error', 'Error connecting to ' . ucfirst($provider) . '. Please try again.');
        }
    }

    public function handleProviderCallback(string $provider)
    {
        Log::info("Socialite - handleProviderCallback: Starting for [{$provider}]");

        
        if (!in_array(strtolower($provider), $this->supportedProviders)) {
            Log::warning("Socialite - handleProviderCallback: Unsupported provider callback [{$provider}]");
            return redirect()->route('register')->with('error', 'Login with ' . ucfirst($provider) . ' is not supported.');
        }

        try {
            $driver = Socialite::driver(strtolower($provider));
            if (config('app.env') === 'local') {
                $driver->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));
            }
            $socialUser = $driver->user();
            Log::info("Socialite - handleProviderCallback: Fetched user from " . ucfirst($provider) . ".", [
                'id' => $socialUser->getId(),
                'email' => $socialUser->getEmail(),
                'name' => $socialUser->getName()
            ]);

            if (empty($socialUser->getEmail())) {
                Log::warning("Socialite - handleProviderCallback: Email not provided by " . ucfirst($provider) . ".", ['provider_user_id' => $socialUser->getId()]);
                return redirect()->route('register')->with('error', ucfirst($provider) . ' did not provide an email. Please register manually or try a different ' . ucfirst($provider) . ' account that shares its email address.');
            }

            $user = $this->findOrCreateSocialUser($socialUser, strtolower($provider)); 

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


    protected function findOrCreateSocialUser(\Laravel\Socialite\Contracts\User $socialProviderUser, string $providerName): ?User
    {
        
        $providerId = $socialProviderUser->getId();
        $email = $socialProviderUser->getEmail();
        $name = $socialProviderUser->getName();
        $avatar = $socialProviderUser->getAvatar();

        
        $user = User::where('provider_name', $providerName)
                    ->where('provider_id', $providerId)
                    ->first();

        if ($user) {
            Log::info("Socialite - findOrCreateSocialUser: User found by provider_id for [{$providerName}].", ['user_id' => $user->id]);
            $user->name = $name ?? $user->name;
            $user->provider_avatar = $avatar ?? $user->provider_avatar;
            if ($user->isDirty()) {
                $user->save();
                Log::info("Socialite - findOrCreateSocialUser: Updated existing user's name/avatar for [{$providerName}].", ['user_id' => $user->id]);
            }
            return $user;
        }

       
        Log::info("Socialite - findOrCreateSocialUser: User not found by provider_id for [{$providerName}]. Checking email: {$email}");
        $user = User::where('email', $email)->first();

        if ($user) {
            Log::info("Socialite - findOrCreateSocialUser: User found by email. Linking [{$providerName}] account.", ['user_id' => $user->id]);

            if ($user->provider_name && $user->provider_name !== $providerName) {
                Log::warning("Socialite - findOrCreateSocialUser: Email [{$email}] already linked to [{$user->provider_name}]. Cannot link to [{$providerName}].");
                
                throw new Exception("This email is already associated with an account using " . ucfirst($user->provider_name) . ". Please log in with that method or use a different email with " . ucfirst($providerName) . ".");
            }
             
            $user->provider_name = $providerName;
            $user->provider_id = $providerId;
            $user->provider_avatar = $avatar ?? $user->provider_avatar;
            $user->name = $name ?? $user->name;
            $user->email_verified_at = $user->email_verified_at ?? now();
            $user->save();
            Log::info("Socialite - findOrCreateSocialUser: Linked [{$providerName}] account to existing user by email.", ['user_id' => $user->id]);
            return $user;
        }

        Log::info("Socialite - findOrCreateSocialUser: Creating new user for email [{$email}] with provider [{$providerName}].");
        $userDataToCreate = [
            'name' => $name,
            'email' => $email,
            'email_verified_at' => now(),
            'password' => null, 
            'provider_name' => $providerName,
            'provider_id' => $providerId,
            'provider_avatar' => $avatar, 
            'has_set_password' => false,
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