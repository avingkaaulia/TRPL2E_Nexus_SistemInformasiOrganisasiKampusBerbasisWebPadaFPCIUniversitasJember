{{-- resources/views/profile/index.blade.php --}}
@extends('layouts.auth')

@section('title', 'Profil Saya - FPCI UNEJ')
@section('page-title', 'Profil Saya')

@section('content')
<div class="profile-container">
    <div class="profile-wrapper">
        <div class="profile-sidebar">
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-icon">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <h3>{{ $user->nama }}</h3>
                    <p class="profile-email">
                        <i class="bi bi-envelope"></i> {{ $user->email }}
                    </p>
                    <p class="profile-username">
                        <i class="bi bi-person-badge"></i> {{ $user->username }}
                    </p>
                    @if($user->id_role == 1)
                        <span class="role-badge admin">Administrator</span>
                    @else
                        <span class="role-badge member">Anggota</span>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="profile-content">
            <div class="profile-edit-card">
                <h4><i class="bi bi-pencil-square me-2"></i> Edit Profil</h4>
                
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    </div>
                @endif
                
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
                
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nama">
                                <i class="bi bi-person"></i> Nama Lengkap <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   name="nama" 
                                   id="nama" 
                                   class="form-control @error('nama') is-invalid @enderror" 
                                   value="{{ old('nama', $user->nama) }}" 
                                   required>
                            @error('nama')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="username">
                                <i class="bi bi-person-badge"></i> Username <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   name="username" 
                                   id="username" 
                                   class="form-control @error('username') is-invalid @enderror" 
                                   value="{{ old('username', $user->username) }}" 
                                   required>
                            <small class="form-text text-muted">Username unik untuk login</small>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">
                            <i class="bi bi-envelope"></i> Email <span class="text-danger">*</span>
                        </label>
                        <input type="email" 
                               name="email" 
                               id="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email', $user->email) }}" 
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <hr>
                    
                    <h5><i class="bi bi-lock me-2"></i> Ubah Password</h5>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Isi form di bawah hanya jika ingin mengubah password. Password baru minimal 6 karakter.
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="current_password">
                                <i class="bi bi-key"></i> Password Saat Ini
                            </label>
                            <div class="password-wrapper">
                                <input type="password" 
                                       name="current_password" 
                                       id="current_password" 
                                       class="form-control @error('current_password') is-invalid @enderror">
                                <i class="bi bi-eye-slash toggle-password" onclick="togglePassword('current_password')"></i>
                            </div>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="password">
                                <i class="bi bi-key"></i> Password Baru
                            </label>
                            <div class="password-wrapper">
                                <input type="password" 
                                       name="password" 
                                       id="password" 
                                       class="form-control @error('password') is-invalid @enderror">
                                <i class="bi bi-eye-slash toggle-password" onclick="togglePassword('password')"></i>
                            </div>
                            <small class="form-text text-muted">Minimal 6 karakter</small>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="password_confirmation">
                                <i class="bi bi-key"></i> Konfirmasi Password Baru
                            </label>
                            <div class="password-wrapper">
                                <input type="password" 
                                       name="password_confirmation" 
                                       id="password_confirmation" 
                                       class="form-control @error('password_confirmation') is-invalid @enderror">
                                <i class="bi bi-eye-slash toggle-password" onclick="togglePassword('password_confirmation')"></i>
                            </div>
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-save">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan
                        </button>
                        <a href="{{ route('home') }}" class="btn-cancel">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
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