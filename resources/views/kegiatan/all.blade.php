@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/kegiatan.css') }}">

<div class="container mt-5">
    <div class="section-header">
        <h2>
            @if(request()->routeIs('kegiatan.event.reguler'))
                📋 Event Reguler
            @elseif(request()->routeIs('kegiatan.event.unggulan'))
                ⭐ Event Unggulan
            @elseif(request()->routeIs('kegiatan.programs') && request()->route('status') == 'planned')
                📌 Program Sedang Direncanakan
            @elseif(request()->routeIs('kegiatan.programs') && request()->route('status') == 'ongoing')
                🚀 Program Sedang Berlangsung
            @elseif(request()->routeIs('kegiatan.programs') && request()->route('status') == 'completed')
                ✅ Program Selesai
            @else
                All Events & Programs
            @endif
        </h2>
        <div class="section-line"></div>
    </div>

    <div class="row g-4">
        @forelse($posts as $post)
        <div class="col-md-4 mb-4">
            <div class="card-kegiatan position-relative h-100">
                <img src="{{ asset($post->featured_image_path) }}" alt="{{ $post->title }}">
                <span class="category-badge">{{ $post->category->category_name ?? 'Uncategorized' }}</span>
                <div class="card-body">
                    <h5>{{ Str::limit($post->title, 60) }}</h5>
                    <a href="{{ route('kegiatan.show', $post->id_post) }}" class="btn btn-main mt-2">
                        Detail <i class="bi bi-info-circle ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <p>Belum ada data.</p>
        </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $posts->links() }}
    </div>
</div>
@endsection