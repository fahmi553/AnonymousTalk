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
Route::get('/profile/{id}/posts', [ProfileController::class, 'userPosts']);
Route::get('/profile/{id}/comments', [ProfileController::class, 'userComments']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // Posts
    Route::post('/posts', [PostController::class, 'store']);
    Route::patch('/posts/{post}/status', [PostController::class, 'updateStatus']);
    Route::post('/posts/{postId}/toggle-like', [LikeController::class, 'toggleLike']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);

    // Comments
    Route::post('/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);

    // Profile (self only)
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/regenerate-username', [ProfileController::class, 'regenerateUsername']);
    Route::patch('/posts/{id}/toggle-profile-visibility', [ProfileController::class, 'togglePostVisibility']);
    Route::patch('/comments/{id}/toggle-profile-visibility', [ProfileController::class, 'toggleCommentVisibility']);
    Route::post('/profile/toggle-hide-posts', [ProfileController::class, 'toggleHideAllPosts']);
    Route::post('/profile/toggle-hide-comments', [ProfileController::class, 'toggleHideAllComments']);

    // Auth user info
    Route::get('/user', fn(Request $request) => $request->user());
});
