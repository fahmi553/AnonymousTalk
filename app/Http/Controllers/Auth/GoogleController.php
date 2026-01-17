<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use App\Helpers\UsernameGenerator;
use Illuminate\Auth\Events\Verified;

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
                    'avatar' => 'default.jpg',
                    'password' => Hash::make(Str::random(16)),
                    'trust_score' => 0,
                    'email_verified_at' => now(),
                ]);

                event(new Verified($user));

            } else {
                $updates = [];
                $wasUnverified = false;

                if (!$user->google_id) {
                    $updates['google_id'] = $googleUser->getId();
                }

                if (!$user->email_verified_at) {
                    $updates['email_verified_at'] = now();
                    $wasUnverified = true;
                }

                if (!empty($updates)) {
                    $user->update($updates);
                    if ($wasUnverified) {
                        event(new Verified($user));
                    }
                }
            }

            if ($user->banned_at) {
                return redirect('/login')->with('error', 'Your account has been banned: ' . ($user->ban_reason ?? 'Violation of terms.'));
            }

            Auth::login($user);

            return redirect('/');

        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Google Login failed.');
        }
    }
}
