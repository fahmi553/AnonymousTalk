<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        return Post::latest()->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:500',
        ]);

        return Post::create([
            'content' => $request->content,
        ]);
    }
}
