{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.auth')

@section('title', 'Login - FPCI UNEJ')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <img src="{{ asset('assets/img/logo.png') }}" width="80" alt="Logo">
            <h2>Login</h2>
            <p>Masuk ke akun FPCI UNEJ Anda</p>
        </div>
        
        @if(session('error'))
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            </div>
        @endif
        
        @if(session('success'))
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            </div>
        @endif
        
        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="email">
                    <i class="bi bi-envelope"></i> Email
                </label>
                <input type="email" 
                       name="email" 
                       id="email" 
                       class="form-control @error('email') is-invalid @enderror" 
                       value="{{ old('email') }}" 
                       required autofocus>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="password">
                    <i class="bi bi-lock"></i> Password
                </label>
                <div class="password-wrapper">
                    <input type="password" 
                           name="password" 
                           id="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           required>
                    <i class="bi bi-eye-slash toggle-password" onclick="togglePassword()"></i>
                </div>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span>Ingat saya</span>
                </label>
                <a href="{{ route('password.request') }}" class="forgot-link">Lupa password?</a>
            </div>
            
            <button type="submit" class="btn-login-submit">
                <i class="bi bi-box-arrow-in-right"></i> Login
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
function togglePassword() {
    const password = document.getElementById('password');
    const toggle = document.querySelector('.toggle-password');
    if (password.type === 'password') {
        password.type = 'text';
        toggle.classList.remove('bi-eye-slash');
        toggle.classList.add('bi-eye');
    } else {
        password.type = 'password';
        toggle.classList.remove('bi-eye');
        toggle.classList.add('bi-eye-slash');
    }
}
</script>
@endpush
@endsection