<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use App\Helpers\UsernameGenerator;

class ProfileController extends Controller
{
    public function show(Request $request, $id = null)
    {
        if ($id === null) {
            if (!$request->user()) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }
            $user = $request->user();
        } else {
            $user = \App\Models\User::findOrFail($id);
        }

        return response()->json([
            'username'               => $user->username,
            'email'                  => $user->email,
            'trust_score'            => $user->trust_score,
            'role'                   => $user->role,
            'created_at'             => $user->created_at->toDateTimeString(),
            'auto_rotate_username'   => $user->auto_rotate_username ?? null,
            'rotation_interval_days' => $user->rotation_interval_days ?? null,
            'last_username_change'   => $user->last_username_change ?? null,
        ]);
    }

    public function edit()
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'username' => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email,' . $user->user_id . ',user_id',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->username = $request->username;
        $user->email    = $request->email;

        if ($request->filled('password')) {
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        if ($user->isDirty('username')) {
            $user->last_username_change = now();
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully!',
            'user'    => $user,
        ]);
    }
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function regenerateUsername(Request $request)
    {
        $user = $request->user();

        $user->username = UsernameGenerator::generate();
        $user->save();

        return response()->json([
            'message'  => 'Username regenerated successfully',
            'username' => $user->username
        ]);
    }
}
