@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 700px;">

    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center mb-2">
                <div class="fw-bold text-dark">{{ $post->user->username ?? 'Anonymous' }}</div>
                <small class="text-muted ms-2">{{ $post->created_at->diffForHumans() }}</small>
            </div>
            <p class="mb-3">{{ $post->content }}</p>
        </div>
    </div>

    <h5 class="fw-bold mb-3">Comments</h5>

    @foreach ($post->comments as $comment)
        <div class="border-bottom py-2">
            <strong>{{ $comment->user->username ?? 'Anonymous' }}</strong>:
            {{ $comment->content }}
        </div>
    @endforeach

    @auth
        <form method="POST" action="/comments" class="mt-3">
            @csrf
            <input type="hidden" name="post_id" value="{{ $post->post_id }}">
            <input type="hidden" name="user_id" value="{{ Auth::id() }}">
            <div class="input-group">
                <input type="text" name="content" class="form-control" placeholder="Write a comment...">
                <button type="submit" class="btn btn-primary">Comment</button>
            </div>
        </form>
    @endauth

</div>
@endsection
