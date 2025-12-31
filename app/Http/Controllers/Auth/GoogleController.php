<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use App\Helpers\UsernameGenerator;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                $newUsername = UsernameGenerator::generate();

                while(User::where('username', $newUsername)->exists()){
                    $newUsername = UsernameGenerator::generate();
                }

                $user = User::create([
                    'username' => $newUsername,
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => '/images/default-avatar.png',
                    'password' => Hash::make(Str::random(16)),
                    'trust_score' => 0,
                ]);
            } else {
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser->getId(),
                    ]);
                }
            }

            Auth::login($user);

            return redirect('/');

        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Google Login failed.');
        }
    }
}
