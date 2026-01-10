<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ModerationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\NotificationController;
use Illuminate\Auth\Events\Verified;
use App\Models\User;

// Public routes
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/trending', [PostController::class, 'getTrendingPosts']);
Route::get('/posts/{postId}', [PostController::class, 'showApi']);
Route::get('/posts/{postId}/comments', [CommentController::class, 'index']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/profile/{id}', [ProfileController::class, 'show'])
    ->name('profile.visit')
    ->where('id', '[0-9]+');

Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::findOrFail($id);

    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        return response()->json(['message' => 'Invalid or expired link.'], 403);
    }

    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new Verified($user));
    }

    return redirect(config('app.url') . '/profile?verified=true');

})->middleware(['signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return response()->json(['message' => 'Verification link sent!']);
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');


// Authenticated routes
Route::middleware('auth')->group(function () {

    Route::post('/posts', [PostController::class, 'store'])
        ->middleware('verified');

    Route::patch('/posts/{post}/status', [PostController::class, 'updateStatus']);
    Route::patch('/comments/{comment}/status', [CommentController::class, 'updateStatus']);
    Route::post('/posts/{postId}/toggle-like', [LikeController::class, 'toggleLike']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
    Route::post('/report', [ReportController::class, 'store']);

    Route::post('/comments', [CommentController::class, 'store'])
        ->middleware('verified');

    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);
    Route::post('/comments/{id}/report', [CommentController::class, 'report']);
    Route::get('/comments/{id}/reports', [CommentController::class, 'showReports'])->middleware('admin');

    // Self profile
    Route::get('/profile/avatars', [ProfileController::class, 'getAvatars']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::delete('/profile', [ProfileController::class, 'destroy']);
    Route::post('/profile/regenerate-username', [ProfileController::class, 'regenerateUsername']);
    Route::patch('/posts/{id}/toggle-profile-visibility', [ProfileController::class, 'togglePostVisibility']);
    Route::post('/profile/toggle-hide-all-posts', [ProfileController::class, 'toggleHideAllPosts']);
    Route::post('/report/user/{user}', [ReportController::class, 'reportUser']);

    // Auth user info
    Route::get('/user', fn(Request $request) => $request->user());

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/mark-read', [NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    Route::delete('/admin/reports/{id}', [AdminController::class, 'deleteReport']);
    Route::get('/admin/report-details/{postId}', [AdminController::class, 'showReportDetails']);
    Route::get('/admin/user-report-details/{id}', [AdminController::class, 'getUserReportDetails']);
    Route::post('/moderate/user/{id}/{action}', [AdminController::class, 'moderateUser']);
    Route::get('/admin/users', [AdminController::class, 'getUsers']);
    Route::get('/admin/logs', [AdminController::class, 'getSystemLogs']);
    Route::post('/admin/user/{id}/adjust-score', [AdminController::class, 'adjustTrustScore']);
    Route::get('/admin/flagged-posts', [AdminController::class, 'getFlaggedPosts']);
    Route::post('/moderate/{type}/{id}/{action}', [ModerationController::class, 'moderateContent']);
    Route::get('/admin/content', [AdminController::class, 'getContent']);
    Route::post('/moderate/{type}/{id}/{action}', [AdminController::class, 'moderateContent']);
});
