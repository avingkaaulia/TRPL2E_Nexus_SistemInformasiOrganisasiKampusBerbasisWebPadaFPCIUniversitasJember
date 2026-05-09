{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.admin')

@section('title', 'Dashboard - Admin FPCI UNEJ')
@section('page-title', 'Dashboard')

@section('content')
<!-- STATS CARDS -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-info">
            <h2>{{ $totalPosts }}</h2>
            <p>Total Postingan</p>
        </div>
        <div class="stat-icon">
            <i class="bi bi-file-post"></i>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h2>{{ $totalKarya }}</h2>
            <p>Total Karya</p>
        </div>
        <div class="stat-icon">
            <i class="bi bi-book"></i>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h2>{{ $totalAnggota }}</h2>
            <p>Total Anggota</p>
        </div>
        <div class="stat-icon">
            <i class="bi bi-people"></i>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h2>{{ $totalComments }}</h2>
            <p>Total Komentar</p>
        </div>
        <div class="stat-icon">
            <i class="bi bi-chat-dots"></i>
        </div>
    </div>
</div>

<!-- TWO COLUMNS -->
<div class="two-columns">
    <!-- Recent Posts -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h4><i class="bi bi-clock-history me-2"></i> Postingan Terbaru</h4>
            <a href="#">Lihat Semua →</a>
        </div>
        <table class="admin-table">
            @forelse($recentPosts as $post)
            <tr>
                <td>{{ Str::limit($post->title, 40) }}</td>
                <td>
                    @php
                        $class = 'badge-publish';
                        if($post->status == 'draft') $class = 'badge-draft';
                        elseif($post->status == 'pending') $class = 'badge-pending';
                    @endphp
                    <span class="badge-status {{ $class }}">{{ $post->status }}</span>
                </td>
            </tr>
            @empty
            <tr><td colspan="2" class="text-center">Belum ada postingan</td><tr>
            @endforelse
        </table>
    </div>

    <!-- Recent Comments -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h4><i class="bi bi-chat me-2"></i> Komentar Terbaru</h4>
            <a href="#">Lihat Semua →</a>
        </div>
        <table class="admin-table">
            @forelse($recentComments as $comment)
            <tr>
                <td>{{ Str::limit($comment->isi_komentar, 35) }}</td>
                <td><small>{{ $comment->nama_pengunjung }}</small></td>
            </tr>
            @empty
            <td><td colspan="2" class="text-center">Belum ada komentar</td></tr>
            @endforelse
        </table>
    </div>
</div>

<!-- BOTTOM ROW -->
<div class="two-columns">
    <!-- Category Stats -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h4><i class="bi bi-tags me-2"></i> Postingan per Kategori</h4>
            <a href="#">Kelola →</a>
        </div>
        @forelse($categoryStats as $cat)
        <div class="category-item">
            <span class="category-name">{{ $cat->category_name }}</span>
            <span class="category-count">{{ $cat->posts_count }} postingan</span>
        </div>
        @empty
        <p class="text-center text-muted">Belum ada kategori</p>
        @endforelse
    </div>

    <!-- Pendaftaran Status -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h4><i class="bi bi-person-plus me-2"></i> Pendaftaran Anggota</h4>
            <a href="{{ route('pendaftaran') }}">Kelola →</a>
        </div>
        <table class="admin-table">
            <tr>
                <td>Total Pendaftar</td>
                <td class="fw-bold">{{ $totalPendaftaran }}</td>
            </tr>
            <tr>
                <td>Menunggu Verifikasi</td>
                <td><span class="badge-status badge-menunggu">{{ $pendingPendaftaran }}</span></td>
            </tr>
            <tr>
                <td>Sudah Diverifikasi</td>
                <td>{{ $totalPendaftaran - $pendingPendaftaran }}</td>
            </tr>
        </table>
    </div>
</div>
@endsection