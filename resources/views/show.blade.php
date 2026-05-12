{{-- resources/views/show.blade.php --}}
@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/writing.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/comments.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/post.css') }}">

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            
            <!-- Post Header -->
            <div class="post-detail-header">
                <div class="post-category">{{ $post->category->category_name ?? 'Uncategorized' }}</div>
                <h1 class="post-title">{{ $post->title }}</h1>
                <div class="post-meta">
                    <span><i class="bi bi-person"></i> {{ $post->user->nama ?? 'Admin' }}</span>
                    <span><i class="bi bi-calendar"></i> {{ \Carbon\Carbon::parse($post->date_published)->format('F d, Y') }}</span>
                    <span><i class="bi bi-chat"></i> {{ $comments->count() }} Comments</span>
                </div>
            </div>
            
            <!-- Featured Image -->
            @if($post->featured_image_path)
            <div class="post-featured-image">
                    <img src="{{ $post->image_url }}" class="w-100" alt="{{ $post->title }}">
            </div>
            @endif
            
            <!-- Post Content -->
            <div class="post-content">
                {!! $post->content !!}
            </div>
            
            <!-- Gallery Section -->
            @if($post->gallery && $post->gallery->count() > 0)
            <div class="gallery-section">
                <h4><i class="bi bi-images me-2"></i> Gallery</h4>
                <div class="row g-3">
                    @foreach($post->gallery as $image)
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
            
            <!-- 🔥 COMMENTS SECTION 🔥 -->
            @include('comments', ['comments' => $comments, 'post' => $post])
            
            <!-- Related Posts -->
            @if($relatedPosts->count() > 0)
            <div class="related-posts">
                <h4><i class="bi bi-files me-2"></i> Related Posts</h4>
                <div class="row g-4">
                    @foreach($relatedPosts as $related)
                    <div class="col-md-4">
                        <div class="related-card">
                            <a href="{{ route('post.show', $related->id_post) }}" class="text-decoration-none">
                                <img src="{{ asset($related->featured_image_path ?? 'https://picsum.photos/400/250?random=' . $related->id_post) }}" 
                                     class="w-100" alt="{{ $related->title }}" style="height: 150px; object-fit: cover;">
                                <p class="related-title">{{ Str::limit($related->title, 50) }}</p>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection