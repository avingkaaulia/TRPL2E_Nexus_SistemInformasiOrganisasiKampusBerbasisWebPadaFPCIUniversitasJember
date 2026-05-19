{{-- resources/views/auth/reset-password.blade.php --}}
@extends('layouts.auth')

@section('title', 'Reset Password - FPCI UNEJ')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <img src="{{ asset('assets/img/logo.png') }}" width="80" alt="Logo">
            <h2>Reset Password</h2>
            <p>Buat password baru untuk akun Anda</p>
        </div>
        
        @if(session('error'))
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            </div>
        @endif
        
        @if($errors->any())
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <form action="{{ route('password.update') }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            
            <div class="form-group">
                <label for="password">
                    <i class="bi bi-lock"></i> Password Baru
                </label>
                <div class="password-wrapper">
                    <input type="password" 
                           name="password" 
                           id="password" 
                           class="form-control" 
                           required>
                    <i class="bi bi-eye-slash toggle-password" onclick="togglePassword('password')"></i>
                </div>
                <small class="text-muted">Minimal 6 karakter</small>
            </div>
            
            <div class="form-group">
                <label for="password_confirmation">
                    <i class="bi bi-lock"></i> Konfirmasi Password Baru
                </label>
                <div class="password-wrapper">
                    <input type="password" 
                           name="password_confirmation" 
                           id="password_confirmation" 
                           class="form-control" 
                           required>
                    <i class="bi bi-eye-slash toggle-password" onclick="togglePassword('password_confirmation')"></i>
                </div>
            </div>
            
            <button type="submit" class="btn-login-submit">
                <i class="bi bi-check-circle"></i> Reset Password
            </button>
        </form>
        
        <div class="auth-footer">
            <p><a href="{{ route('login') }}">← Kembali ke Login</a></p>
        </div>
    </div>
</div>

@push('scripts')
<script>
function togglePassword(fieldId) {
    const password = document.getElementById(fieldId);
    const toggle = password.nextElementSibling;
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