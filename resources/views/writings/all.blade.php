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

   <!-- Search & Sorting Form -->
    <div class="row mb-4">
        <div class="col-md-8 mx-auto">
            <form action="{{ url()->current() }}" method="GET" class="d-flex gap-2 flex-wrap justify-content-center">
                <select name="sort" class="form-select" style="width: auto;">
                    <option value="terbaru" {{ request('sort') == 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                    <option value="terlama" {{ request('sort') == 'terlama' ? 'selected' : '' }}>Terlama</option>
                    <option value="az" {{ request('sort') == 'az' ? 'selected' : '' }}>A-Z</option>
                    <option value="za" {{ request('sort') == 'za' ? 'selected' : '' }}>Z-A</option>
                </select>
                <button type="submit" class="btn-search-submit"><i class="bi bi-sort-down"></i> Urutkan</button>
                @if(request('sort'))
                <a href="{{ url()->current() }}" class="btn-reset">Reset</a>
                @endif
            </form>
        </div>
    </div>

    <!-- Result Info -->
    @if($posts->total() > 0)
    <div class="result-info mb-3 text-muted">
        Menampilkan {{ $posts->firstItem() }} - {{ $posts->lastItem() }} dari {{ $posts->total() }} writings
    </div>
    @endif

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
    <div class="pagination-wrapper">
    {{ $posts->appends(request()->query())->links('vendor.pagination.custom') }}
</div>

    <!-- Back Button -->
    <div class="text-center mt-4">
        <a href="{{ route('writings') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i> Back to Writings
        </a>
    </div>
</div>
@endsection