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

<!-- Tombol Tambah Postingan (sembunyikan saat mode trash) -->
@if(!$showTrash)
<div class="mb-3">
    <a href="{{ route('admin.posts.create') }}" class="btn btn-success">
        <i class="bi bi-plus-circle me-1"></i> Tambah Postingan Baru
    </a>
</div>
@endif

{{-- Tab Filter Status + Trash --}}
<div class="status-tabs mb-4">
    @if(!$showTrash)
        <a href="{{ route('admin.posts.index', array_merge(request()->except('status', 'page'), [])) }}"
           class="status-tab {{ !request('status') ? 'active' : '' }}">
            <i class="bi bi-grid me-1"></i> Semua
            <span class="tab-count">{{ $statusCounts['all'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.posts.index', array_merge(request()->except('page'), ['status' => 'publish'])) }}"
           class="status-tab status-tab-publish {{ request('status') == 'publish' ? 'active' : '' }}">
            <i class="bi bi-check-circle me-1"></i> Publish
            <span class="tab-count">{{ $statusCounts['publish'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.posts.index', array_merge(request()->except('page'), ['status' => 'draft'])) }}"
           class="status-tab status-tab-draft {{ request('status') == 'draft' ? 'active' : '' }}">
            <i class="bi bi-file-earmark me-1"></i> Draft
            <span class="tab-count">{{ $statusCounts['draft'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.posts.index', array_merge(request()->except('page'), ['status' => 'pending'])) }}"
           class="status-tab status-tab-pending {{ request('status') == 'pending' ? 'active' : '' }}">
            <i class="bi bi-hourglass-split me-1"></i> Pending
            <span class="tab-count">{{ $statusCounts['pending'] ?? 0 }}</span>
        </a>
        {{-- ← TAMBAHKAN TAB TRASH --}}
        <a href="{{ route('admin.posts.index', ['trash' => 1]) }}"
           class="status-tab status-tab-trash">
            <i class="bi bi-trash me-1"></i> Trash
            <span class="tab-count">{{ $statusCounts['trash'] ?? 0 }}</span>
        </a>
    @else
        <a href="{{ route('admin.posts.index') }}" class="status-tab">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Postingan Aktif
        </a>
    @endif
</div>

<!-- Daftar Postingan -->
<div class="admin-card">
    <div class="admin-card-header">
        <h4>
            <i class="bi bi-{{ $showTrash ? 'trash' : 'file-post' }} me-2"></i> 
            {{ $showTrash ? 'Postingan Terhapus (Trash)' : 'Daftar Postingan' }}
        </h4>
        <span class="badge bg-secondary">{{ $posts->total() }} postingan</span>
    </div>
    
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
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
                    <td>
                        @php
                            $imageFound = false;
                            $imageUrl =  asset('assets/img/default-image.jpg');
                            
                            if($post->featured_image_path) {
                                $storagePath = storage_path('app/public/' . $post->featured_image_path);
                                if(file_exists($storagePath)) {
                                    $imageFound = true;
                                    $imageUrl = asset('storage/' . $post->featured_image_path);
                                }
                                elseif(file_exists(public_path($post->featured_image_path))) {
                                    $imageFound = true;
                                    $imageUrl = asset($post->featured_image_path);
                                }
                                elseif(file_exists(public_path('img/' . basename($post->featured_image_path)))) {
                                    $imageFound = true;
                                    $imageUrl = asset('img/' . basename($post->featured_image_path));
                                }
                                elseif(file_exists(storage_path('app/public/img/' . basename($post->featured_image_path)))) {
                                    $imageFound = true;
                                    $imageUrl = asset('storage/img/' . basename($post->featured_image_path));
                                }
                            }
                        @endphp
                        
                        @if($imageFound)
                            <img src="{{ $imageUrl }}" 
                                 width="50" height="40" 
                                 class="post-thumb {{ $showTrash ? 'post-thumb-trashed' : '' }}">
                        @else
                            <img src="{{ asset('assets/img/default-image.jpg') }}" 
                                 width="50" height="40" 
                                 class="post-thumb {{ $showTrash ? 'post-thumb-trashed' : '' }}">
                        @endif
                    </td>
                    <td class="td-title">{{ Str::limit($post->title, 50) }}</td>
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
                    <td class="td-date">
                        <div class="date-human">{{ \Carbon\Carbon::parse($post->date_published)->diffForHumans() }}</div>
                        <div class="date-full">{{ \Carbon\Carbon::parse($post->date_published)->format('d/m/Y H:i') }}</div>
                        @if($showTrash && $post->deleted_at)
                            <div class="date-full date-deleted">
                                <i class="bi bi-trash"></i> Dihapus {{ \Carbon\Carbon::parse($post->deleted_at)->diffForHumans() }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="dropdown">
                            <button class="btn-action btn-dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @if($showTrash)
                                    {{-- Mode Trash: Restore & Force Delete --}}
                                    <li>
                                        <form action="{{ route('admin.posts.restore', $post->id_post) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="dropdown-item text-success" 
                                                    onclick="return confirm('Kembalikan postingan ini?')">
                                                <i class="bi bi-arrow-counterclockwise me-2"></i> Kembalikan
                                            </button>
                                        </form>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('admin.posts.force-delete', $post->id_post) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger" 
                                                    onclick="return confirm('HAPUS PERMANEN? Data tidak bisa dikembalikan!')">
                                                <i class="bi bi-trash me-2"></i> Hapus Permanen
                                            </button>
                                        </form>
                                    </li>
                                @else
                                    {{-- Mode Normal: Edit, Lihat, Hapus --}}
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.posts.edit', $post->id_post) }}">
                                            <i class="bi bi-pencil me-2"></i> Edit
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('post.show', $post->id_post) }}" target="_blank">
                                            <i class="bi bi-eye me-2"></i> Lihat
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('admin.posts.destroy', $post->id_post) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger" 
                                                    onclick="return confirm('Hapus postingan ini? Bisa di-restore dari Trash.')">
                                                <i class="bi bi-trash me-2"></i> Hapus
                                            </button>
                                        </form>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        @if($showTrash)
                            <i class="bi bi-trash" style="font-size: 48px; color: #ccc;"></i>
                            <p class="mt-2">Tidak ada postingan di trash</p>
                        @else
                            <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                            <p class="mt-2">Belum ada postingan</p>
                        @endif
                    </td>
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