<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckBanned
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->banned_at !== null) {

            $reason = Auth::user()->ban_reason;

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Your account has been suspended. Reason: {$reason}",
                    'banned' => true
                ], 403);
            }

            return redirect('/login')->withErrors([
                'email' => "Your account has been suspended. Reason: {$reason}",
            ]);
        }

        return $next($request);
    }
}
