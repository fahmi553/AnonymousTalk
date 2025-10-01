<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\ProfileController;

Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{postId}', [PostController::class, 'showApi']);
Route::get('/posts/{postId}/comments', [CommentController::class, 'index']);
Route::get('/categories', [CategoryController::class, 'index']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/posts', [PostController::class, 'store']);
    Route::patch('/posts/{post}/status', [PostController::class, 'updateStatus']);
    Route::post('/posts/{post}/toggle-like', [LikeController::class, 'toggleLike']);
    Route::post('/comments', [CommentController::class, 'store']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/regenerate-username', [ProfileController::class, 'regenerateUsername']);
    Route::get('/user', fn(Request $request) => $request->user());
    Route::delete('/comments/{id}', [App\Http\Controllers\CommentController::class, 'destroy']);
});
