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
use App\Models\Post;
use App\Models\Comment;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminLoginController;

Route::get('/', fn() => view('welcome'));

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);
Route::get('/forgot-password', fn() => view('auth.forgot-password'))
    ->middleware('guest')
    ->name('password.request');

Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);
    $status = Password::sendResetLink($request->only('email'));

    return $status === Password::RESET_LINK_SENT
        ? back()->with('status', __($status))
        : back()->withErrors(['email' => __($status)]);
})->middleware('guest')->name('password.email');

Route::get('/reset-password/{token}', fn($token) => view('auth.reset-password', ['token' => $token]))
    ->middleware('guest')
    ->name('password.reset');

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
})->middleware('guest')->name('password.update');


Route::prefix('admin')->group(function () {

    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminLoginController::class, 'login'])->name('admin.login.submit');
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

    Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])
        ->get('/{any?}', function () {
            return view('welcome');
        })
        ->where('any', '.*')
        ->name('admin.dashboard');
});

Route::get('/simulate-full-badges', function () {
    // Step 1: Get or create a test user
    $user = User::first() ?? User::create([
        'username' => 'testuser',
        'email' => 'testuser@example.com',
        'password' => bcrypt('password'),
        'trust_score' => 0,
        'role' => 'user',
    ]);

    // Clean up old posts/comments for clean test
    $user->posts()->delete();
    $user->comments()->delete();

    // Step 2: Create fake posts
    $publishedPosts = 12;  // Enough to trigger 'Consistent Poster'
    $moderatedPosts = 2;   // To test 'Under Review' or 'Civil Contributor'
    for ($i = 1; $i <= $publishedPosts; $i++) {
        Post::create([
            'user_id' => $user->user_id,
            'category_id' => 1,
            'title' => "Test Post #$i",
            'content' => "This is the content of test post #$i.",
            'status' => 'published',
            'sentiment_score' => 1.0,
        ]);
    }
    for ($i = 1; $i <= $moderatedPosts; $i++) {
        Post::create([
            'user_id' => $user->user_id,
            'category_id' => 1,
            'title' => "Moderated Post #$i",
            'content' => "This post will be moderated.",
            'status' => 'moderated',
            'sentiment_score' => 0.0,
        ]);
    }

    // Step 3: Create fake comments
    $publishedComments = 25; // Enough to trigger 'Helpful Commenter'
    $safeReplies = 15;       // Enough to trigger 'Respectful Debater'
    $moderatedComments = 3;  // To test 'Under Review' badge

    for ($i = 1; $i <= $publishedComments; $i++) {
        Comment::create([
            'user_id' => $user->user_id,
            'post_id' => 1,
            'content' => "Comment #$i",
            'status' => 'published',
            'sentiment_score' => 1.0,
        ]);
    }

    for ($i = 1; $i <= $safeReplies; $i++) {
        Comment::create([
            'user_id' => $user->user_id,
            'post_id' => 1,
            'parent_id' => 1,  // Reply to first post comment
            'content' => "Reply #$i",
            'status' => 'published',
            'sentiment_score' => 1.0,
        ]);
    }

    for ($i = 1; $i <= $moderatedComments; $i++) {
        Comment::create([
            'user_id' => $user->user_id,
            'post_id' => 1,
            'content' => "Moderated Comment #$i",
            'status' => 'moderated',
            'sentiment_score' => 0.0,
        ]);
    }

    // Step 4: Apply trust points to simulate activity
    $user->applyTrustChange(User::TRUST_SCORE_POST_REWARD * $publishedPosts, 'Simulated posts');
    $user->applyTrustChange(User::TRUST_SCORE_COMMENT_REWARD * $publishedComments, 'Simulated comments');

    // Step 5: Refresh user and load badges
    $user->refresh()->load('badges');

    // Step 6: Show results
    $badges = $user->badges->pluck('badge_name')->toArray();
    return response()->json([
        'trust_score' => $user->trust_score,
        'badges' => $badges,
        'message' => 'Full badge simulation complete'
    ]);
});

Route::get('/badge-test/{id}', function($id) {
    $user = User::findOrFail($id);

    // Optional query param: ?change=5
    $change = request()->query('change', 0);
    $reason = request()->query('reason', 'Test simulation');

    if ($change != 0) {
        $user->applyTrustChange($change, $reason, 'test_simulation');
    } else {
        // Just recalc badges without changing trust
        $user->updateBadges();
    }

    return response()->json([
        'trust_score' => $user->trust_score,
        'badges'      => $user->badges()->pluck('badge_name'),
        'message'     => 'Badge test complete'
    ]);
});

Route::get('/badge-test-ui/{id}', function($id) {
    $user = User::findOrFail($id);

    return view('badge-test', ['user' => $user]);
});

Route::post('/badge-test-ui/{id}/change', function($id) {
    $user = User::findOrFail($id);
    $change = request('change', 0);
    $reason = request('reason', 'Test simulation');

    $user->applyTrustChange($change, $reason, 'test_simulation');

    return response()->json([
        'trust_score' => $user->trust_score,
        'badges'      => $user->badges()->pluck('badge_name'),
    ]);
});

Route::view('/profile', 'welcome');
Route::view('/profile/{id}', 'welcome');

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '^(?!api).*$');
