{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.admin')

@section('title', 'Dashboard - Admin FPCI UNEJ')
@section('page-title', 'Dashboard')

@section('content')
<!-- STATS CARDS -->
<div class="stats-row">
    <a href="{{ route('admin.posts.index') }}" class="stat-card text-decoration-none">
        <div class="stat-info">
            <h2>{{ $totalPosts }}</h2>
            <p>Total Postingan</p>
        </div>
        <div class="stat-icon">
            <i class="bi bi-file-post"></i>
        </div>
    </a>
    <a href="{{ route('admin.writings.pending') }}" class="stat-card text-decoration-none">
        <div class="stat-info">
            <h2>{{ $totalKarya }}</h2>
            <p>Total Karya</p>
        </div>
        <div class="stat-icon">
            <i class="bi bi-book"></i>
        </div>
    </a>
    <a href="{{ route('admin.anggota.index') }}" class="stat-card text-decoration-none">
        <div class="stat-info">
            <h2>{{ $totalAnggota }}</h2>
            <p>Total Anggota</p>
        </div>
        <div class="stat-icon">
            <i class="bi bi-people"></i>
        </div>
    </a>
    <a href="{{ route('admin.comments.index') }}" class="stat-card text-decoration-none">
        <div class="stat-info">
            <h2>{{ $totalComments }}</h2>
            <p>Total Komentar</p>
        </div>
        <div class="stat-icon">
            <i class="bi bi-chat-dots"></i>
        </div>
    </a>
</div>

<!-- TWO COLUMNS -->
<div class="two-columns">
    <!-- Recent Posts -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h4><i class="bi bi-clock-history me-2"></i> Postingan Terbaru</h4>
            <a href="{{ route('admin.posts.index') }}">Lihat Semua →</a>
        </div>
        <table class="admin-table">
            @forelse($recentPosts as $post)
            <tr>
                <td><a href="{{ route('admin.posts.edit', $post->id_post) }}" class="text-decoration-none">{{ Str::limit($post->title, 40) }}</a></td>
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
            <tr><td colspan="2" class="text-center">Belum ada postingan</td></tr>
            @endforelse
        </table>
    </div>

    <!-- Recent Comments -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h4><i class="bi bi-chat me-2"></i> Komentar Terbaru</h4>
            <a href="{{ route('admin.comments.index') }}">Lihat Semua →</a>
        </div>
        <table class="admin-table">
            @forelse($recentComments as $comment)
            <tr>
                <td>{{ Str::limit($comment->isi_komentar, 35) }}</td>
                <td><small>{{ $comment->nama_pengunjung }}</small></td>
            </tr>
            @empty
            <tr><td colspan="2" class="text-center">Belum ada komentar</td></tr>
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
            <a href="{{ route('admin.categories.index') }}">Kelola →</a>
        </div>
        @forelse($categoryStats as $cat)
        <div class="category-item">
            <a href="{{ route('admin.posts.index') }}?category={{ $cat->id_category }}" class="text-decoration-none category-name">
                {{ $cat->category_name }}
            </a>
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
            <a href="{{ route('admin.pendaftaran.index') }}">Kelola →</a>
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
        <div class="mt-3">
            <a href="{{ route('admin.pendaftaran.index') }}?status=menunggu" class="btn btn-sm btn-warning">Lihat Menunggu</a>
            <a href="{{ route('admin.pendaftaran.periode') }}" class="btn btn-sm btn-info">Atur Periode</a>
        </div>
    </div>
</div>

<!-- USER STATS (Tambahan) -->
<div class="admin-card mt-4">
    <div class="admin-card-header">
        <h4><i class="bi bi-people me-2"></i> Statistik User</h4>
        <a href="{{ route('admin.users.index') }}">Kelola User →</a>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="stat-small">
                <div class="stat-small-icon">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <div class="stat-small-info">
                    <h3>{{ $totalAdmin }}</h3>
                    <p>Admin</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-small">
                <div class="stat-small-icon">
                    <i class="bi bi-person"></i>
                </div>
                <div class="stat-small-info">
                    <h3>{{ $totalAnggotaUser }}</h3>
                    <p>Anggota (User)</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-small">
                <div class="stat-small-icon">
                    <i class="bi bi-person-plus"></i>
                </div>
                <div class="stat-small-info">
                    <h3>{{ $totalPendaftaran }}</h3>
                    <p>Calon Anggota</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection