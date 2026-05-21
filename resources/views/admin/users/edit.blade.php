{{-- resources/views/admin/users/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit User - Admin FPCI UNEJ')
@section('page-title', 'Edit User')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h4><i class="bi bi-pencil-square me-2"></i> Edit User: {{ $user->nama }}</h4>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    <form action="{{ route('admin.users.update', $user->id_user) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="nama">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="nama" id="nama" class="form-control" value="{{ old('nama', $user->nama) }}" required>
                    @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="username">Username <span class="text-danger">*</span></label>
                    <input type="text" name="username" id="username" class="form-control" value="{{ old('username', $user->username) }}" required>
                    @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label for="email">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        
        <div class="form-group">
            <label for="id_role">Role <span class="text-danger">*</span></label>
            <select name="id_role" id="id_role" class="form-control" required>
                @foreach($roles as $role)
                <option value="{{ $role->id_role }}" {{ old('id_role', $user->id_role) == $role->id_role ? 'selected' : '' }}>
                    {{ $role->nama_role }}
                </option>
                @endforeach
            </select>
            <small class="text-muted">Role menentukan hak akses user di sistem</small>
            @error('id_role')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        
        <hr>
        
        <h5 class="mt-3">Ubah Password (Opsional)</h5>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="password">Password Baru</label>
                    <input type="password" name="password" id="password" class="form-control">
                    <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="password_confirmation">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                </div>
            </div>
        </div>
        
        <div class="form-actions mt-4">
            <button type="submit" class="btn-save">
                <i class="bi bi-save me-1"></i> Simpan Perubahan
            </button>
            <a href="{{ route('admin.users.index') }}" class="btn-cancel">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </form>
</div>
@endsection