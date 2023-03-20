<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FAQRCode\Google2FA;

class OTPController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show(Request $request)
    {
        if(auth()->user()->twofa_type != 'Code2fa'){
            // avoid double OTP check
            session(["2fa_checked" => true]);
            return redirect("/");
        }else if (isset($request->regenerateGoogle2FA) && $request->regenerateGoogle2FA==1) {
            $google2fa = new Google2FA();
            // generate a secret
            $secret = $google2fa->generateSecretKey();

            $user = Auth::user();
            $user->twofa_secret = $secret;
            $user->save();
            //Session::flash('success', 'Google 2FA has been disabled for your account.');
            //session(["2fa_checked" => false]);
            $qr_code = $google2fa->getQRCodeInline(
                env('APP_NAME', 'Laravel'),
                $user->email,
                $secret
            );

            // store the current secret in the session
            // will be used when we enable 2FA (see below)
            session(["2fa_secret" => $secret]);
            session(["2fa_checked" => false]);

            return view('profile.2fa', compact('qr_code'));
        } else {
            $google2fa = new Google2FA();
            // generate the QR code, indicating the address 
            // of the web application and the user name
            // or email in this case
            $qr_code = $google2fa->getQRCodeInline(
                env('APP_NAME', 'Laravel'),
                Auth::user()->email,
                Auth::user()->twofa_secret
            );

            return view('auth.otp',compact('qr_code'));
        }
    }

    public function check(Request $request)
    {
        $google2fa = new Google2FA();
        $secret = Auth::user()->twofa_secret;
        if ($google2fa->verify($request->input('otp'), $secret)) {
            session(["2fa_checked" => true]);
            return redirect("/");
        }

        throw ValidationException::withMessages([
            'otp' => 'Incorrect value. Please try again...'
        ]);
    }

    
}