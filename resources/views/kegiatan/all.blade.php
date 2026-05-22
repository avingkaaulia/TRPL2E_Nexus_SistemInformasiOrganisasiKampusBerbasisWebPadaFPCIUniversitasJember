@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/kegiatan.css') }}">

<div class="container my-5">
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
    <div class="result-info mb-3 text-muted text-center">
        Menampilkan {{ $posts->firstItem() }} - {{ $posts->lastItem() }} dari {{ $posts->total() }} data
    </div>
    @endif
    <div class="row g-4">
        @forelse($posts as $post)
        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card-kegiatan position-relative h-100">
                <img src="{{ getImageUrl($post->featured_image_path) }}" alt="{{ $post->title }}" class="card-img-top">
                <span class="category-badge">{{ $post->category->category_name ?? 'Uncategorized' }}</span>
                <div class="card-body">
                    <h5>{{ Str::limit($post->title, 60) }}</h5>
                    <a href="{{ route('kegiatan.show', $post->id_post) }}" class="btn btn-main mt-2">
                        Detail <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <div class="empty-state">
                <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                <h4 class="mt-3">Belum ada data</h4>
                <p>Belum ada konten untuk kategori ini.</p>
                <a href="{{ route('kegiatan.index') }}" class="btn btn-main mt-2">Kembali ke Events</a>
            </div>
        </div>
        @endforelse
    </div>

    <div class="pagination-wrapper">
    {{ $posts->appends(request()->query())->links('vendor.pagination.custom') }}
</div>

    <div class="text-center mt-4">
        <a href="{{ route('kegiatan.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i> Back to Events & Programs
        </a>
    </div>
</div>
@endsection