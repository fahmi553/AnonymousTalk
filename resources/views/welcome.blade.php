<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Anonymous Talk</title>
    <link rel="icon" href="{{ asset('images/AnonymousTalkLogo3.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- <link rel="stylesheet" href="{{ asset('css/style.css') }}"> --}}
</head>
<body class="d-flex flex-column min-vh-100 bg-body-tertiary">

    <div id="app-header" class="sticky-top"></div>

    <div id="app" class="d-flex flex-grow-1 container-fluid px-0">
        <div class="row w-100 mx-0 flex-grow-1">
            <app-sidebar></app-sidebar>

            <main class="col p-4 bg-body">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <strong>Something went wrong:</strong>
                        <ul class="mb-0 mt-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <verification-alert></verification-alert>
                <router-view></router-view>
            </main>
        </div>
    </div>

    <div id="app-footer"></div>

    <script>
        window.authUserId = @json(auth()->id());
        window.authUserName = @json(auth()->user()->username ?? null);
        window.authUserAvatar = @json(auth()->user()->avatar ?? null);
        window.flashMessage = @json(session('status') ?? session('success') ?? null);
        window.isEmailVerified = @json(auth()->check() && auth()->user()->hasVerifiedEmail());
    </script>
</body>
</html>
