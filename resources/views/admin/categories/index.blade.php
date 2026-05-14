{{-- resources/views/admin/categories/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Kelola Kategori - Admin FPCI UNEJ')
@section('page-title', 'Kelola Kategori')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row">
    <!-- Form Tambah Kategori -->
    <div class="col-md-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h4><i class="bi bi-plus-circle me-2"></i> Tambah Kategori Baru</h4>
            </div>
            <div class="card-body p-3">
                <form action="{{ route('admin.categories.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="category_name" class="form-control @error('category_name') is-invalid @enderror" 
                               value="{{ old('category_name') }}" required placeholder="Contoh: Berita Terbaru">
                        @error('category_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Parent Kategori</label>
                        <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                            <option value="">-- Tanpa Parent (Kategori Utama) --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id_category }}">
                                    {{ $cat->category_name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Pilih parent jika ingin membuat sub-kategori</small>
                        @error('parent_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-save me-1"></i> Simpan Kategori
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Daftar Kategori -->
    <div class="col-md-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h4><i class="bi bi-tags me-2"></i> Daftar Kategori</h4>
                <span class="badge bg-secondary">{{ $categories->count() }} kategori</span>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th width="50">ID</th>
                            <th>Nama Kategori</th>
                            <th>Parent</th>
                            <th>Sub Kategori</th>
                            <th>Jumlah Post</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $cat)
                        @php
                            $childCount = $categories->where('parent_id', $cat->id_category)->count();
                            $postCount = DB::table('posts')->where('id_post_category', $cat->id_category)->count();
                        @endphp
                        <tr>
                            <td>{{ $cat->id_category }}</td>
                            <td>
                                @if($cat->parent_id)
                                    <span style="margin-left: 20px;">↳</span>
                                @endif
                                <strong>{{ $cat->category_name }}</strong>
                                @if($cat->parent_id)
                                    <small class="text-muted">(sub)</small>
                                @endif
                            </td>
                            <td>
                                @if($cat->parent)
                                    <span class="badge-parent">{{ $cat->parent->category_name }}</span>
                                @else
                                    <span class="badge-parent">Kategori Utama</span>
                                @endif
                            </td>
                            <td>
                                @if($childCount > 0)
                                    <span class="badge bg-info">{{ $childCount }} sub</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $postCount }} postingan</span>
                            </td>
                            <td>
                                <div class="action-group">
                                    <a href="{{ route('admin.categories.edit', $cat->id_category) }}" 
                                       class="btn-action btn-edit" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.categories.destroy', $cat->id_category) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-delete" 
                                                onclick="return confirm('Hapus kategori {{ $cat->category_name }}?')"
                                                title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-inbox" style="font-size: 32px; color: #ccc;"></i>
                                <p class="mt-2 text-muted">Belum ada kategori</p>
                             </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="admin-card">
            <div class="admin-card-header">
                <h4><i class="bi bi-diagram-3 me-2"></i> Struktur Hirarki Kategori</h4>
            </div>
            <div class="card-body p-3">
                <div class="category-tree">
                    @foreach($categories->where('parent_id', null) as $root)
                        <div class="tree-item">
                            <div class="tree-root">
                                <i class="bi bi-folder-fill text-warning me-2"></i>
                                <strong>{{ $root->category_name }}</strong>
                                <span class="badge bg-secondary ms-2">{{ DB::table('posts')->where('id_post_category', $root->id_category)->count() }} post</span>
                            </div>
                            @include('admin.categories.partials.tree-children', ['children' => $categories->where('parent_id', $root->id_category), 'level' => 1])
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.category-tree {
    padding: 10px;
}
.tree-item {
    margin-bottom: 10px;
}
.tree-root {
    padding: 10px;
    background: #F5F2E8;
    border-radius: 8px;
    margin-bottom: 5px;
}
.tree-children {
    margin-left: 30px;
    padding-left: 15px;
    border-left: 2px dashed #5C6844;
}
.tree-child {
    padding: 8px 12px;
    margin: 5px 0;
    background: white;
    border-radius: 8px;
    border: 1px solid #E8E4D9;
}
.tree-child:hover {
    background: #F5F2E8;
}
.badge-parent {
    background: #eef3ea;
    color: #5C6844;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
}
</style>
@endpush