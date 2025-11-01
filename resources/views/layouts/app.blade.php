<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Anonymous Talk</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if (request()->is('admin/*'))
        @vite(['resources/css/app.css', 'resources/js/admin.js'])
    @else
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
</head>
<body class="d-flex flex-column min-vh-100 bg-body-tertiary">
    @if (request()->is('admin/*'))
        <div id="admin-header" class="sticky-top"></div>
    @else
        <div id="vue-header" class="sticky-top"></div>
    @endif

    <div id="app" class="d-flex flex-grow-1">
        @unless (request()->is('login', 'register', 'password/*', 'admin/*'))
            @include('layouts.sidebar')
        @endunless

        <main class="flex-grow-1 p-4 bg-body">
            @foreach (['status' => 'info', 'success' => 'success', 'error' => 'danger'] as $msg => $type)
                @if (session($msg))
                    <div class="alert alert-{{ $type }}">{{ session($msg) }}</div>
                @endif
            @endforeach
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Something went wrong:</strong>
                    <ul class="mb-0 mt-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
    @if (request()->is('admin/*'))
        <div id="admin-footer"></div>
    @else
        @include('layouts.footer')
    @endif

    <script>
        window.authUserId = @json(auth()->id());
    </script>
</body>
</html>
