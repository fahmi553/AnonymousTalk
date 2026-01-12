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
</head>
<body class="d-flex flex-column min-vh-100 bg-body-tertiary">
    <div id="app-header" class="sticky-top"></div>
    @yield('content')
    -->
    <div id="app-footer"></div>
</body>
</html>
