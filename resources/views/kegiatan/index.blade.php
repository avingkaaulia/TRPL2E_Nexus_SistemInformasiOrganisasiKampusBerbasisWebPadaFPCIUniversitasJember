@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/kegiatan.css') }}">

<!-- CAROUSEL DINAMIS -->
<div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
    <div class="carousel-indicators">
        @foreach($carousel as $key => $c)
            <button data-bs-target="#carouselExampleIndicators" data-bs-slide-to="{{ $key }}" class="{{ $key == 0 ? 'active' : '' }}"></button>
        @endforeach
    </div>
    <div class="carousel-inner">
        @foreach($carousel as $key => $c)
        <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
            <img src="{{ asset($c->featured_image_path) }}" class="d-block w-100" alt="{{ $c->title }}">
            <div class="carousel-caption">
                <h1>{{ $c->title }}</h1>
                <p>{{ Str::limit($c->content, 100) }}</p>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- CATEGORY FILTER BUTTONS -->
<div class="categories">
    <a href="{{ route('kegiatan.index') }}" class="btn-category {{ !request()->get('category') ? 'active' : '' }}">All</a>
    @foreach($categories->where('category_name', 'kegiatan') as $cat)
    <a href="{{ route('kegiatan.index', ['category' => $cat->category_name]) }}" class="btn-category {{ request()->get('category') == $cat->category_name ? 'active' : '' }}">
        {{ $cat->category_name }}
    </a>
    @endforeach
</div>

<!-- LATEST UPDATES SECTION -->
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="section-header text-start mb-0">
            <h2>Latest Updates</h2>
            <div class="section-line"></div>
        </div>
        <a href="{{ route('kegiatan.all') }}" class="see-more-link">See More <i class="bi bi-arrow-right-circle ms-1"></i></a>
    </div>

    <div class="row g-4">
        @forelse($latest as $post)
        <div class="col-md-4">
            <div class="card-kegiatan position-relative">
                <img src="{{ asset($post->featured_image_path) }}" alt="{{ $post->title }}">
                <span class="category-badge">{{ $post->category->category_name ?? 'Kegiatan' }}</span>
                <div class="card-body">
                    <h5>{{ Str::limit($post->title, 50) }}</h5>
                    <!-- Link untuk detail kegiatan menggunakan route('kegiatan.show') -->
<a href="{{ route('kegiatan.show', $post->id_post) }}" class="btn btn-main mt-2">
    Detail <i class="bi bi-info-circle ms-1"></i>
</a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <p>Belum ada kegiatan terbaru.</p>
        </div>
        @endforelse
    </div>
</div>

<!-- OTHER EVENTS & PROGRAMS -->
<div class="container mt-5">
    <div class="section-header">
        <h2>Other Events & Programs</h2>
        <div class="section-line"></div>
    </div>

    <div class="row g-4">
        @forelse($others as $event)
        <div class="col-md-4 col-sm-6">
            <div class="card-kegiatan position-relative">
                <img src="{{ asset($event->featured_image_path) }}" alt="{{ $event->title }}">
                <span class="category-badge">{{ $event->category->category_name ?? 'Kegiatan' }}</span>
                <div class="card-body">
                    <h5>{{ Str::limit($event->title, 50) }}</h5>
                    <!-- Link untuk detail kegiatan menggunakan route('kegiatan.show') -->
<a href="{{ route('kegiatan.show', $post->id_post) }}" class="btn btn-main mt-2">
    Detail <i class="bi bi-info-circle ms-1"></i>
</a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <p>Belum ada kegiatan lainnya.</p>
        </div>
        @endforelse
    </div>
</div>

<!-- FEATURED PROGRAMS -->
<div class="container mt-5 mb-5">
    <div class="section-header">
        <h2>Featured Programs</h2>
        <div class="section-line"></div>
    </div>

    <div class="row g-4">
        @forelse($featured as $item)
        <div class="col-md-4">
            <a href="{{ route('kegiatan.show', $post->id_post) }}" class="featured-card">
                <div class="featured-img">
                    <img src="{{ asset($item->featured_image_path) }}" alt="{{ $item->title }}">
                    <div class="featured-overlay">
                        <h5>{{ Str::limit($item->title, 40) }}</h5>
                    </div>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <p>Belum ada program unggulan.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection