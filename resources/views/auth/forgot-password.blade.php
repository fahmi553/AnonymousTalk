@extends('layouts.auth')

@section('content')
<div class="card shadow-sm w-100" style="max-width: 400px;">
    <div class="card-body">
        <h3 class="card-title text-center mb-4">Forgot Password</h3>
        <p class="text-muted text-center">Enter your email to receive a password reset link.</p>

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" name="email" id="email" class="form-control" required autofocus>
            </div>

            <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
        </form>

        <div class="mt-3 text-center">
            <a href="{{ route('login') }}">Back to Login</a>
        </div>
    </div>
</div>
@endsection
