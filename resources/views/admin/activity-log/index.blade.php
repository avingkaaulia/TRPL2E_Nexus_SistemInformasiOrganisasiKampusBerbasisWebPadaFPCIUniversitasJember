{{-- resources/views/admin/activity-log/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Log Aktivitas - Admin FPCI UNEJ')
@section('page-title', 'Histori / Log Aktivitas')

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Statistik --}}
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-info">
                <h2>{{ $totalLogs }}</h2>
                <p>Total Aktivitas</p>
            </div>
            <div class="stat-icon">
                <i class="bi bi-clock-history"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-info">
                <h2>{{ $todayLogs }}</h2>
                <p>Aktivitas Hari Ini</p>
            </div>
            <div class="stat-icon">
                <i class="bi bi-calendar-check"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-info">
                <h2>{{ $activeUsers }}</h2>
                <p>User Aktif Hari Ini</p>
            </div>
            <div class="stat-icon">
                <i class="bi bi-people"></i>
            </div>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="admin-card mb-4">
    <div class="admin-card-header">
        <h4><i class="bi bi-funnel me-2"></i> Filter Log</h4>
    </div>
    <form method="GET" action="{{ route('admin.activity-log.index') }}" class="row g-3">
        <div class="col-md-3">
            <label class="form-label small">User</label>
            <select name="user_id" class="form-select">
                <option value="">Semua User</option>
                @foreach($users as $u)
                    <option value="{{ $u->id_user }}" {{ request('user_id') == $u->id_user ? 'selected' : '' }}>
                        {{ $u->nama }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">Jenis Aksi</label>
            <select name="action" class="form-select">
                <option value="">Semua Aksi</option>
                @foreach($actions as $a)
                    <option value="{{ $a }}" {{ request('action') == $a ? 'selected' : '' }}>
                        {{ ucfirst($a) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">Dari Tanggal</label>
            <input type="date" name="dari" class="form-control" value="{{ request('dari') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small">Sampai Tanggal</label>
            <input type="date" name="sampai" class="form-control" value="{{ request('sampai') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small">Cari</label>
            <input type="text" name="cari" class="form-control" placeholder="Kata kunci..." value="{{ request('cari') }}">
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-search"></i>
            </button>
        </div>
    </form>
</div>

{{-- Tabel Log --}}
<div class="admin-card">
    <div class="admin-card-header">
        <h4><i class="bi bi-list-ul me-2"></i> Daftar Aktivitas</h4>
        <div class="d-flex gap-2">
            <span class="badge bg-secondary">{{ $logs->total() }} log</span>
            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#clearModal">
                <i class="bi bi-trash"></i> Bersihkan Log Lama
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th width="130">Waktu</th>
                    <th width="150">User</th>
                    <th width="100">Aksi</th>
                    <th>Deskripsi</th>
                    <th width="120">IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td class="td-date">
                        <div class="date-human">{{ $log->created_at->diffForHumans() }}</div>
                        <div class="date-full">{{ $log->created_at->format('d/m/Y H:i') }}</div>
                    </td>
                    <td class="td-title">
                        <strong>{{ $log->user_name ?? 'Guest' }}</strong>
                    </td>
                    <td>
                        <span class="badge bg-{{ $log->action_color }}">{{ $log->action_label }}</span>
                    </td>
                    <td class="td-desc">{{ $log->description }}</td>
                    <td>
                        <code style="font-size: 11px;">{{ $log->ip_address }}</code>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                        <p class="mt-2">Belum ada log aktivitas</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        {{ $logs->appends(request()->query())->links('vendor.pagination.custom') }}
    </div>
</div>

{{-- Modal Clear Log --}}
<div class="modal fade" id="clearModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.activity-log.clear') }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Bersihkan Log Lama</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Log yang dihapus tidak bisa dikembalikan!
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hapus log lebih dari berapa hari?</label>
                        <input type="number" name="hari" class="form-control" value="30" min="1" required>
                        <small class="text-muted">Contoh: 30 = hapus log yang umurnya lebih dari 30 hari</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i> Hapus Log Lama
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection