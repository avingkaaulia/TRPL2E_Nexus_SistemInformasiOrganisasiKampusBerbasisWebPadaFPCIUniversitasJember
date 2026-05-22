{{-- resources/views/admin/pages/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Kelola Halaman - Admin FPCI UNEJ')
@section('page-title', 'Kelola Halaman (Pages)')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Filter -->
<div class="admin-card mb-4">
    <div class="admin-card-header">
        <h4><i class="bi bi-funnel me-2"></i> Filter Halaman</h4>
    </div>
    <form method="GET" action="{{ route('admin.pages.list') }}" class="row g-3">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control" placeholder="Cari judul halaman..." value="{{ request('search') }}">
        </div>
        <div class="col-md-4">
            <select name="status" class="form-select">
                <option value="">Semua Status</option>
                <option value="publish" {{ request('status') == 'publish' ? 'selected' : '' }}>Publish</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-search me-1"></i> Filter
            </button>
        </div>
    </form>
</div>

<!-- Tombol Tambah Halaman -->
<div class="mb-3">
    <a href="{{ route('admin.posts.create') }}?type=page" class="btn btn-success">
        <i class="bi bi-plus-circle me-1"></i> Tambah Halaman Baru
    </a>
</div>

<!-- Daftar Halaman -->
<div class="admin-card">
    <div class="admin-card-header">
        <h4><i class="bi bi-files me-2"></i> Daftar Halaman (Pages)</h4>
        <span class="badge bg-secondary">{{ $pages->total() }} halaman</span>
    </div>
    
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Gambar</th>
                    <th>Judul Halaman</th>
                    <th>Status</th>
                    <th>Tanggal Dibuat</th>
                    <th width="150">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pages as $page)
                <tr>
                    <td>{{ $page->id_post }}</td>
                    <td>
                        @php
                            $imageUrl = asset('assets/img/default-image.jpg');
                            if($page->featured_image_path) {
                                $storagePath = storage_path('app/public/' . $page->featured_image_path);
                                if(file_exists($storagePath)) {
                                    $imageUrl = asset('storage/' . $page->featured_image_path);
                                } elseif(file_exists(public_path($page->featured_image_path))) {
                                    $imageUrl = asset($page->featured_image_path);
                                }
                            }
                        @endphp
                        <img src="{{ $imageUrl }}" width="50" height="40" style="object-fit: cover; border-radius: 6px;">
                    </td>
                    <td>{{ $page->title }}</td>
                    <td>
                        <span class="badge-status {{ $page->status == 'publish' ? 'badge-publish' : 'badge-draft' }}">
                            {{ $page->status == 'publish' ? 'Publish' : 'Draft' }}
                        </span>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($page->date_published)->format('d/m/Y H:i') }}</td>
                    <td>
                        <div class="action-group">
                            <a href="{{ route('admin.posts.edit', $page->id_post) }}" class="btn-action btn-edit" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="{{ route('page.show', $page->id_post) }}" target="_blank" class="btn-action btn-view" title="Lihat">
                                <i class="bi bi-eye"></i>
                            </a>
                            <form action="{{ route('admin.posts.destroy', $page->id_post) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-delete" title="Hapus" onclick="return confirm('Hapus halaman {{ $page->title }}?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                        <p class="mt-2">Belum ada halaman (page) yang dibuat</p>
                        <a href="{{ route('admin.posts.create') }}?type=page" class="btn btn-success mt-2">
                            <i class="bi bi-plus-circle me-1"></i> Buat Halaman Pertama
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="pagination-wrapper">
        {{ $pages->appends(request()->query())->links('vendor.pagination.custom') }}
    </div>
</div>
@endsection