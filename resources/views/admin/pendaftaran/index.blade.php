{{-- resources/views/admin/pendaftaran/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Kelola Pendaftaran - Admin FPCI UNEJ')
@section('page-title', 'Kelola Pendaftaran Anggota')

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
        <h4><i class="bi bi-funnel me-2"></i> Filter Pendaftaran</h4>
    </div>
    <form method="GET" action="{{ route('admin.pendaftaran.index') }}" class="row g-3">
        <div class="col-md-3">
            <input type="text" name="search" class="form-control" placeholder="Cari nama, email, atau NIM..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Semua Status</option>
                <option value="menunggu" {{ request('status') == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                <option value="diterima" {{ request('status') == 'diterima' ? 'selected' : '' }}>Diterima</option>
                <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="periode" class="form-select">
                <option value="">Semua Periode</option>
                @foreach($periodeList as $periode)
                <option value="{{ $periode->id_periode }}" {{ request('periode') == $periode->id_periode ? 'selected' : '' }}>
                    {{ $periode->nama_periode }} ({{ $periode->tahun_ajaran }})
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-search me-1"></i> Filter
            </button>
        </div>
    </form>
</div>

<!-- Menu Tambahan -->
<div class="mb-3 d-flex gap-2 flex-wrap">
    <a href="{{ route('admin.pendaftaran.config') }}" class="btn btn-dark">
        <i class="bi bi-gear me-1"></i> Konfigurasi
    </a>
    <a href="{{ route('admin.pendaftaran.periode') }}" class="btn btn-dark">
        <i class="bi bi-calendar me-1"></i> Periode Pendaftaran
    </a>
    <a href="{{ route('admin.pendaftaran.form-fields') }}" class="btn btn-info">
        <i class="bi bi-layout-text-window me-1"></i> Form Fields
    </a>
    <a href="{{ route('admin.pendaftaran.jenis-berkas') }}" class="btn btn-info">
        <i class="bi bi-file-earmark-text me-1"></i> Jenis Berkas
    </a>
</div>

<!-- Daftar Pendaftaran -->
<div class="admin-card">
    <div class="admin-card-header">
        <h4><i class="bi bi-people me-2"></i> Daftar Pendaftar</h4>
        <span class="badge bg-secondary">{{ $pendaftaran->total() }} pendaftar</span>
    </div>
    
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>NIM</th>
                    <th>Status</th>
                    <th>Tanggal Daftar</th>
                    <th>Aksi</th>
                <tr>
            </thead>
            <tbody>
                @forelse($pendaftaran as $p)
                <tr>
                    <td>{{ $p->id_pendaftaran }}</td>
                    <td>{{ $p->nama }}</td>
                    <td>{{ $p->email }}</td>
                    <td>{{ $p->nim ?? '-' }}</td>
                    <td>
                        <span class="badge-status 
                            @if($p->status == 'menunggu') badge-menunggu
                            @elseif($p->status == 'diterima') badge-publish
                            @else badge-draft @endif">
                            {{ $p->status }}
                        </span>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($p->tanggal_daftar)->format('d/m/Y') }}</td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="{{ route('admin.pendaftaran.show', $p->id_pendaftaran) }}" 
                               class="btn btn-info" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            
                            @if($p->status == 'menunggu')
                            <form action="{{ route('admin.pendaftaran.accept', $p->id_pendaftaran) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-success" title="Terima" 
                                        onclick="return confirm('Terima pendaftar {{ $p->nama }}?')">
                                    <i class="bi bi-check-circle"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.pendaftaran.reject', $p->id_pendaftaran) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-danger" title="Tolak" 
                                        onclick="return confirm('Tolak pendaftar {{ $p->nama }}?')">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </form>
                            @endif
                            
                            <form action="{{ route('admin.pendaftaran.destroy', $p->id_pendaftaran) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" title="Hapus" 
                                        onclick="return confirm('Hapus data pendaftaran {{ $p->nama }}?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">Belum ada pendaftar</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="d-flex justify-content-center mt-4">
        {{ $pendaftaran->links() }}
    </div>
</div>
@endsection