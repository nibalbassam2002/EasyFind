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

class SocialiteController extends Controller
{
    private $supportedProviders = ['google'];

    public function redirectToProvider(string $provider)
    {
        Log::info("Socialite: Attempting redirect to provider [{$provider}].");
        if (!in_array(strtolower($provider), $this->supportedProviders)) {
            Log::warning("Socialite: Unsupported provider [{$provider}] requested for redirect.");
            return redirect()->route('login')->with('error', ucfirst($provider) . ' is not a supported login provider.');
        }
        try {
            return Socialite::driver($provider)->redirect();
        } catch (Exception $e) {
            Log::error("Socialite: Exception during redirect to provider [{$provider}].", ['exception_message' => $e->getMessage()]);
            return redirect()->route('login')->with('error', "An error occurred with " . ucfirst($provider) . ".");
        }
    }

    public function handleProviderCallback(string $provider)
    {
        Log::info("Socialite: Starting callback for provider [{$provider}].");
        if (!in_array(strtolower($provider), $this->supportedProviders)) {
            Log::warning("Socialite: Unsupported provider [{$provider}] in callback.");
            return redirect()->route('login')->with('error', ucfirst($provider) . ' is not a supported login provider.');
        }

        try {
            $socialUser = Socialite::driver($provider)->user(); // تم إرجاع $socialUser هنا
            Log::info("Socialite: Fetched user from [{$provider}].", (array) $socialUser); // استخدام $socialUser هنا
        } catch (InvalidStateException $e) {
            Log::error("Socialite: InvalidStateException for [{$provider}] callback.", ['exception' => $e->getMessage()]);
            return redirect()->route('login')->with('error', 'Invalid authentication state. Please try again.');
        } catch (Exception $e) {
            Log::error("Socialite: Generic callback error for [{$provider}].", ['exception' => $e->getMessage()]);
            return redirect()->route('login')->with('error', "Failed to get user info from " . ucfirst($provider) . ". Please try again or register manually.");
        }

        if (empty($socialUser->getEmail())) { // استخدام $socialUser هنا
            Log::warning("Socialite: Email not provided by [{$provider}] for ID: " . $socialUser->getId()); // استخدام $socialUser هنا
            return redirect()->route('register')->with('error', "Email not provided by " . ucfirst($provider) . ". Please use manual registration or ensure your " . ucfirst($provider) . " account shares email.");
        }

        try {
            $user = $this->findOrCreateUser($socialUser, $provider); // تمرير $socialUser هنا

            if (!$user) {
                Log::critical("Socialite: CRITICAL - findOrCreateUser returned null for provider [{$provider}] and email [{$socialUser->getEmail()}]. This indicates a logic flaw.");
                return redirect()->route('register')->with('error', 'A critical error occurred. Please contact support or register manually.');
            }

            Auth::login($user, true);
            Log::info("Socialite: User [ID: {$user->id}] logged in via [{$provider}].");

            return redirect()->intended($this->redirectTo($user));

        } catch (QueryException $e) {
            $errorCode = $e->errorInfo[1] ?? null;
            $errorMessage = $e->errorInfo[2] ?? $e->getMessage();
            Log::error("Socialite: QueryException during user find/create for [{$provider}].", [
                'email' => $socialUser->getEmail(), // استخدام $socialUser هنا
                'error_code' => $errorCode,
                'db_error_message' => $errorMessage,
                'full_exception_message' => $e->getMessage(),
            ]);
            if ($errorCode == 1062) {
                return redirect()->route('login')->with('error', 'This email or social account is already associated with an existing user. Please log in.');
            }
            return redirect()->route('register')->with('error', 'A database error occurred while setting up your account. Please try again or register manually.');
        } catch (Exception $e) {
            Log::error("Socialite: Generic Exception during user find/create for [{$provider}].", ['email' => $socialUser->getEmail(), 'exception' => $e->getMessage()]); // استخدام $socialUser هنا
            return redirect()->route('register')->with('error', 'An unexpected error occurred. Please try again.');
        }
    }


    protected function findOrCreateUser(\Laravel\Socialite\Contracts\User $socialUserData, string $provider): ?User 
    {
        $providerId = $socialUserData->getId();
        $providerEmail = $socialUserData->getEmail();
        $providerNameValue = $socialUserData->getName(); 
        $providerAvatarValue = $socialUserData->getAvatar(); 

        $user = User::where('provider_name', $provider)
                    ->where('provider_id', $providerId)
                    ->first();

        if ($user) {
            Log::info("Socialite Inner: Found existing user by provider.", ['user_id' => $user->id]);
            $user->name = $providerNameValue ?? $user->name;
            $user->provider_avatar = $providerAvatarValue ?? $user->provider_avatar;
            $user->save();
            return $user;
        }

        $user = User::where('email', $providerEmail)->first();

        if ($user) {
            Log::info("Socialite Inner: Found existing user by email [{$providerEmail}]. Linking social account.", ['user_id' => $user->id]);
            if ($user->provider_name && $user->provider_name !== $provider) {
                Log::warning("Socialite Inner: Email [{$providerEmail}] already linked to [{$user->provider_name}]. Cannot link to [{$provider}].");
                throw new Exception("This email is already linked to " . ucfirst($user->provider_name) . ". Please use that login method or a different email for " . ucfirst($provider) . ".");
            }
            if ($user->provider_name === $provider && $user->provider_id !== $providerId) {
                Log::warning("Socialite Inner: Email [{$providerEmail}] linked to [{$provider}] with different ID. Current: {$user->provider_id}, New: {$providerId}. This is unusual.");
                 throw new Exception("There's an issue with your " . ucfirst($provider) . " account linkage. Please contact support.");
            }

            $user->provider_name = $provider;
            $user->provider_id = $providerId;
            $user->provider_avatar = $providerAvatarValue ?? $user->provider_avatar;
            $user->name = $providerNameValue ?? $user->name;
            $user->email_verified_at = $user->email_verified_at ?? now();
            $user->save();
            return $user;
        }


        Log::info("Socialite Inner: Creating new user for email [{$providerEmail}] with provider [{$provider}].");
        $userDataToCreate = [
            'name' => $providerNameValue,
            'email' => $providerEmail,
            'email_verified_at' => now(),
            'password' => null,
            'provider_name' => $provider,
            'provider_id' => $providerId,
            'provider_avatar' => $providerAvatarValue,
            'role' => 'customer',
            'status' => 'active',

        ];
        Log::info("Socialite Inner: Data for new user creation:", $userDataToCreate);
        $createdUser = User::create($userDataToCreate);
        Log::info("Socialite Inner: New user created successfully with ID: " . $createdUser->id);
        return $createdUser;
    }

    protected function redirectTo(User $user)
    {
        $intendedUrl = session()->pull('url.intended');
        $defaultRedirect = ($user->role === 'customer') ? route('frontend.home') : route('dashboard');

        if ($intendedUrl) {
            $path = parse_url($intendedUrl, PHP_URL_PATH);
            $loginPath = parse_url(route('login', [], false), PHP_URL_PATH);
            $registerPath = parse_url(route('register', [], false), PHP_URL_PATH);

            if ($path && !in_array($path, [$loginPath, $registerPath])) {
                Log::info("Socialite Redirect: To intended URL: {$intendedUrl}");
                return $intendedUrl;
            }
            Log::info("Socialite Redirect: Intended URL was login/register ({$intendedUrl}), redirecting to default: {$defaultRedirect}");
            return $defaultRedirect;
        }

        Log::info("Socialite Redirect: No intended URL, redirecting to default: {$defaultRedirect}");
        return $defaultRedirect;
    }
}