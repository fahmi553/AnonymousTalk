<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;

Route::get('/posts', [PostController::class, 'index']);
Route::post('/posts', [PostController::class, 'store']);

Route::get('/posts/{postId}/comments', [CommentController::class, 'index']);
Route::post('/comments', [CommentController::class, 'store']);
Route::patch('/posts/{post}/status', [PostController::class, 'updateStatus']);
