<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Auth\Auth;
use App\Helpers\UsernameGenerator;

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

        $user = User::create([
            'username'    => UsernameGenerator::generate(),
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'trust_score' => 0,
            'role'        => 'user',
        ]);

        Auth::login($user);

        return redirect('/');
    }
}
