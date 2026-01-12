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
                    <h3 class="fw-bold text-body-emphasis">Forgot Password?</h3>
                    <p class="text-body-secondary">No problem. Enter your email address below and we will send you a link to reset your password.</p>
                </div>
                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger py-2" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ $errors->first() }}
                    </div>
                @endif
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="mb-4">
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

                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2">Send Reset Link</button>
                </form>

                <div class="mt-4 text-center">
                    <a href="{{ route('login') }}" class="text-body-secondary text-decoration-underline small">Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
