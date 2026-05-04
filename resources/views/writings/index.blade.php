{{-- resources/views/writings/index.blade.php --}}
@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/writing.css') }}">

<div class="container py-5">

    <!-- Category Section -->
    <div class="category-section">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
            <h3 class="section-heading" style="font-size: 1.8rem; font-weight: 700; color: #1C150F;">Writings Category</h3>
            <a href="{{ route('writings') }}" class="text-decoration-none" style="color: #5C6844;">View All Writings →</a>
        </div>
        <div class="category-grid">
            @foreach($categories->whereIn('category_name', ['Foreign Policy', 'Technology', 'Economy', 'Security', 'writings', 'kegiatan', 'pengumuman']) as $cat)
            <a href="{{ route('writings.category', $cat->id_category) }}" class="text-decoration-none">
                <div class="category-item {{ isset($currentCategory) && $currentCategory->id_category == $cat->id_category ? 'active' : '' }}">
                    <span class="category-name">{{ $cat->category_name }}</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>

    <!-- Current Category Info -->
    @if(isset($currentCategory))
    <div class="alert alert-info mb-4 text-center">
        <i class="bi bi-folder"></i> 
        Showing posts from category: <strong>{{ $currentCategory->category_name }}</strong>
        <a href="{{ route('writings') }}" class="ms-3">View all →</a>
    </div>
    @endif

    <!-- Search Form -->
    <div class="row mb-4">
        <div class="col-md-6 mx-auto">
            <form action="{{ isset($currentCategory) ? route('writings.category', $currentCategory->id_category) : route('writings') }}" method="GET" class="search-wrapper d-flex">
                <input type="text" name="search" class="search-input flex-grow-1" 
                       placeholder="Search writings..." value="{{ $search ?? '' }}">
                <button type="submit" class="btn-search-submit">
                    <i class="bi bi-search"></i> Search
                </button>
            </form>
        </div>
    </div>

    <!-- Filter Info Search -->
    @if($search)
    <div class="alert alert-info mb-4 text-center">
        <i class="bi bi-search"></i> 
        Showing results for: <strong>"{{ $search }}"</strong>
        <a href="{{ isset($currentCategory) ? route('writings.category', $currentCategory->id_category) : route('writings') }}" class="ms-3">Clear search →</a>
    </div>
    @endif

    <!-- Hot Topics -->
    <div class="hot-topics">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
            <h3 class="section-heading" style="font-size: 1.5rem; font-weight: 700; color: #1C150F;">Our Hot Topics Of Writings</h3>
        </div>
        <div class="row">
            @forelse($hotTopics as $topic)
            <div class="col-md-4 col-sm-6 mb-3">
                <div class="hot-topic-item">
                    <i class="bi bi-fire me-2" style="color: #e74c3c;"></i>
                    <a href="{{ route('writings.show', $topic->id_post) }}" class="text-decoration-none">
                        {{ Str::limit($topic->title, 50) }}
                    </a>
                </div>
            </div>
            @empty
            <div class="col-12">
                <p class="text-muted text-center">No hot topics available.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Writings Grid -->
    <div class="writings-gallery">
        <div class="row">
            @forelse($posts as $post)
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <a href="{{ route('writings.show', $post->id_post) }}" class="text-decoration-none">
                    <div class="writing-card">
                        <div class="card-image">
                            <img src="{{ asset($post->featured_image_path ?? 'https://picsum.photos/400/300?random=' . $post->id_post) }}" 
                                 alt="{{ $post->title }}">
                        </div>
                        <div class="separator"></div>
                        <div class="card-body">
                            <div class="category">{{ $post->category->category_name ?? 'Uncategorized' }}</div>
                            <h5 class="title">{{ Str::limit($post->title, 60) }}</h5>
                            <p class="excerpt">{{ Str::limit(strip_tags($post->content), 100) }}</p>
                            <div class="meta">
                                <span class="date">
                                    <i class="bi bi-calendar me-1"></i>
                                    {{ \Carbon\Carbon::parse($post->date_published)->format('M d, Y') }}
                                </span>
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
                    <p>Try different search terms or check back later.</p>
                    <a href="{{ route('writings') }}" class="btn btn-main">View All Writings</a>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            @if($posts->hasPages())
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        @if($posts->onFirstPage())
                            <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $posts->previousPageUrl() }}">&laquo;</a></li>
                        @endif

                        @foreach($posts->getUrlRange(1, $posts->lastPage()) as $page => $url)
                            @if($page == $posts->currentPage())
                                <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                            @endif
                        @endforeach

                        @if($posts->hasMorePages())
                            <li class="page-item"><a class="page-link" href="{{ $posts->nextPageUrl() }}">&raquo;</a></li>
                        @else
                            <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                        @endif
                    </ul>
                </nav>
            @endif
        </div>
    </div>
</div>

<!-- Create Your Own Section -->
<div class="create-own-section mt-5 mb-5">
    <div class="container">
        <div class="create-own-wrapper">
            <h3 class="create-own-title">Start Your Writing Journey</h3>
            <p class="create-own-subtitle">Share your thoughts, stories, and ideas with our community</p>
            <a href="#" class="btn-create-own" onclick="alert('Coming Soon! Feature will be available soon.')">
                Create Your Own Now! <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</div>

@endsection