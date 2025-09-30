@extends('layouts.auth')

@section('content')
<div class="card shadow-sm w-100" style="max-width: 500px;">
    <div class="card-body">
        <h3 class="card-title text-center mb-4">Register</h3>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" id="username" class="form-control" required autofocus>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-success w-100">Register</button>
        </form>

        <div class="mt-3 text-center">
            <a href="{{ route('login') }}">Already have an account? Log in here</a>
        </div>
    </div>
</div>
@endsection
