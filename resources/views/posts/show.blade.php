@extends('layouts.app')

@section('content')
<div id="app" class="container py-4" style="max-width: 700px;">
    <post-detail :post-id="{{ $post->post_id }}" :auth-user-id="{{ Auth::id() ?? 'null' }}"></post-detail>
</div>
@endsection
