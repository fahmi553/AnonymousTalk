<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Helpers\UsernameGenerator;
use Illuminate\Auth\Events\Registered;

class RegisterController extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $newUsername = UsernameGenerator::generate();
        while(User::where('username', $newUsername)->exists()){
            $newUsername = UsernameGenerator::generate();
        }

        $user = User::create([
            'username'    => $newUsername,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'trust_score' => 0,
            'role'        => 'user',
        ]);

        Auth::login($user);
        return redirect('/')->with('success', 'Registration successful! Please check your email.');
    }
}
