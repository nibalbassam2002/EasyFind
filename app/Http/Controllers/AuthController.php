<?php

namespace App\Http\Controllers;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password; 

class AuthController extends Controller
{

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $redirectUrl = $request->input('redirect');
            if ($redirectUrl && Str::startsWith($redirectUrl, url('/'))) { 
                return redirect()->intended($redirectUrl); 
           }
            $user = Auth::user();


            switch ($user->role) {
                case 'admin':
                case 'content_moderator':
                case 'property_lister':
                case 'customer':
                    return $user->role === 'customer' ? redirect()->route('frontend.home') : redirect()->route('dashboard');
                default:

                    return redirect()->route('dashboard');
            }
        }


        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

         return redirect()->route('login'); 

    }


    public function showRegistrationForm()
    {

        return view('auth.register');
    }

    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone'], // اختياري وفريد
            'password' => [
                'required',
                'string',
                Password::min(8) 

                    ,
                'confirmed' 
             ],
            'terms' => ['accepted'], 
        ], [
         
            'terms.accepted' => 'You must agree to the Terms and Privacy Policies.',
            'email.unique' => 'This email address is already registered.',
            'phone.unique' => 'This phone number is already registered.',
        ]);


        if ($validator->fails()) {
            return redirect()->route('register')
                        ->withErrors($validator)
                        ->withInput(); 
        }


        try {
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'), 
                'password' => Hash::make($request->input('password')), 
                'role' => 'customer', 
                'status' => 'active', 
            ]);


            Auth::login($user);


            $request->session()->regenerate();


            return redirect()->route('dashboard')->with('success', 'Account created successfully! Welcome!');

        } catch (\Exception $e) {

            return redirect()->route('register')
                        ->with('error', 'An unexpected error occurred during registration. Please try again.')
                        ->withInput();
        }
    }
}