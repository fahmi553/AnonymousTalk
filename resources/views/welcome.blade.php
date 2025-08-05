<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Anonymous Talk</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        <h1>Welcome to Anonymous Talk</h1>

        @auth
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Logout</button>
            </form>

            <p>Logged in as: {{ Auth::user()->username }}</p>
            <post-form></post-form>
            <post-feed></post-feed>
            <comment-list :post-id="1"></comment-list>
            <comment-form :post-id="1" :user-id="{{ Auth::id() }}"></comment-form>
        @else
            <p>Please <a href="{{ route('login') }}">Login</a> or <a href="{{ route('register') }}">Register</a>.</p>
        @endauth
    </div>
</body>
</html>
