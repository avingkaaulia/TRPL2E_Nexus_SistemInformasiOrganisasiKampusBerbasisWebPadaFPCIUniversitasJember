{{-- resources/views/admin/posts/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Semua Postingan - Admin FPCI UNEJ')
@section('page-title', 'Semua Postingan')

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
        <h4><i class="bi bi-funnel me-2"></i> Filter Postingan</h4>
    </div>
    <form method="GET" action="{{ route('admin.posts.index') }}" class="row g-3">
        <div class="col-md-3">
            <input type="text" name="search" class="form-control" placeholder="Cari judul..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
    <select name="category" class="form-select">
        <option value="">Semua Kategori</option>
        @foreach($categories as $cat)
        <option value="{{ $cat->id_category }}" {{ request('category') == $cat->id_category ? 'selected' : '' }}>
            {{ $cat->category_name }}
        </option>
        @endforeach
    </select>
</div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">Semua Status</option>
                @foreach($statuses as $status)
                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                    {{ ucfirst($status) }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-search me-1"></i> Filter
            </button>
        </div>
    </form>
</div>

<!-- Tombol Tambah Postingan -->
<div class="mb-3">
    <a href="{{ route('admin.posts.create') }}" class="btn btn-success">
        <i class="bi bi-plus-circle me-1"></i> Tambah Postingan Baru
    </a>
</div>

<!-- Daftar Postingan -->
<div class="admin-card">
    <div class="admin-card-header">
        <h4><i class="bi bi-file-post me-2"></i> Daftar Postingan</h4>
        <span class="badge bg-secondary">{{ $posts->total() }} postingan</span>
    </div>
    
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Gambar</th>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Penulis</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($posts as $post)
                <tr>
                    <td>{{ $post->id_post }}</td>
                    <td>
                        @php
                            $imageFound = false;
                            $imageUrl = '';
                            
                            if($post->featured_image_path) {
                                // Cek di storage
                                $storagePath = storage_path('app/public/' . $post->featured_image_path);
                                if(file_exists($storagePath)) {
                                    $imageFound = true;
                                    $imageUrl = asset('storage/' . $post->featured_image_path);
                                }
                                // Cek di public
                                elseif(file_exists(public_path($post->featured_image_path))) {
                                    $imageFound = true;
                                    $imageUrl = asset($post->featured_image_path);
                                }
                                // Cek di public/img
                                elseif(file_exists(public_path('img/' . basename($post->featured_image_path)))) {
                                    $imageFound = true;
                                    $imageUrl = asset('img/' . basename($post->featured_image_path));
                                }
                                // Cek di storage/img
                                elseif(file_exists(storage_path('app/public/img/' . basename($post->featured_image_path)))) {
                                    $imageFound = true;
                                    $imageUrl = asset('storage/img/' . basename($post->featured_image_path));
                                }
                            }
                        @endphp
                        
                        @if($imageFound)
                            <img src="{{ $imageUrl }}" 
                                 width="50" height="40" style="object-fit: cover; border-radius: 6px;">
                        @else
                            <div style="width:50px; height:40px; background:#5C6844; border-radius:6px; display:flex; align-items:center; justify-content:center; color:white;">
                                <i class="bi bi-image" style="font-size:20px;"></i>
                            </div>
                        @endif
                    </td>
                    <td>{{ Str::limit($post->title, 50) }}</td>
                    <td>{{ $post->category->category_name ?? '-' }}</td>
                    <td>{{ $post->user->nama ?? 'Admin' }}</td>
                    <td>
                        <span class="badge-status 
                            @if($post->status == 'publish') badge-publish
                            @elseif($post->status == 'draft') badge-draft
                            @else badge-pending @endif">
                            {{ $post->status }}
                        </span>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($post->date_published)->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.posts.edit', $post->id_post) }}" 
                           class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('admin.posts.destroy', $post->id_post) }}" 
                              method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" 
                                    onclick="return confirm('Hapus postingan ini?')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        <a href="{{ route('post.show', $post->id_post) }}" 
                           target="_blank" class="btn btn-sm btn-info">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">Belum ada postingan</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="pagination-wrapper">
    {{ $posts->appends(request()->query())->links('vendor.pagination.custom') }}
</div>
</div>
@endsection