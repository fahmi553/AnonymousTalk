@extends('layouts.auth')

@section('content')
<div class="container-fluid">
    <div class="row min-vh-100">
        <div class="col-md-6 d-flex flex-column justify-content-center align-items-center bg-body-tertiary p-5 d-none d-md-flex">
            <i class="fas fa-user-shield fa-5x text-primary mb-4"></i>
            <h1 class="fw-bold text-body-emphasis">Join Anonymous Talk</h1>
            <p class="text-body-secondary fs-5" style="max-width: 400px; text-align: center;">
                Speak freely, discuss openly, and connect with others.
            </p>
        </div>
        <div class="col-md-6 d-flex flex-column justify-content-center align-items-center p-4 p-md-5 bg-body">
            <div class="w-100" style="max-width: 420px;">
                <div class="mb-4">
                    <h3 class="fw-bold text-body-emphasis">Create your Account</h3>
                    <p class="text-body-secondary">Get started by filling out the form below.</p>
                </div>
                @if ($errors->any())
                    <div class="alert alert-danger py-2" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <ul class="mb-0 small">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('register') }}">
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
                                placeholder="Create a strong password"
                            >
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label fw-medium">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-body-tertiary"><i class="fas fa-check-circle"></i></span>
                            <input
                                type="password"
                                name="password_confirmation"
                                id="password_confirmation"
                                class="form-control bg-body"
                                required
                                placeholder="Confirm your password"
                            >
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 fw-bold py-2">Create Account</button>
                </form>

                <div class="mt-4 text-center">
                    <a href="{{ route('login') }}" class="text-body-secondary text-decoration-none small">Already have an account? Log in here</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
