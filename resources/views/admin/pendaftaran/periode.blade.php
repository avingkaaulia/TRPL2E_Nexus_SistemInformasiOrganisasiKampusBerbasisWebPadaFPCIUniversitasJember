{{-- resources/views/admin/pendaftaran/periode.blade.php --}}
@extends('layouts.admin')

@section('title', 'Kelola Periode Pendaftaran - Admin FPCI UNEJ')
@section('page-title', 'Kelola Periode Pendaftaran')

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

<!-- 🔥 INFO STATUS PENDAFTARAN -->
<div class="alert {{ ($config->is_open ?? 0) == 1 ? 'alert-info' : 'alert-warning' }} mb-4">
    <i class="bi {{ ($config->is_open ?? 0) == 1 ? 'bi-unlock' : 'bi-lock' }} me-2"></i>
    <strong>Status Pendaftaran:</strong> 
    {{ ($config->is_open ?? 0) == 1 ? '🔓 TERBUKA' : '🔒 TERTUTUP' }}
    @if(($config->is_open ?? 0) == 1)
        <small class="d-block mt-1">✅ Pendaftaran sedang terbuka. Silakan aktifkan periode yang sesuai.</small>
    @else
        <small class="d-block mt-1">⚠️ Pendaftaran sedang ditutup. Silakan buka pendaftaran di menu Konfigurasi terlebih dahulu.</small>
    @endif
</div>

<div class="mb-3">
    <a href="{{ route('admin.pendaftaran.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Pendaftaran
    </a>
    <a href="{{ route('admin.pendaftaran.config') }}" class="btn btn-primary">
        <i class="bi bi-gear me-1"></i> Konfigurasi Pendaftaran
    </a>
</div>

<div class="row">
    <!-- Form Tambah Periode -->
    <div class="col-md-5">
        <div class="admin-card">
            <div class="admin-card-header">
                <h4><i class="bi bi-plus-circle me-2"></i> Tambah Periode Baru</h4>
            </div>
            <form action="{{ route('admin.pendaftaran.periode.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>Tahun Ajaran <span class="text-danger">*</span></label>
                    <input type="text" name="tahun_ajaran" class="form-control" required placeholder="2025/2026">
                </div>
                <div class="mb-3">
                    <label>Nama Periode <span class="text-danger">*</span></label>
                    <input type="text" name="nama_periode" class="form-control" required placeholder="Gelombang 1">
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label>Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_mulai" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label>Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_selesai" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label>Kuota <span class="text-danger">*</span></label>
                    <input type="number" name="kuota" class="form-control" required value="100">
                </div>
                <div class="mb-3">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3" placeholder="Deskripsi periode pendaftaran"></textarea>
                </div>
                
                @if(($config->is_open ?? 0) == 0)
                <div class="alert alert-warning">
                    <i class="bi bi-info-circle me-2"></i>
                    Pendaftaran sedang ditutup. Periode baru akan ditambahkan dalam status tidak aktif.
                </div>
                @endif
                
                <button type="submit" class="btn btn-success w-100">
                    <i class="bi bi-save me-1"></i> Simpan Periode
                </button>
            </form>
        </div>
    </div>
    
    <!-- Daftar Periode -->
    <div class="col-md-7">
        <div class="admin-card">
            <div class="admin-card-header">
                <h4><i class="bi bi-calendar me-2"></i> Daftar Periode Pendaftaran</h4>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Periode</th>
                            <th>Tahun Ajaran</th>
                            <th>Tanggal</th>
                            <th>Kuota</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($periode as $p)
                        <tr>
                            <td>{{ $p->id_periode }}</td>
                            <td>{{ $p->nama_periode }}</td>
                            <td>{{ $p->tahun_ajaran }}</td>
                            <td>
                                {{ \Carbon\Carbon::parse($p->tanggal_mulai)->format('d/m/Y') }} - 
                                {{ \Carbon\Carbon::parse($p->tanggal_selesai)->format('d/m/Y') }}
                            </td>
                            <td>{{ $p->kuota }}</td>
                            <td>
                                @if($p->is_active)
                                    <span class="badge-status badge-publish">✅ Aktif</span>
                                @else
                                    <span class="badge-status badge-draft">❌ Tidak Aktif</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" 
                                        data-bs-target="#editModal{{ $p->id_periode }}">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <form action="{{ route('admin.pendaftaran.periode.destroy', $p->id_periode) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('Yakin hapus periode {{ $p->nama_periode }}?')">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Periode -->
@foreach($periode as $p)
<div class="modal fade" id="editModal{{ $p->id_periode }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.pendaftaran.periode.update', $p->id_periode) }}" method="POST" id="formEditPeriode{{ $p->id_periode }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Periode: {{ $p->nama_periode }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Tahun Ajaran</label>
                        <input type="text" name="tahun_ajaran" class="form-control" value="{{ $p->tahun_ajaran }}" required>
                    </div>
                    <div class="mb-3">
                        <label>Nama Periode</label>
                        <input type="text" name="nama_periode" class="form-control" value="{{ $p->nama_periode }}" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" class="form-control" value="{{ $p->tanggal_mulai }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai" class="form-control" value="{{ $p->tanggal_selesai }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Kuota</label>
                        <input type="number" name="kuota" class="form-control" value="{{ $p->kuota }}" required>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" 
                                   id="is_active_checkbox_{{ $p->id_periode }}" 
                                   {{ $p->is_active ? 'checked' : '' }}
                                   {{ ($config->is_open ?? 0) == 0 ? 'disabled' : '' }}>
                            <label class="form-check-label fw-bold" for="is_active_checkbox_{{ $p->id_periode }}">
                                Aktifkan periode ini
                            </label>
                        </div>
                        <small class="text-muted">
                            @if(($config->is_open ?? 0) == 0)
                                ⚠️ Pendaftaran sedang ditutup. Buka konfigurasi pendaftaran terlebih dahulu.
                            @else
                                Hanya satu periode yang boleh aktif dalam satu waktu
                            @endif
                        </small>
                    </div>
                    <div class="mb-3">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3">{{ $p->deskripsi }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection