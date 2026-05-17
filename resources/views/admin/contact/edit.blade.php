{{-- resources/views/admin/contact/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Kontak - Admin FPCI UNEJ')
@section('page-title', 'Edit Informasi Kontak')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h4><i class="bi bi-pencil-square me-2"></i> Edit Informasi Kontak</h4>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        </div>
    @endif
    
    <form action="{{ route('admin.contact.update') }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-section-contact">
            <h5><i class="bi bi-envelope me-2"></i> Kontak Utama</h5>
            
            <div class="form-group">
                <label for="email">Email <span class="text-danger">*</span></label>
                <input type="email" 
                       name="email" 
                       id="email" 
                       class="form-control @error('email') is-invalid @enderror" 
                       value="{{ old('email', $contact->email ?? '') }}" 
                       required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="no_hp">No HP / WhatsApp <span class="text-danger">*</span></label>
                <input type="text" 
                       name="no_hp" 
                       id="no_hp" 
                       class="form-control @error('no_hp') is-invalid @enderror" 
                       value="{{ old('no_hp', $contact->no_hp ?? '') }}" 
                       required>
                @error('no_hp')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="alamat">Alamat <span class="text-danger">*</span></label>
                <textarea name="alamat" 
                          id="alamat" 
                          class="form-control @error('alamat') is-invalid @enderror" 
                          rows="3" 
                          required>{{ old('alamat', $contact->alamat ?? '') }}</textarea>
                @error('alamat')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="form-section-contact">
            <h5><i class="bi bi-share me-2"></i> Media Sosial</h5>
            <p class="text-muted small">Kosongkan jika tidak ingin menampilkan media sosial tersebut</p>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="instagram">
                            <i class="fab fa-instagram me-1"></i> Instagram
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">@</span>
                            <input type="text" 
                                   name="instagram" 
                                   id="instagram" 
                                   class="form-control" 
                                   value="{{ old('instagram', $contact->instagram ?? '') }}" 
                                   placeholder="username">
                        </div>
                        <small class="text-muted">Contoh: fpciunej</small>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="linkedin">
                            <i class="fab fa-linkedin-in me-1"></i> LinkedIn
                        </label>
                        <input type="text" 
                               name="linkedin" 
                               id="linkedin" 
                               class="form-control" 
                               value="{{ old('linkedin', $contact->linkedin ?? '') }}" 
                               placeholder="company-name">
                        <small class="text-muted">Contoh: fpci-chapter-universitas-jember</small>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="tiktok">
                            <i class="fab fa-tiktok me-1"></i> TikTok
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">@</span>
                            <input type="text" 
                                   name="tiktok" 
                                   id="tiktok" 
                                   class="form-control" 
                                   value="{{ old('tiktok', $contact->tiktok ?? '') }}" 
                                   placeholder="username">
                        </div>
                        <small class="text-muted">Contoh: fpciunej</small>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="youtube">
                            <i class="fab fa-youtube me-1"></i> YouTube
                        </label>
                        <input type="text" 
                               name="youtube" 
                               id="youtube" 
                               class="form-control" 
                               value="{{ old('youtube', $contact->youtube ?? '') }}" 
                               placeholder="channel-handle">
                        <small class="text-muted">Contoh: @FPCIUNEJ atau channel ID</small>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="x">
                            <i class="fab fa-twitter me-1"></i> Twitter / X
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">@</span>
                            <input type="text" 
                                   name="x" 
                                   id="x" 
                                   class="form-control" 
                                   value="{{ old('x', $contact->x ?? '') }}" 
                                   placeholder="username">
                        </div>
                        <small class="text-muted">Contoh: fpciunej</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-actions-contact">
            <button type="submit" class="btn-save-contact">
                <i class="bi bi-save me-1"></i> Simpan Perubahan
            </button>
            <a href="{{ route('admin.contact.index') }}" class="btn-cancel-contact">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </form>
</div>
@endsection