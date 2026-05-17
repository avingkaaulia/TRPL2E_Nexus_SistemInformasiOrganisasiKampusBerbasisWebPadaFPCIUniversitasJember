{{-- resources/views/admin/contact/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Kelola Kontak - Admin FPCI UNEJ')
@section('page-title', 'Kelola Informasi Kontak')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h4><i class="bi bi-envelope me-2"></i> Informasi Kontak</h4>
        <a href="{{ route('admin.contact.edit') }}" class="btn-edit-contact">
            <i class="bi bi-pencil-square me-1"></i> Edit Kontak
        </a>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        </div>
    @endif
    
    <div class="contact-preview">
        <!-- Contact Info Preview -->
        <div class="preview-section">
            <h5><i class="bi bi-info-circle me-2"></i> Detail Kontak</h5>
            <div class="info-list">
                <div class="info-item">
                    <div class="info-label"><i class="bi bi-envelope"></i> Email:</div>
                    <div class="info-value">{{ $contact->email ?? 'Belum diisi' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="bi bi-telephone"></i> No HP / WhatsApp:</div>
                    <div class="info-value">{{ $contact->no_hp ?? 'Belum diisi' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="bi bi-geo-alt"></i> Alamat:</div>
                    <div class="info-value">{{ $contact->alamat ?? 'Belum diisi' }}</div>
                </div>
            </div>
        </div>
        
        <!-- Social Media Preview -->
        <div class="preview-section">
            <h5><i class="bi bi-share me-2"></i> Media Sosial</h5>
            <div class="social-preview">
                @if($contact && $contact->instagram)
                <div class="social-item">
                    <i class="fab fa-instagram"></i>
                    <span>Instagram:</span>
                    <a href="https://instagram.com/{{ $contact->instagram }}" target="_blank">@ {{ $contact->instagram }}</a>
                </div>
                @endif
                
                @if($contact && $contact->linkedin)
                <div class="social-item">
                    <i class="fab fa-linkedin-in"></i>
                    <span>LinkedIn:</span>
                    <a href="https://linkedin.com/company/{{ $contact->linkedin }}" target="_blank">{{ $contact->linkedin }}</a>
                </div>
                @endif
                
                @if($contact && $contact->tiktok)
                <div class="social-item">
                    <i class="fab fa-tiktok"></i>
                    <span>TikTok:</span>
                    <a href="https://tiktok.com/@{{ $contact->tiktok }}" target="_blank">@ {{ $contact->tiktok }}</a>
                </div>
                @endif
                
                @if($contact && $contact->youtube)
                <div class="social-item">
                    <i class="fab fa-youtube"></i>
                    <span>YouTube:</span>
                    <a href="https://youtube.com/{{ $contact->youtube }}" target="_blank">{{ $contact->youtube }}</a>
                </div>
                @endif
                
                @if($contact && $contact->x)
                <div class="social-item">
                    <i class="fab fa-twitter"></i>
                    <span>Twitter / X:</span>
                    <a href="https://twitter.com/{{ $contact->x }}" target="_blank">@ {{ $contact->x }}</a>
                </div>
                @endif
                
                @if(!$contact || (!$contact->instagram && !$contact->linkedin && !$contact->tiktok && !$contact->youtube && !$contact->x))
                <div class="text-muted">Belum ada media sosial yang ditambahkan</div>
                @endif
            </div>
        </div>
        
        <!-- Preview Link ke Halaman Contact -->
        <div class="preview-section">
            <h5><i class="bi bi-eye me-2"></i> Preview</h5>
            <a href="{{ route('contact') }}" class="btn-preview-contact" target="_blank">
                <i class="bi bi-box-arrow-up-right me-1"></i> Lihat Halaman Contact
            </a>
        </div>
    </div>
</div>
@endsection