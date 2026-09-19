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
                    <th>Nama Menu</th>
                    <th>Parent</th>
                    <th>Link</th>
                    <th width="80">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($menus as $menu)
                <tr>
                    <td class="td-title">
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
                    <td class="td-link">
                        <code style="font-size: 11px;">{{ $menu->link }}</code>
                    </td>
                    <td class="td-action">
                        <div class="dropdown">
                            <button class="btn-action btn-dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.menu.edit', $menu->id_menu) }}">
                                        <i class="bi bi-pencil me-2"></i> Edit
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('admin.menu.destroy', $menu->id_menu) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger" 
                                                onclick="return confirm('Hapus menu {{ $menu->menu_label }}?')">
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
                    <td colspan="4" class="text-center py-5">
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