@extends('layouts.auth')

@section('content')
<div class="container-fluid">
    <div class="row min-vh-100">
        {{-- Left Side: Branding (Same as Login) --}}
        <div class="col-md-6 d-flex flex-column justify-content-center align-items-center bg-body-tertiary p-5 d-none d-md-flex">
            <i class="fas fa-shield-alt fa-5x text-primary mb-4"></i>
            <h1 class="fw-bold text-body-emphasis">Anonymous Talk</h1>
            <p class="text-body-secondary fs-5" style="max-width: 400px; text-align: center;">
                Secure your account and get back to the conversation.
            </p>
        </div>

        {{-- Right Side: Reset Form --}}
        <div class="col-md-6 d-flex flex-column justify-content-center align-items-center p-4 p-md-5 bg-body">
            <div class="w-100" style="max-width: 420px;">
                <div class="mb-4">
                    <h3 class="fw-bold text-body-emphasis">Reset Password</h3>
                    <p class="text-body-secondary">Enter your email and new password below.</p>
                </div>

                {{-- Error Handling --}}
                @if ($errors->any())
                    <div class="alert alert-danger py-2 mb-4" role="alert">
                        <ul class="mb-0 small ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    {{-- This hidden token is required by Laravel --}}
                    <input type="hidden" name="token" value="{{ $token }}">

                    {{-- Email Address --}}
                    <div class="mb-3">
                        <label for="email" class="form-label fw-medium">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-body-tertiary"><i class="fas fa-envelope"></i></span>
                            <input id="email" type="email" class="form-control bg-body" name="email" value="{{ $email ?? old('email') }}" required autofocus placeholder="you@example.com">
                        </div>
                    </div>

                    {{-- New Password --}}
                    <div class="mb-3">
                        <label for="password" class="form-label fw-medium">New Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-body-tertiary"><i class="fas fa-lock"></i></span>
                            <input id="password" type="password" class="form-control bg-body" name="password" required placeholder="New password">
                        </div>
                    </div>

                    {{-- Confirm Password --}}
                    <div class="mb-4">
                        <label for="password-confirm" class="form-label fw-medium">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-body-tertiary"><i class="fas fa-check-circle"></i></span>
                            <input id="password-confirm" type="password" class="form-control bg-body" name="password_confirmation" required placeholder="Confirm new password">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2 mb-3">
                        Reset Password
                    </button>

                    <div class="text-center">
                        <a href="{{ route('login') }}" class="text-decoration-none small text-body-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
