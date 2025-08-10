<header class="header d-flex justify-content-between align-items-center p-3">
    <div class="d-flex align-items-center">
        <h1 class="me-4 mb-0">Anonymous Talk</h1>
        <nav class="d-flex">
            <a href="{{ url('/') }}" class="nav-link text-white me-3">Home</a>
            @auth
                <a href="{{ route('posts.create') }}" class="nav-link text-white me-3">Create Post</a>
                <a href="{{ route('profile.edit') }}" class="nav-link text-white me-3">Profile</a>
            @endauth
        </nav>
    </div>

    <div class="d-flex align-items-center">
        @auth
            <span class="text-white me-3">Hi, {{ Auth::user()->username }}</span>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-light btn-sm">Logout</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="btn btn-light btn-sm me-2">Login</a>
            <a href="{{ route('register') }}" class="btn btn-outline-light btn-sm">Register</a>
        @endauth
    </div>
</header>
