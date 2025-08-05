<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Anonymous Talk</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        <h1>Anonymous Talk</h1>

        @auth
            <p>Logged in as: {{ Auth::user()->username }}</p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Logout</button>
            </form>
        @endauth

        @yield('content')
    </div>
</body>
</html>
