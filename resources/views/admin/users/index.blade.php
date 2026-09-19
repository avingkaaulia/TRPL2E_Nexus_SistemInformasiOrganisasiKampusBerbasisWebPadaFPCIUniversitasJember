{{-- resources/views/admin/users/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Kelola User - Admin FPCI UNEJ')
@section('page-title', 'Kelola User & Role')

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

<!-- Filter -->
<div class="admin-card mb-4">
    <div class="admin-card-header">
        <h4><i class="bi bi-funnel me-2"></i> Filter User</h4>
    </div>
    <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Cari nama, email, atau username..." value="{{ request('search') }}">
        </div>
        <div class="col-md-4">
            <select name="role" class="form-select">
                <option value="">Semua Role</option>
                @foreach($roles as $role)
                <option value="{{ $role->id_role }}" {{ request('role') == $role->id_role ? 'selected' : '' }}>
                    {{ $role->nama_role }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-search me-1"></i> Filter
            </button>
        </div>
    </form>
</div>

<!-- Daftar User -->
<div class="admin-card">
    <div class="admin-card-header">
        <h4><i class="bi bi-people me-2"></i> Daftar User</h4>
        <span class="badge bg-secondary">{{ $users->total() }} user</span>
    </div>
    
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Tanggal Daftar</th>
                    <th width="80">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td class="td-title">{{ $user->nama }}</td>
                    <td>{{ $user->username }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="badge-status {{ $user->id_role == 1 ? 'badge-publish' : 'badge-pending' }}">
                            {{ $user->role->nama_role ?? 'Unknown' }}
                        </span>
                    </td>
                    <td class="td-date">
                        <div class="date-human">{{ \Carbon\Carbon::parse($user->tanggal_daftar)->diffForHumans() }}</div>
                        <div class="date-full">{{ \Carbon\Carbon::parse($user->tanggal_daftar)->format('d M Y H:i') }}</div>
                    </td>
                    <td class="td-action">
                        <div class="dropdown">
                            <button class="btn-action btn-dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.users.edit', $user->id_user) }}">
                                        <i class="bi bi-pencil me-2"></i> Edit User
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li class="dropdown-header" style="font-size: 11px; color: #999;">
                                    <i class="bi bi-shield me-1"></i> UBAH ROLE
                                </li>
                                @foreach($roles as $role)
                                <li>
                                    <form action="{{ route('admin.users.update-role', $user->id_user) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="id_role" value="{{ $role->id_role }}">
                                        <button type="submit" class="dropdown-item {{ $user->id_role == $role->id_role ? 'active' : '' }}">
                                            <i class="bi {{ $role->id_role == 1 ? 'bi-shield-lock' : 'bi-person' }} me-2"></i>
                                            {{ $role->nama_role }}
                                            @if($user->id_role == $role->id_role)
                                                <i class="bi bi-check ms-2"></i>
                                            @endif
                                        </button>
                                    </form>
                                </li>
                                @endforeach
                                @if($user->id_user != 1 && $user->id_user != Auth::id())
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('admin.users.destroy', $user->id_user) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Hapus user {{ $user->nama }}?')">
                                            <i class="bi bi-trash me-2"></i> Hapus User
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
                    <td colspan="6" class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                        <p class="mt-2">Belum ada user</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="pagination-wrapper">
        {{ $users->appends(request()->query())->links('vendor.pagination.custom') }}
    </div>
</div>

@push('styles')
<style>
.dropdown-item.active {
    background: #5C6844;
    color: white;
}
.dropdown-item.active i {
    color: white;
}
.dropdown-header {
    padding: 8px 18px 4px;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
}
</style>
@endpush
@endsection