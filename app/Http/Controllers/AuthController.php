<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\ForgotPassword;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller {
    public function register( Request $request ) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', 'min:8', 'regex:/[A-Za-z]/', 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/'],
        ]);        

        if ( $validator->fails() ) {
            return response()->json( $validator->errors(), 422 );
        }

        $user = User::create( [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make( $request->password ),
        ] );

        // Send verification email
        event( new Registered( $user ) );

        return response()->json( [
            'message' => 'Registration successful, please verify your email.'
        ], 201 );
    }

    public function resendVerificationEmail( Request $request ) {
        if ( $request->user()->hasVerifiedEmail() ) {
            return response()->json( [ 'message' => 'Email already verified.' ], 200 );
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json( [ 'message' => 'Verification link sent.' ], 200 );
    }

    public function verifyEmail( Request $request, $id, $hash ) {
        $user = User::findOrFail( $id );

        if ( ! hash_equals( ( string ) $hash, sha1( $user->getEmailForVerification() ) ) ) {
            return redirect("https://www.samarmart.com/login?emailverified=Invalid_verification_link");
        }

        if ( $user->hasVerifiedEmail() ) {
            return redirect("https://www.samarmart.com/login?emailverified=Email_already_verfied");
        }

        $user->markEmailAsVerified();

        return redirect("https://www.samarmart.com/login?emailverified=Email_Verified");
    }

    public function login(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        // Apply rate limiting for login attempts
        if (RateLimiter::tooManyAttempts('login:'.$request->ip(), 5)) {
            return response()->json(['error' => 'Too many login attempts. Please try again later.'], 429);
        }
    
        $credentials = $request->only('email', 'password');
    
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
    
            // Check if email is verified
            if (!$user->hasVerifiedEmail()) {
                return response()->json(['error' => 'Email not verified.'], 403);
            }
    
            // Generate token
            $token = $user->createToken('auth_token')->plainTextToken;
    
            // Clear failed attempts on successful login
            RateLimiter::clear('login:'.$request->ip());
    
            return response()->json(['token' => $token, 'user' => $user], 200);
        }
    
        // Increment failed attempts if login fails
        RateLimiter::hit('login:'.$request->ip());
    
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    
    

    public function sendResetLinkEmail( Request $request ) {
        // Validate email
        $validator = Validator::make( $request->all(), [
            'email' => 'required|email|exists:users,email',
        ] );

        if (RateLimiter::tooManyAttempts('password-reset:'.$request->ip(), 5)) {
            return response()->json(['error' => 'Too many password reset attempts. Please try again later.'], 429);
        }        

        if ( $validator->fails() ) {
            return back()->withErrors( $validator )->withInput();
        }

        // Find the user by email
        $user = User::where( 'email', $request->email )->first();

        // Create a random token for resetting password
        $token = Str::random( 60 );

        // Save token in password_resets table
        \DB::table( 'users' )->where( 'email', $request->email )->update(
            [
                'email_enc' => Hash::make( $user->email ),
                'token_fp' => Hash::make( $token ),
                'fp_at' => now()
            ]
        );

        // Send reset password email
        Mail::to( $user->email )->send( new ForgotPassword( $user, Crypt::encryptString( $token ), Crypt::encryptString( $user->email ) ) );

        return response()->json( 'Email Has Been Sent', 200 );
    }

    public function rp_validateCreds(Request $request) {
        $email = Crypt::decryptString($request->email);
        $token = Crypt::decryptString($request->token);
    
        $user = DB::table('users')->where('email', $email)->first();
    
        if ($user) {
            // Check if the token has expired (example: 60 minutes validity)
            $tokenExpiryTime = now()->subMinutes(60);
            if ($user->fp_at < $tokenExpiryTime) {
                return response()->json('Token has expired', 400);
            }
    
            // Check if the token and email match
            if (Hash::check($token, $user->token_fp) && Hash::check($email, $user->email_enc)) {
                return response()->json($request->email, 200);
            } else {
                return response()->json('Invalid credentials', 400);
            }
        }
        return response()->json('Invalid credentials', 400);
    }
    

    public function set_newpass(Request $request) {
        $email = Crypt::decryptString($request->token);
        $password = $request->password;
    
        $user = DB::table('users')->where('email', $email)->first();
        if (Hash::check($email, $user->email_enc)) {
            DB::table('users')->where('email', $email)->update([
                'password' => Hash::make($request->password),
                'email_enc'=> Str::random(60),
                'token_fp'=> Hash::make(Str::random(60)),
                'fp_at'=> NULL,
            ]);
    
            // Revoke all tokens after password reset
            DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->delete();
    
            return response()->json('Password Changed', 200);
        } else {
            return response()->json('Error', 406);
        }
    }    
}