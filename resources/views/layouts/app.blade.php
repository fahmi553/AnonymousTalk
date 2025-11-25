<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Anonymous Talk</title>
    <link rel="icon" href="{{ asset('images/AnonymousTalkLogo3.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
</head>
<body class="d-flex flex-column min-vh-100 bg-body-tertiary">
    
    <div id="app-header" class="sticky-top"></div>

    <div id="app" class="d-flex flex-grow-1 container-fluid px-0">
        <div class="row w-100 mx-0 flex-grow-1">
            <app-sidebar></app-sidebar>

            <main class="col p-4 bg-body">
                
                @foreach (['status' => 'info', 'success' => 'success', 'error' => 'danger'] as $msg => $type)
                    @if (session($msg))
                        <div class="alert alert-{{ $type }} alert-dismissible fade show" role="alert">
                            {{ session($msg) }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
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

                <router-view></router-view>
                
                {{-- @yield('content')  --}}
            </main>
        </div>
    </div>
    <div id="app-footer"></div>
    <script>
        window.authUserId = @json(auth()->id());
        window.authUserName = @json(auth()->user()->username ?? null);
    </script>
</body>
</html>