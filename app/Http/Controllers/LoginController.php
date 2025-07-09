<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $this->oauthRedirect('google');
            return redirect()->intended(route('home'));
        } catch (\Exception $e) {
            info('Google OAuth callback error: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Authentication failed. Please try again.');
        }
    }

    protected function oauthRedirect($driver)
    {
        // get oauth request back from provider ($driver) to authenticate user
        try {
            $providerUser = Socialite::driver($driver)->user();
            // if this user doesn't exist, add them
            // if they do, get the model from provider
            // either way, authenticate the user into the app and redirect afterwards
            $user = User::updateOrCreate([
                'email' => $providerUser->getEmail(),
            ], [
                'name' => $providerUser->getName(),
                'password' => Hash::make(Str::random(8)),
            ]);

            $user->save();
    
            Auth::login($user, true);

        } catch(InvalidStateException $e) {
            info('Login attempt error: ' . '"InvalidStateException" in Vintage Console\'s OAuth2 LoginController');
            info($e->getMessage());
            throw $e; // Re-throw to be handled by calling method
        } catch(\Exception $e) {
            info('OAuth error: ' . $e->getMessage());
            throw $e; // Re-throw to be handled by calling method
        }
    }
}
