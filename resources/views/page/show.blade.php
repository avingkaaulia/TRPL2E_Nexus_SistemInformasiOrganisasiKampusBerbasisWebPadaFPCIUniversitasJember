{{-- resources/views/page/show.blade.php --}}
@extends('layouts.app')

@section('title', $page->title . ' - FPCI UNEJ')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/writing.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/comments.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/post.css') }}">

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            
            <!-- Page Header -->
            <div class="post-detail-header">
                @if($page->category)
                    <div class="post-category">{{ $page->category->category_name ?? 'Page' }}</div>
                @endif
                <h1 class="post-title">{{ $page->title }}</h1>
                <div class="post-meta">
                    <span><i class="bi bi-person"></i> {{ $page->user->nama ?? 'Admin' }}</span>
                    <span><i class="bi bi-calendar"></i> {{ \Carbon\Carbon::parse($page->date_published)->format('F d, Y') }}</span>
                    @if($page->gallery && $page->gallery->count() > 0)
                    <span><i class="bi bi-images"></i> {{ $page->gallery->count() }} Gallery</span>
                    @endif
                </div>
            </div>
            
            <!-- Featured Image -->
            @if($page->featured_image_path)
            <div class="post-featured-image">
                <img src="{{ $page->image_url }}" class="w-100" alt="{{ $page->title }}">
            </div>
            @endif
            
            <!-- Page Content -->
            <div class="post-content">
                {!! $page->content !!}
            </div>
            
            <!-- Gallery Section -->
            @if($page->gallery && $page->gallery->count() > 0)
            <div class="gallery-section">
                <h4><i class="bi bi-images me-2"></i> Gallery</h4>
                <div class="row g-3">
                    @foreach($page->gallery as $image)
                    <div class="col-md-4 col-sm-6">
                        <div class="gallery-item">
                            <img src="{{ $image->image_url }}" class="img-fluid rounded w-100" alt="Gallery">
                            @if($image->description)
                            <div class="gallery-caption">{{ $image->description }}</div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            
            <!-- ← TAMBAHKAN INI: Post dengan kategori sama dengan page -->
            @if(isset($postsInPage) && $postsInPage->count() > 0)
            <div class="page-posts-section mt-5">
                <h4><i class="bi bi-files me-2"></i> Postingan Terkait</h4>
                <div class="row g-4">
                    @foreach($postsInPage as $relatedPost)
                    <div class="col-md-6">
                        <div class="page-post-card">
                            <a href="{{ route('post.show', $relatedPost->id_post) }}" class="text-decoration-none">
                                @if($relatedPost->featured_image_path)
                                <img src="{{ $relatedPost->image_url }}" 
                                     class="w-100" 
                                     alt="{{ $relatedPost->title }}" 
                                     style="height: 180px; object-fit: cover; border-radius: 8px;">
                                @endif
                                <div class="page-post-body mt-2">
                                    <h5 class="page-post-title">{{ $relatedPost->title }}</h5>
                                    <p class="page-post-excerpt">
                                        {{ Str::limit(strip_tags($relatedPost->content), 100) }}
                                    </p>
                                    <span class="text-muted small">
                                        <i class="bi bi-calendar"></i> 
                                        {{ \Carbon\Carbon::parse($relatedPost->date_published)->format('M d, Y') }}
                                    </span>
                                </div>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            <!-- ← END TAMBAHKAN -->
            
            <!-- Back Button -->
            <div class="text-center mt-4">
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>
@endsection