{{-- resources/views/contact/index.blade.php --}}
@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/contact.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

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

<!-- Contact Section -->
<section class="contact-section">
    <div class="contact-container">
        <!-- Contact Info -->
        <div class="contact-info">
            <h2>Contact Us</h2>
            <div class="contact-detail">
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="contact-text">
                        <h3>Email</h3>
                        <p><a href="mailto:{{ $contact->email ?? 'fpciunej@gmail.com' }}">{{ $contact->email ?? 'fpciunej@gmail.com' }}</a></p>
                    </div>
                </div>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <div class="contact-text">
                        <h3>Telephone / WhatsApp</h3>
                        <p><a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contact->no_hp ?? '6282245323805') }}">{{ $contact->no_hp ?? '+6282245323805' }}</a></p>
                    </div>
                </div>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="contact-text">
                        <h3>Address</h3>
                        <p>{{ $contact->alamat ?? 'Jl. Kalimantan No.37, Sumbersari, Jember, Jawa Timur' }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Social Media -->
        <div class="social-section">
            <h2>Follow Us</h2>
            <div class="social-grid">
                @if($contact && $contact->instagram)
                <a href="https://instagram.com/{{ $contact->instagram }}" target="_blank" class="social-card">
                    <div class="social-icon instagram">
                        <i class="fab fa-instagram"></i>
                    </div>
                    <div class="social-info">
                        <h4>Instagram</h4>
                        <p>@<span>{{ $contact->instagram }}</span></p>
                    </div>
                </a>
                @endif
                
                @if($contact && $contact->linkedin)
                <a href="https://linkedin.com/company/{{ $contact->linkedin }}" target="_blank" class="social-card">
                    <div class="social-icon linkedin">
                        <i class="fab fa-linkedin-in"></i>
                    </div>
                    <div class="social-info">
                        <h4>LinkedIn</h4>
                        <p>{{ $contact->linkedin }}</p>
                    </div>
                </a>
                @endif
                
                @if($contact && $contact->tiktok)
                <a href="https://tiktok.com/@{{ $contact->tiktok }}" target="_blank" class="social-card">
                    <div class="social-icon tiktok">
                        <i class="fab fa-tiktok"></i>
                    </div>
                    <div class="social-info">
                        <h4>TikTok</h4>
                        <p>@<span>{{ $contact->tiktok }}</span></p>
                    </div>
                </a>
                @endif
                
                @if($contact && $contact->youtube)
                <a href="https://youtube.com/{{ $contact->youtube }}" target="_blank" class="social-card">
                    <div class="social-icon youtube">
                        <i class="fab fa-youtube"></i>
                    </div>
                    <div class="social-info">
                        <h4>YouTube</h4>
                        <p>{{ $contact->youtube }}</p>
                    </div>
                </a>
                @endif
                
                @if($contact && $contact->x)
                <a href="https://twitter.com/{{ $contact->x }}" target="_blank" class="social-card">
                    <div class="social-icon twitter">
                        <i class="fab fa-twitter"></i>
                    </div>
                    <div class="social-info">
                        <h4>Twitter / X</h4>
                        <p>@<span>{{ $contact->x }}</span></p>
                    </div>
                </a>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="map-section">
    <div class="map-container">
        <iframe 
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3950.762102074463!2d113.707027!3d-8.159432!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd69553d71b1e83%3A0xed4ede264f7b7c4a!2sUniversitas%20Jember!5e0!3m2!1sid!2sid!4v1700000000000!5m2!1sid!2sid" 
            allowfullscreen="" 
            loading="lazy" 
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>
    </div>
</section>
@endsection