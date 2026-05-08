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

<!-- ==================== EVENT REGULER ==================== -->
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="section-header text-start mb-0">
            <h2>📋 Event Reguler</h2>
            <div class="section-line"></div>
        </div>
        <a href="{{ route('kegiatan.event.reguler') }}" class="see-more-link">See More <i class="bi bi-arrow-right-circle ms-1"></i></a>
    </div>

    <div class="row g-4">
        @forelse($eventRegulerPosts as $event)
        <div class="col-md-4">
            <div class="card-event">
                <div class="card-image">
                    <img src="{{ asset($event->featured_image_path) }}" alt="{{ $event->title }}">
                    <div class="event-badge reguler">Event Reguler</div>
                </div>
                <div class="card-body">
                    <h5>{{ Str::limit($event->title, 50) }}</h5>
                    <p class="date"><i class="bi bi-calendar"></i> {{ \Carbon\Carbon::parse($event->date_published)->format('d M Y') }}</p>
                    <a href="{{ route('kegiatan.show', $event->id_post) }}" class="btn btn-event">Detail <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <p>Belum ada event reguler.</p>
        </div>
        @endforelse
    </div>
</div>

<!-- ==================== EVENT UNGGULAN ==================== -->
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="section-header text-start mb-0">
            <h2>⭐ Event Unggulan</h2>
            <div class="section-line"></div>
        </div>
        <a href="{{ route('kegiatan.event.unggulan') }}" class="see-more-link">See More <i class="bi bi-arrow-right-circle ms-1"></i></a>
    </div>

    <div class="row g-4">
        @forelse($eventUnggulanPosts as $event)
        <div class="col-md-4">
            <div class="card-event-unggulan">
                <div class="card-image">
                    <img src="{{ asset($event->featured_image_path) }}" alt="{{ $event->title }}">
                    <div class="event-badge unggulan">Event Unggulan</div>
                </div>
                <div class="card-body">
                    <h5>{{ Str::limit($event->title, 50) }}</h5>
                    <p class="date"><i class="bi bi-calendar"></i> {{ \Carbon\Carbon::parse($event->date_published)->format('d M Y') }}</p>
                    <a href="{{ route('kegiatan.show', $event->id_post) }}" class="btn btn-event-unggulan">Detail <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <p>Belum ada event unggulan.</p>
        </div>
        @endforelse
    </div>
</div>

<!-- ==================== PROGRAM BY STATUS ==================== -->
<div class="container mt-5 mb-5">
    <div class="section-header">
        <h2>📌 Programs by Status</h2>
        <div class="section-line"></div>
    </div>

    <div class="row g-4">
        <!-- Sedang Direncanakan -->
        <div class="col-md-4">
            <div class="status-card planned">
                <h4><i class="bi bi-clock-history"></i> Sedang Direncanakan</h4>
                <div class="status-list">
                    @forelse($programsPlanned as $program)
                    <a href="{{ route('kegiatan.show', $program->id_post) }}" class="status-item">
                        <span class="status-title">{{ Str::limit($program->title, 40) }}</span>
                        <span class="status-date">{{ \Carbon\Carbon::parse($program->date_published)->format('d M Y') }}</span>
                    </a>
                    @empty
                    <p class="text-muted">Tidak ada program direncanakan.</p>
                    @endforelse
                    <a href="{{ route('kegiatan.programs', 'planned') }}" class="see-more-link-small">See all →</a>
                </div>
            </div>
        </div>

        <!-- Sedang Berlangsung -->
        <div class="col-md-4">
            <div class="status-card ongoing">
                <h4><i class="bi bi-play-circle"></i> Sedang Berlangsung</h4>
                <div class="status-list">
                    @forelse($programsOngoing as $program)
                    <a href="{{ route('kegiatan.show', $program->id_post) }}" class="status-item">
                        <span class="status-title">{{ Str::limit($program->title, 40) }}</span>
                        <span class="status-date">{{ \Carbon\Carbon::parse($program->date_published)->format('d M Y') }}</span>
                    </a>
                    @empty
                    <p class="text-muted">Tidak ada program berlangsung.</p>
                    @endforelse
                    <a href="{{ route('kegiatan.programs', 'ongoing') }}" class="see-more-link-small">See all →</a>
                </div>
            </div>
        </div>

        <!-- Selesai -->
        <div class="col-md-4">
            <div class="status-card completed">
                <h4><i class="bi bi-check-circle"></i> Selesai</h4>
                <div class="status-list">
                    @forelse($programsCompleted as $program)
                    <a href="{{ route('kegiatan.show', $program->id_post) }}" class="status-item">
                        <span class="status-title">{{ Str::limit($program->title, 40) }}</span>
                        <span class="status-date">{{ \Carbon\Carbon::parse($program->date_published)->format('d M Y') }}</span>
                    </a>
                    @empty
                    <p class="text-muted">Tidak ada program selesai.</p>
                    @endforelse
                    <a href="{{ route('kegiatan.programs', 'completed') }}" class="see-more-link-small">See all →</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection