@extends('layouts.app')

@section('content')
<div class="feed-container">

    <h3 class="mb-4 fw-bold text-primary">Latest Posts</h3>

    @foreach ($posts as $post)
        <div class="post-card">
            <div class="post-header">
                <span class="username">{{ $post->user->username ?? 'Anonymous' }}</span>
                <span class="time">· {{ $post->created_at->diffForHumans() }}</span>
            </div>
            <div class="post-content">{{ $post->content }}</div>
            <a href="{{ route('posts.show', $post->post_id) }}" class="text-decoration-none text-primary fw-semibold">
                View Post
            </a>
            @php
                $latestComments = $post->comments->take(2);
            @endphp

            @if($latestComments->isNotEmpty())
                <div class="comment-section">
                    @foreach ($latestComments as $comment)
                        <div class="comment">
                            <strong>{{ $comment->user->username ?? 'Anonymous' }}</strong>:
                            {{ $comment->content }}
                        </div>
                    @endforeach

                    @if ($post->comments->count() > 2)
                        <a href="{{ route('posts.show', $post->post_id) }}" class="small text-muted">
                            View all {{ $post->comments->count() }} comments
                        </a>
                    @endif
                </div>
            @endif
        </div>
    @endforeach

</div>
@endsection
