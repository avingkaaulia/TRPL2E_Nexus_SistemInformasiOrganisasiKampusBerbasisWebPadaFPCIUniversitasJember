@extends('layouts.auth')

@section('title', 'Register - FPCI UNEJ')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <img src="{{ asset('assets/img/logo.png') }}" width="80" alt="Logo">
            <h2>Daftar Akun</h2>
            <p>Bergabung menjadi anggota FPCI UNEJ</p>
        </div>
        
        @if($errors->any())
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <form action="{{ route('register.post') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="nama">
                    <i class="bi bi-person"></i> Nama Lengkap
                </label>
                <input type="text" 
                       name="nama" 
                       id="nama" 
                       class="form-control" 
                       value="{{ old('nama') }}" 
                       required>
            </div>
            
            <div class="form-group">
                <label for="username">
                    <i class="bi bi-person-badge"></i> Username
                </label>
                <input type="text" 
                       name="username" 
                       id="username" 
                       class="form-control" 
                       value="{{ old('username') }}" 
                       required>
                <small class="text-muted">Username unik untuk login</small>
            </div>
            
            <div class="form-group">
                <label for="email">
                    <i class="bi bi-envelope"></i> Email
                </label>
                <input type="email" 
                       name="email" 
                       id="email" 
                       class="form-control" 
                       value="{{ old('email') }}" 
                       required>
            </div>
            
            <div class="form-group">
                <label for="password">
                    <i class="bi bi-lock"></i> Password
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
                    <i class="bi bi-lock"></i> Konfirmasi Password
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
                <i class="bi bi-person-plus"></i> Daftar
            </button>
        </form>
        
        <div class="auth-footer">
            <p>Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a></p>
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