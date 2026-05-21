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

<div class="admin-card">
    <div class="admin-card-header">
        <h4><i class="bi bi-files me-2"></i> Daftar Halaman (Pages)</h4>
        <a href="{{ route('admin.posts.create') }}" class="btn btn-success btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Tambah Halaman Baru
        </a>
    </div>
    
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Judul Halaman</th>
                    <th>Status</th>
                    <th>Tanggal Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pages as $page)
                <tr>
                    <td>{{ $page->id_post }}</td>
                    <td>{{ $page->title }}</td>
                    <td>
                        <span class="badge-status {{ $page->status == 'publish' ? 'badge-publish' : 'badge-draft' }}">
                            {{ $page->status == 'publish' ? 'Published' : 'Draft' }}
                        </span>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($page->date_published)->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.posts.edit', $page->id_post) }}" class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <a href="{{ route('page.show', $page->id_post) }}" target="_blank" class="btn btn-sm btn-info">
                            <i class="bi bi-eye"></i> Lihat
                        </a>
                        <form action="{{ route('admin.posts.destroy', $page->id_post) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus halaman {{ $page->title }}?')">
                                <i class="bi bi-trash"></i> Hapus
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">Belum ada halaman (page) yang dibuat</td>
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