<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminLoginController extends Controller
{
    public function showLoginForm()
    {
        // --- THIS IS THE FIX ---
        // Check if the user is already logged in and is an admin
        if (Auth::check() && Auth::user()->role === 'admin') {
            // If they are, redirect them to the admin dashboard.
            return redirect('/admin/dashboard'); // Or '/admin' if that's your route
        }
        // --- END OF FIX ---

        // If not logged in, show the login page
        return view('welcome');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password) && $user->role === 'admin') {
            Auth::login($user);

            // Make sure to regenerate the session
            $request->session()->regenerate();

            return response()->json([
                'success' => true,
                'message' => 'Admin login successful.',
            ]);
        }

        return response()->json([
            'email' => 'Invalid admin credentials.',
        ], 401);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
