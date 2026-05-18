{{-- resources/views/about/index.blade.php --}}
@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/about.css') }}">

<!-- CAROUSEL -->
<div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
    <div class="carousel-indicators">
        @foreach($carousel as $key => $c)
            <button data-bs-target="#carouselExampleIndicators" data-bs-slide-to="{{ $key }}" class="{{ $key == 0 ? 'active' : '' }}"></button>
        @endforeach
    </div>
    <div class="carousel-inner">
        @foreach($carousel as $key => $c)
        <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
            <img src="{{ $c->image_url }}" class="d-block w-100" alt="{{ $c->title }}">
            <div class="carousel-caption">
                <h1>{{ $c->title }}</h1>
                <p>{{ Str::limit(strip_tags($c->content), 100) }}</p>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- About Sections Dinamis (KE BAWAH) - TENTANG, SEJARAH, TUJUAN -->
<section class="about-sections">
    <div class="container">
        <div class="about-container">
            <!-- Tentang -->
            @if($tentang)
            <div class="about-item">
                <h2>{{ $tentang->title }}</h2>
                {!! $tentang->content !!}
            </div>
            @endif
            
            <!-- Sejarah -->
            @if($sejarah)
            <div class="about-item">
                <h2>{{ $sejarah->title }}</h2>
                {!! $sejarah->content !!}
            </div>
            @endif
            
            <!-- Tujuan -->
            @if($tujuan)
            <div class="about-item">
                <h2>{{ $tujuan->title }}</h2>
                {!! $tujuan->content !!}
            </div>
            @endif
        </div>
    </div>
</section>

<!-- VISI & MISI - SEJAJAR (2 KOLOM) -->
<section class="visi-misi-section">
    <div class="container">
        <div class="visi-misi-row">
            <!-- Visi -->
            @if($visi)
            <div class="visi-box">
                <h3>{{ $visi->title }}</h3>
                {!! $visi->content !!}
            </div>
            @endif
            
            <!-- Misi -->
            @if($misi)
            <div class="misi-box">
                <h3>{{ $misi->title }}</h3>
                {!! $misi->content !!}
            </div>
            @endif
        </div>
    </div>
</section>

<!-- 🔥 POSTINGAN ABOUT LAINNYA (yang ditambahkan via admin) -->
@if($otherSections && $otherSections->count() > 0)
<section class="about-sections">
    <div class="container">
        <div class="about-container">
            @foreach($otherSections as $section)
            <div class="about-item">
                <h2>{{ $section->title }}</h2>
                {!! $section->content !!}
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Struktur Organisasi -->
<section class="struktur-section">
    <div class="container">
        <div class="section-header">
            <h2>Struktur Organisasi</h2>
            <div class="section-line"></div>
        </div>
        
        <div class="struktur-grid">
            @foreach($anggota as $a)
            <div class="anggota-card">
                <div class="anggota-foto-wrapper">
                    <img src="{{ asset($a->foto ?? 'assets/img/avatars/default-avatar.png') }}" 
                         class="anggota-foto" 
                         alt="{{ $a->divisi->nama_divisi ?? 'Member' }}">
                </div>
                <div class="anggota-info">
                    <p class="anggota-posisi">{{ $a->divisi->nama_divisi ?? 'Staff' }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Logo Section -->
<section class="logo-section">
    <div class="container">
        <h2>Our Logo</h2>
        <div class="logo-wrapper">
            @php
                $logo = App\Models\Setting::get('site_logo', 'assets/img/logo.png');
            @endphp
            <img src="{{ asset($logo) }}" class="main-logo" alt="FPCI UNEJ Logo">
        </div>
    </div>
</section>
@endsection