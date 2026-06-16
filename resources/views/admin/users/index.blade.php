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
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Tanggal Daftar</th>
                    <th width="200">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>{{ $user->id_user }}</td>
                    <td>{{ $user->nama }}</td>
                    <td>{{ $user->username }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="badge-status {{ $user->id_role == 1 ? 'badge-publish' : 'badge-pending' }}">
                            {{ $user->role->nama_role ?? 'Unknown' }}
                        </span>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($user->tanggal_daftar)->format('d M Y') }}</td>
                    <td>
                        <div class="action-group">
                            <a href="{{ route('admin.users.edit', $user->id_user) }}" class="btn-action btn-edit" title="Edit User">
                                <i class="bi bi-pencil"></i>
                            </a>
                            
                            <!-- Dropdown untuk ubah role cepat -->
<div class="dropdown d-inline">
    <button class="btn-action btn-role" type="button" data-bs-toggle="dropdown" title="Ubah Role">
        <i class="bi bi-shield"></i>
    </button>
    <ul class="dropdown-menu">
        @foreach($roles as $role)
        <li>
            {{-- 🔥 METHOD POST --}}
            <form action="{{ route('admin.users.update-role', $user->id_user) }}" method="POST" style="display:inline;">
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
    </ul>
</div>
                            
                            @if($user->id_user != 1 && $user->id_user != Auth::id())
                            <form action="{{ route('admin.users.destroy', $user->id_user) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-delete" title="Hapus User" onclick="return confirm('Hapus user {{ $user->nama }}?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
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

@push('scripts')
<script>
document.querySelectorAll('.role-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Cegah reload
        
        const formData = new FormData(this);
        const action = this.action;
        const method = this.querySelector('input[name="_method"]').value || 'POST';
        
        fetch(action, {
            method: method,
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Tampilkan alert sukses
                showAlert('success', data.message);
                // Reload halaman setelah 1 detik biar role berubah
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('danger', data.message || 'Gagal mengubah role');
            }
        })
        .catch(error => {
            showAlert('danger', 'Terjadi kesalahan: ' + error.message);
        });
    });
});

function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Hapus alert lama
    document.querySelectorAll('.alert').forEach(el => el.remove());
    
    // Tambah alert baru di atas tabel
    const container = document.querySelector('.admin-card .table-responsive');
    if (container) {
        container.insertAdjacentHTML('beforebegin', alertHtml);
    }
}
</script>
@endpush

@push('styles')
<style>
.btn-role {
    background: #17a2b8;
    color: white;
}
.btn-role:hover {
    background: #138496;
}
.dropdown-item.active {
    background: #5C6844;
    color: white;
}
.dropdown-item.active i {
    color: white;
}
</style>
@endpush
@endsection