{{-- resources/views/pages/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Halaman - FPCI UNEJ')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/writing.css') }}">

<div class="container py-5">
    <div class="section-header">
        <h2>Halaman</h2>
        <div class="section-line"></div>
    </div>

    <!-- Result Info -->
    @if($pages->total() > 0)
    <div class="result-info mb-3 text-muted">
        Menampilkan {{ $pages->firstItem() }} - {{ $pages->lastItem() }} dari {{ $pages->total() }} halaman
    </div>
    @endif

    <!-- Pages Grid -->
    <div class="row">
        @forelse($pages as $page)
        <div class="col-lg-4 col-md-6 col-sm-6 mb-4">
            <a href="{{ route('page.show', $page->id_post) }}" class="text-decoration-none">
                <div class="writing-card">
                    <div class="card-image">
                        <img src="{{ getImageUrl($page->featured_image_path) }}" alt="{{ $page->title }}">
                    </div>
                    <div class="separator"></div>
                    <div class="card-body">
                        <div class="category">{{ $page->category->category_name ?? 'Page' }}</div>
                        <h5 class="title">{{ Str::limit($page->title, 60) }}</h5>
                        <p class="excerpt">{{ Str::limit(strip_tags($page->content), 100) }}</p>
                        <div class="meta">
                            <span class="date"><i class="bi bi-calendar me-1"></i>{{ \Carbon\Carbon::parse($page->date_published)->format('M d, Y') }}</span>
                            <span class="read-more">Read More →</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12">
            <div class="empty-state text-center py-5">
                <i class="bi bi-file-earmark-text" style="font-size: 48px; color: #ccc;"></i>
                <h4 class="mt-3">Belum ada halaman</h4>
                <a href="{{ route('home') }}" class="btn btn-main">Kembali ke Home</a>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="pagination-wrapper">
        {{ $pages->links('vendor.pagination.custom') }}
    </div>
</div>
@endsection