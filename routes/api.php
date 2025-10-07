<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\ProfileController;

// Public routes
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{postId}', [PostController::class, 'showApi']);
Route::get('/posts/{postId}/comments', [CommentController::class, 'index']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/profile/{id}', [ProfileController::class, 'show'])->name('profile.visit');

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // Posts
    Route::post('/posts', [PostController::class, 'store']);
    Route::patch('/posts/{post}/status', [PostController::class, 'updateStatus']);
    Route::post('/posts/{post}/toggle-like', [LikeController::class, 'toggleLike']);

    // Comments
    Route::post('/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);

    // Profile (self only)
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/regenerate-username', [ProfileController::class, 'regenerateUsername']);

    // Auth user info
    Route::get('/user', fn(Request $request) => $request->user());
});
