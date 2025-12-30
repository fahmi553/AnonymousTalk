@extends('layouts.auth')

@section('content')
<div class="container-fluid">
    <div class="row min-vh-100">
        <div class="col-md-6 d-flex flex-column justify-content-center align-items-center bg-body-tertiary p-5 d-none d-md-flex">
            <i class="fas fa-user-shield fa-5x text-primary mb-4"></i>
            <h1 class="fw-bold text-body-emphasis">Anonymous Talk</h1>
            <p class="text-body-secondary fs-5" style="max-width: 400px; text-align: center;">
                Speak freely, discuss openly, and connect with others.
            </p>
        </div>

        <div class="col-md-6 d-flex flex-column justify-content-center align-items-center p-4 p-md-5 bg-body">
            <div class="w-100" style="max-width: 420px;">
                <div class="mb-4">
                    <h3 class="fw-bold text-body-emphasis">Login to your Account</h3>
                    <p class="text-body-secondary">Welcome back! Please enter your details.</p>
                </div>

                @if (session('error'))
                    <div class="alert alert-danger py-2" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger py-2" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <div>
                                @foreach ($errors->all() as $error)
                                    <div class="small">{{ $error }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label fw-medium">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-body-tertiary"><i class="fas fa-envelope"></i></span>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                class="form-control bg-body @error('email') is-invalid @enderror"
                                required
                                autofocus
                                value="{{ old('email') }}"
                                placeholder="you@example.com"
                            >
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-medium">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-body-tertiary"><i class="fas fa-lock"></i></span>
                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control bg-body @error('password') is-invalid @enderror"
                                required
                                placeholder="Enter your password"
                            >
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label small" for="remember">
                                Remember Me
                            </label>
                        </div>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-body-secondary text-decoration-none small">
                                Forgot password?
                            </a>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2 mb-3">Log In</button>
                </form>

                <div class="d-flex align-items-center mb-3">
                    <hr class="flex-grow-1 text-secondary">
                    <span class="px-3 text-secondary small">OR</span>
                    <hr class="flex-grow-1 text-secondary">
                </div>

                <a href="{{ route('login.google') }}" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center py-2">
                    <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google" style="width: 20px; height: 20px;" class="me-2">
                    <span class="fw-medium">Continue with Google</span>
                </a>

                <div class="mt-4 text-center">
                    <a href="{{ route('register') }}" class="text-body-secondary text-decoration-none small">Don't have an account? Register here</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
