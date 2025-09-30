<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Anonymous Talk</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
</head>
<body class="d-flex flex-column min-vh-100">

    <div id="vue-header"></div>

    <main class="flex-grow-1 d-flex align-items-center justify-content-center p-4">
        @foreach (['status' => 'info', 'success' => 'success', 'error' => 'danger'] as $msg => $type)
            @if (session($msg))
                <div class="alert alert-{{ $type }}">{{ session($msg) }}</div>
            @endif
        @endforeach
        @if ($errors->any())
            <div class="alert alert-danger w-100" style="max-width: 500px;">
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

    @include('layouts.footer')
</body>
</html>
