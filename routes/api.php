<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ModerationController;
use App\Http\Controllers\ReportController;

// Public routes
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/trending', [PostController::class, 'getTrendingPosts']);
Route::get('/posts/{postId}', [PostController::class, 'showApi']);
Route::get('/posts/{postId}/comments', [CommentController::class, 'index']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/profile/{id}', [ProfileController::class, 'show'])->name('profile.visit');
// Route::get('/profile/{id}/posts', [ProfileController::class, 'userPosts']);
// Route::get('/profile/{id}/comments', [ProfileController::class, 'userComments']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // Posts
    Route::post('/posts', [PostController::class, 'store']);
    Route::patch('/posts/{post}/status', [PostController::class, 'updateStatus']);
    Route::post('/posts/{postId}/toggle-like', [LikeController::class, 'toggleLike']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
    Route::post('/report', [ReportController::class, 'store']);

    // Comments
    Route::post('/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);
    Route::post('/comments/{id}/report', [CommentController::class, 'report']);
    Route::get('/comments/{id}/reports', [CommentController::class, 'showReports'])->middleware('admin');

    // Self profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/regenerate-username', [ProfileController::class, 'regenerateUsername']);
    Route::patch('/posts/{id}/toggle-profile-visibility', [ProfileController::class, 'togglePostVisibility']);
    Route::post('/profile/toggle-hide-all-posts', [ProfileController::class, 'toggleHideAllPosts']);

    Route::post('/report/user/{user}', [ReportController::class, 'reportUser']);

    // Post moderation
    Route::post('/moderate/post/{id}/approve', [ModerationController::class, 'approvePost']);
    Route::post('/moderate/post/{id}/hide', [ModerationController::class, 'hidePost']);
    Route::post('/moderate/post/{id}/delete', [ModerationController::class, 'deletePost']);

    // Comment moderation
    Route::post('/moderate/comment/{id}/approve', [ModerationController::class, 'approveComment']);
    Route::post('/moderate/comment/{id}/hide', [ModerationController::class, 'hideComment']);
    Route::post('/moderate/comment/{id}/delete', [ModerationController::class, 'deleteComment']);

    // Auth user info
    Route::get('/user', fn(Request $request) => $request->user());
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::middleware('admin')->group(function () {
        Route::get('/admin/posts', [AdminController::class, 'allPosts']);
        Route::get('/admin/comments', [AdminController::class, 'allComments']);
        Route::patch('/admin/posts/{id}/toggle', [AdminController::class, 'togglePostStatus']);
        Route::patch('/admin/comments/{id}/toggle', [AdminController::class, 'toggleCommentStatus']);
        Route::delete('/admin/posts/{id}', [AdminController::class, 'deletePost']);
        Route::delete('/admin/comments/{id}', [AdminController::class, 'deleteComment']);
    });
});
