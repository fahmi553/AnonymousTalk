<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('login.google');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::post('/register', [RegisterController::class, 'register']);

Route::post('/forgot-password', [LoginController::class, 'sendResetLink'])->middleware('guest')->name('password.email');
Route::post('/reset-password', [LoginController::class, 'resetPassword'])->middleware('guest')->name('password.update');

Route::prefix('admin')->group(function () {
    Route::post('/login', [App\Http\Controllers\Admin\AdminLoginController::class, 'login'])->name('admin.login.submit');
    Route::post('/logout', [App\Http\Controllers\Admin\AdminLoginController::class, 'logout'])->name('admin.logout');
});

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
