<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str; 
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\Rules;

class ResetPasswordController extends Controller
{
    
 
    protected $redirectTo; 
    public function __construct()
    {
        $this->redirectTo = route('frontend.home'); 
    }

    public function showResetForm(Request $request)
    {
        $token = $request->route()->parameter('token'); 

       
        return view('auth.passwords.reset')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }


    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $response = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );

        if ($response == Password::PASSWORD_RESET) {
            return redirect($this->redirectTo)->with('status', __($response));
        }
        return redirect()->back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => __($response)]); 
    }

    protected function resetPassword($user, $password)
    {
      
        $user->forceFill([ 
            'password' => Hash::make($password),
            'remember_token' => Str::random(60), 
        ])->save();

        event(new PasswordReset($user));

    }

}
