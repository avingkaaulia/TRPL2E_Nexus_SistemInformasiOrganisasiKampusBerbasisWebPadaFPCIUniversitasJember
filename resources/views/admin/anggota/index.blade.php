{{-- resources/views/admin/anggota/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Kelola Anggota - Admin FPCI UNEJ')
@section('page-title', 'Kelola Anggota')

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

<!-- 🔥 KONVERSI PENDAFTAR YANG DITERIMA DAN BELUM ADA DI USERS -->
@if($pendaftaranDiterima->count() > 0)
<div class="admin-card mb-4">
    <div class="admin-card-header">
        <h4><i class="bi bi-arrow-repeat me-2"></i> Konversi Pendaftar ke Anggota</h4>
    </div>
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>
        Berikut adalah pendaftar yang sudah DITERIMA dan siap dikonversi menjadi anggota.
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Jurusan</th>
                    <th>Tanggal Daftar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendaftaranDiterima as $p)
                <tr>
                    <td>{{ $p->nama }}</td>
                    <td>{{ $p->email }}</td>
                    <td>{{ $p->jurusan ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($p->tanggal_daftar)->format('d/m/Y') }}</td>
                    <td>
                        <form action="{{ route('admin.anggota.convert', $p->id_pendaftaran) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm" 
                                    onclick="return confirm('Konversi {{ $p->nama }} menjadi anggota?')">
                                <i class="bi bi-check-circle me-1"></i> Konversi ke Anggota
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
<div class="alert alert-secondary mb-4">
    <i class="bi bi-info-circle me-2"></i>
    Tidak ada pendaftar yang siap dikonversi. Pastikan ada pendaftar dengan status "Diterima".
</div>
@endif

<!-- Filter -->
<div class="admin-card mb-4">
    <div class="admin-card-header">
        <h4><i class="bi bi-funnel me-2"></i> Filter Anggota</h4>
    </div>
    <form method="GET" action="{{ route('admin.anggota.index') }}" class="row g-3">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Cari nama, email, atau username..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="divisi" class="form-select">
                <option value="">Semua Divisi</option>
                @foreach($divisiList as $divisi)
                <option value="{{ $divisi->id_divisi }}" {{ request('divisi') == $divisi->id_divisi ? 'selected' : '' }}>
                    {{ $divisi->nama_divisi }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="periode" class="form-select">
                <option value="">Semua Periode</option>
                @foreach($periodeList as $periode)
                <option value="{{ $periode }}" {{ request('periode') == $periode ? 'selected' : '' }}>
                    {{ $periode }}
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

<!-- 🔥 TOMBOL TAMBAH ANGGOTA DIHAPUS - ANGGOTA HANYA DARI KONVERSI PENDAFTARAN -->

<!-- Daftar Anggota -->
<div class="admin-card">
    <div class="admin-card-header">
        <h4><i class="bi bi-people me-2"></i> Daftar Anggota</h4>
        <span class="badge bg-secondary">{{ $anggota->total() }} anggota</span>
    </div>
    
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>No Urut</th>
                    <th>Foto</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Divisi</th>
                    <th>Jabatan</th>
                    <th>Periode</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($anggota as $a)
                <tr>
                    <td>{{ $a->no_urut }}</td>
                    <td>
                        @if($a->foto)
                            <img src="{{ asset($a->foto) }}" class="rounded-circle" width="40" height="40" style="object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width:40px; height:40px; color:white;">
                                <i class="bi bi-person"></i>
                            </div>
                        @endif
                    </td>
                    <td>{{ $a->user->nama ?? '-' }}</td>
                    <td>{{ $a->user->email ?? '-' }}</td>
                    <td>{{ $a->divisi->nama_divisi ?? '-' }}</td>
                    <td>{{ $a->jabatan }}</td>
                    <td>{{ $a->periode }}</td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="{{ route('admin.anggota.show', $a->id_anggota) }}" class="btn btn-info" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.anggota.edit', $a->id_anggota) }}" class="btn btn-warning" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.anggota.destroy', $a->id_anggota) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" title="Hapus" onclick="return confirm('Hapus anggota {{ $a->user->nama }}?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">Belum ada anggota</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="pagination-wrapper">
        {{ $anggota->appends(request()->query())->links('vendor.pagination.custom') }}
    </div>
</div>
@endsection