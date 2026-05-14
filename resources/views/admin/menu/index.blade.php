{{-- resources/views/admin/menu/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Kelola Menu - Admin FPCI UNEJ')
@section('page-title', 'Kelola Menu Navigasi')

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

<div class="page-card">
    <div class="page-card-header">
        <h4><i class="bi bi-list me-2"></i> Daftar Menu Navigasi</h4>
        <div>
            <span class="badge bg-secondary me-2">Total Menu: {{ $menus->count() }}</span>
            <a href="{{ route('admin.menu.create') }}" class="btn-add">
                <i class="bi bi-plus-circle me-1"></i> Tambah Menu
            </a>
        </div>
    </div>
    
    <div class="admin-table-wrapper">
        <table class="admin-table-menu">
            <thead>
                <tr>
                    <th width="50">ID</th>
                    <th>Nama Menu</th>
                    <th>Parent</th>
                    <th>Link</th>
                    <th width="100">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($menus as $menu)
                <tr>
                    <td>{{ $menu->id_menu }}</td>
                    <td>
                        @if($menu->id_menu_parent > 0)
                            <span class="menu-level-1">{{ $menu->menu_label }}</span>
                        @else
                            <strong>{{ $menu->menu_label }}</strong>
                        @endif
                    </td>
                    <td>
                        @if($menu->parent)
                            <span class="badge-parent">
                                {{ $menu->parent->menu_label }}
                            </span>
                        @else
                            <span class="badge-parent">
                                Main Menu
                            </span>
                        @endif
                    </td>
                    <td>
                        <code style="font-size: 11px;">{{ $menu->link }}</code>
                    </td>
                    <td>
                        <div class="action-group">
                            <a href="{{ route('admin.menu.edit', $menu->id_menu) }}" 
                               class="btn-action btn-edit" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.menu.destroy', $menu->id_menu) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-delete" 
                                        onclick="return confirm('Hapus menu {{ $menu->menu_label }}?')"
                                        title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 32px; color: #ccc;"></i>
                        <p class="mt-2 text-muted">Belum ada menu</p>
                        <a href="{{ route('admin.menu.create') }}" class="btn-add mt-2">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Menu Pertama
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection