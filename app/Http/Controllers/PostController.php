<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;

class PostController extends Controller
{
    public function index()
    {
        return Post::with('user:id,username')->latest()->get();
    }


    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:500',
        ]);
        $ip = $request->ip();
        $user = User::firstOrCreate(
            ['ip_address' => $ip],
            ['username' => 'anon_' . Str::random(6)]
        );

        return $user->posts()->create([
            'content' => $request->content,
        ]);
    }
}
