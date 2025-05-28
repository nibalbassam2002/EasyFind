<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    
    public function showLinkRequestForm()
    {
        
        return view('auth.passwords.email');
    }
    public function sendResetLinkEmail(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }

      
        $response = Password::broker()->sendResetLink(
            $request->only('email')
        );

        
        if ($response == Password::RESET_LINK_SENT) {
            return redirect()->back()->with('status', __($response)); 
        }

        return redirect()->back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => __($response)]);
    }

}
