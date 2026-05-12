{{-- resources/views/writings/all.blade.php --}}
@extends('layouts.app')

@section('title', 'All Writings - FPCI UNEJ')
@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/writing.css') }}">

<div class="container py-5">
    <div class="section-header">
        <h2>All Writings</h2>
        <div class="section-line"></div>
    </div>

    <!-- Search Form -->
    <div class="row mb-4">
        <div class="col-md-6 mx-auto">
            <form action="{{ route('writings.all') }}" method="GET" class="search-wrapper d-flex">
                <input type="text" name="search" class="search-input flex-grow-1" placeholder="Search writings..." value="{{ request('search') }}">
                <button type="submit" class="btn-search-submit"><i class="bi bi-search"></i> Search</button>
            </form>
        </div>
    </div>

    <!-- Writings Grid -->
    <div class="row">
        @forelse($posts as $post)
        <div class="col-lg-4 col-md-6 col-sm-6 mb-4">
            <a href="{{ route('writings.show', $post->id_post) }}" class="text-decoration-none">
                <div class="writing-card">
                    <div class="card-image">
                        <img src="{{ $post->image_url ?? 'https://picsum.photos/400/300?random=' . $post->id_post }}" alt="{{ $post->title }}">
                    </div>
                    <div class="separator"></div>
                    <div class="card-body">
                        <div class="category">{{ $post->category->category_name ?? 'Uncategorized' }}</div>
                        <h5 class="title">{{ Str::limit($post->title, 60) }}</h5>
                        <p class="excerpt">{{ Str::limit(strip_tags($post->content), 100) }}</p>
                        <div class="meta">
                            <span class="date"><i class="bi bi-calendar me-1"></i>{{ \Carbon\Carbon::parse($post->date_published)->format('M d, Y') }}</span>
                            <span class="read-more">Read More →</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12">
            <div class="empty-state text-center py-5">
                <i class="bi bi-journal-bookmark-fill" style="font-size: 48px; color: #ccc;"></i>
                <h4 class="mt-3">No writings found</h4>
                <a href="{{ route('writings') }}" class="btn btn-main">Back to Writings</a>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        {{ $posts->withQueryString()->links() }}
    </div>

    <!-- Back Button -->
    <div class="text-center mt-4">
        <a href="{{ route('writings') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i> Back to Writings
        </a>
    </div>
</div>
@endsection