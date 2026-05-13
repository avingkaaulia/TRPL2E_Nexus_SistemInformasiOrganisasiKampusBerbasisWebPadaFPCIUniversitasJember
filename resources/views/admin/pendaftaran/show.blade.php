{{-- resources/views/admin/pendaftaran/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Detail Pendaftaran - Admin FPCI UNEJ')
@section('page-title', 'Detail Pendaftaran')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="admin-card">
    <div class="admin-card-header">
        <h4><i class="bi bi-person-badge me-2"></i> Data Pendaftar</h4>
        <a href="{{ route('admin.pendaftaran.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <table class="table table-bordered">
                @foreach($formFields as $field)
                <tr>
                    <th width="35%">{{ $field->field_label }}</th>
                    <td>
                        @php
                            $value = $pendaftaran->{$field->field_name} ?? '-';
                            if($field->field_type == 'textarea') {
                                $value = nl2br(e($value));
                            }
                        @endphp
                        {!! $value !!}
                    </td>
                </tr>
                @endforeach
                <tr>
                    <th>Periode Pendaftaran</th>
                    <td>{{ $pendaftaran->periode->nama_periode ?? '-' }} ({{ $pendaftaran->periode->tahun_ajaran ?? '-' }})</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        <span class="badge-status 
                            @if($pendaftaran->status == 'menunggu') badge-menunggu
                            @elseif($pendaftaran->status == 'diterima') badge-publish
                            @else badge-draft @endif">
                            {{ $pendaftaran->status }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Tanggal Daftar</th>
                    <td>{{ \Carbon\Carbon::parse($pendaftaran->tanggal_daftar)->format('d F Y H:i') }}</td>
                </tr>
            </table>
            
            <!-- 🔥 TOMBOL AKSI DITERIMA & TOLAK 🔥 -->
            <div class="mt-4">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i> 
                    <strong>Update Status Pendaftar:</strong>
                </div>
                
                <div class="d-flex gap-3">
                    <form action="{{ route('admin.pendaftaran.accept', $pendaftaran->id_pendaftaran) }}" 
                          method="POST" class="d-inline" 
                          onsubmit="return confirm('Terima pendaftar {{ $pendaftaran->nama }}?')">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-success btn-lg px-4">
                            <i class="bi bi-check-circle me-2"></i> Diterima
                        </button>
                    </form>
                    
                    <form action="{{ route('admin.pendaftaran.reject', $pendaftaran->id_pendaftaran) }}" 
                          method="POST" class="d-inline"
                          onsubmit="return confirm('Tolak pendaftar {{ $pendaftaran->nama }}?')">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-danger btn-lg px-4">
                            <i class="bi bi-x-circle me-2"></i> Ditolak
                        </button>
                    </form>
                    
                    <a href="{{ route('admin.pendaftaran.index') }}" class="btn btn-secondary btn-lg px-4">
                        <i class="bi bi-arrow-left me-2"></i> Kembali
                    </a>
                </div>
                
                @if($pendaftaran->status == 'menunggu')
                    <div class="mt-3 text-muted">
                        <i class="bi bi-hourglass-split me-1"></i> 
                        Pendaftar ini masih menunggu konfirmasi. Silahkan pilih Diterima atau Ditolak.
                    </div>
                @elseif($pendaftaran->status == 'diterima')
                    <div class="mt-3 text-success">
                        <i class="bi bi-check-circle-fill me-1"></i> 
                        Pendaftar ini sudah DITERIMA.
                    </div>
                @elseif($pendaftaran->status == 'ditolak')
                    <div class="mt-3 text-danger">
                        <i class="bi bi-x-circle-fill me-1"></i> 
                        Pendaftar ini sudah DITOLAK.
                    </div>
                @endif
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-file-earmark-text me-2"></i> Berkas Pendaftaran
                </div>
                <div class="card-body">
                    @forelse($jenisBerkas as $berkas)
                        @php
                            $uploaded = $pendaftaran->berkas->where('id_jenis', $berkas->id_jenis)->first();
                        @endphp
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ $berkas->nama_jenis }}</label>
                            <div>
                                @if($uploaded)
                                    <a href="{{ route('admin.pendaftaran.download-berkas', $uploaded->id_berkas) }}" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-download me-1"></i> Download
                                    </a>
                                    <span class="text-success ms-2">
                                        <i class="bi bi-check-circle"></i> Terupload
                                    </span>
                                @else
                                    <span class="text-muted">
                                        <i class="bi bi-x-circle"></i> Belum diupload
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-muted">Tidak ada jenis berkas</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection