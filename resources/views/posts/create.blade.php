@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 600px;">
    <h2 class="mb-4">Create New Post</h2>

    <form action="{{ route('posts.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="content" class="form-label">Post Content</label>
            <textarea name="content" id="content" class="form-control" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Post</button>
    </form>
</div>
@endsection
