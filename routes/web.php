<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use App\Models\User;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Auth\GoogleController;

Route::get('/', fn () => view('welcome'));

Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])
    ->name('login.google');

Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::middleware('guest')->group(function () {

    Route::get('/login', [LoginController::class, 'showLoginForm'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/register', [RegisterController::class, 'showRegisterForm'])
        ->name('register');

    Route::post('/register', [RegisterController::class, 'register']);

    Route::get('/forgot-password', fn () => view('auth.forgot-password'))
        ->name('password.request');

    Route::post('/forgot-password', function (Request $request) {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    })->name('password.email');

    Route::get('/reset-password/{token}', fn ($token) =>
        view('auth.reset-password', ['token' => $token])
    )->name('password.reset');

    Route::post('/reset-password', function (Request $request) {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    })->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::prefix('admin')->group(function () {

    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])
        ->name('admin.login');

    Route::post('/login', [AdminLoginController::class, 'login'])
        ->name('admin.login.submit');

    Route::post('/logout', [AdminLoginController::class, 'logout'])
        ->name('admin.logout');

    Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])
        ->get('/{any?}', fn () => view('welcome'))
        ->where('any', '.*')
        ->name('admin.dashboard');
});

Route::view('/profile', 'welcome');
Route::view('/profile/{id}', 'welcome');

use Illuminate\Support\Facades\Artisan;

Route::get('/run-migrate', function () {
    Artisan::call('migrate', ["--force" => true]);
    return "Migrations successful! Database tables are now created.";
});

Route::get('/{any}', fn () => view('welcome'))
    ->where('any', '^(?!api|login|register|forgot-password|reset-password|admin).*$');
