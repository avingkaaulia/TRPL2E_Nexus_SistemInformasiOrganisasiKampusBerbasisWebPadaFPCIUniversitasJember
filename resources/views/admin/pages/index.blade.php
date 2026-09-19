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
                    <th>Gambar</th>
                    <th>Judul Halaman</th>
                    <th>Status</th>
                    <th>Tanggal Dibuat</th>
                    <th width="100">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pages as $page)
                <tr>
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
                    <td class="td-title">{{ $page->title }}</td>
                    <td>
                        <span class="badge-status {{ $page->status == 'publish' ? 'badge-publish' : 'badge-draft' }}">
                            {{ $page->status == 'publish' ? 'Publish' : 'Draft' }}
                        </span>
                    </td>
                    <td class="td-date">
                        <div class="date-human">{{ \Carbon\Carbon::parse($page->date_published)->diffForHumans() }}</div>
                        <div class="date-full">{{ \Carbon\Carbon::parse($page->date_published)->format('d/m/Y H:i') }}</div>
                    </td>
                    <td>
                        <div class="dropdown">
                            <button class="btn-action btn-dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.posts.edit', $page->id_post) }}">
                                        <i class="bi bi-pencil me-2"></i> Edit
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('page.show', $page->id_post) }}" target="_blank">
                                        <i class="bi bi-eye me-2"></i> Lihat
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('admin.posts.destroy', $page->id_post) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger" 
                                                onclick="return confirm('Hapus halaman {{ $page->title }}?')">
                                            <i class="bi bi-trash me-2"></i> Hapus
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5">
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