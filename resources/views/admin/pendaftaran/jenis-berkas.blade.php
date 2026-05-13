{{-- resources/views/admin/pendaftaran/jenis-berkas.blade.php --}}
@extends('layouts.admin')

@section('title', 'Kelola Jenis Berkas - Admin FPCI UNEJ')
@section('page-title', 'Kelola Jenis Berkas Pendaftaran')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row">
    <!-- Form Tambah Jenis Berkas -->
    <div class="col-md-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h4><i class="bi bi-plus-circle me-2"></i> Tambah Jenis Berkas</h4>
            </div>
            <form action="{{ route('admin.pendaftaran.jenis-berkas.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>Nama Jenis <span class="text-danger">*</span></label>
                    <input type="text" name="nama_jenis" class="form-control" required 
                           placeholder="contoh: Surat Pernyataan">
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="is_required" value="1" class="form-check-input" id="is_required">
                        <label class="form-check-label" for="is_required">Wajib diupload</label>
                    </div>
                </div>
                <div class="mb-3">
                    <label>Format File <span class="text-danger">*</span></label>
                    <select name="file_type" class="form-select" required>
                        <option value="jpg,png">JPG, PNG</option>
                        <option value="pdf">PDF</option>
                        <option value="jpg,png,pdf">JPG, PNG, PDF</option>
                        <option value="doc,docx">DOC, DOCX</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Max Size (KB) <span class="text-danger">*</span></label>
                    <input type="number" name="max_size" class="form-control" value="2048" required>
                    <small class="text-muted">2048 KB = 2 MB</small>
                </div>
                <button type="submit" class="btn btn-success w-100">
                    <i class="bi bi-save me-1"></i> Simpan
                </button>
            </form>
        </div>
    </div>
    
    <!-- Daftar Jenis Berkas -->
    <div class="col-md-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h4><i class="bi bi-file-earmark-text me-2"></i> Daftar Jenis Berkas</h4>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Jenis</th>
                            <th>Wajib</th>
                            <th>Format</th>
                            <th>Max Size</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jenisBerkas as $jenis)
                        <tr>
                            <td>{{ $jenis->id_jenis }}</td>
                            <td>{{ $jenis->nama_jenis }}</td>
                            <td>{{ $jenis->is_required ? 'Ya' : 'Tidak' }}</td>
                            <td>{{ $jenis->file_type }}</td>
                            <td>{{ number_format($jenis->max_size / 1024, 1) }} MB</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" 
                                        data-bs-target="#editModal{{ $jenis->id_jenis }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('admin.pendaftaran.jenis-berkas.destroy', $jenis->id_jenis) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('Hapus jenis berkas ini?')">
                                        <i class="bi bi-trash"></i>
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

<!-- Modal Edit -->
@foreach($jenisBerkas as $jenis)
<div class="modal fade" id="editModal{{ $jenis->id_jenis }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.pendaftaran.jenis-berkas.update', $jenis->id_jenis) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Jenis Berkas: {{ $jenis->nama_jenis }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nama Jenis</label>
                        <input type="text" name="nama_jenis" class="form-control" value="{{ $jenis->nama_jenis }}" required>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_required" value="1" class="form-check-input" 
                                   id="is_required{{ $jenis->id_jenis }}" {{ $jenis->is_required ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_required{{ $jenis->id_jenis }}">Wajib diupload</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Format File</label>
                        <select name="file_type" class="form-select">
                            <option value="jpg,png" {{ $jenis->file_type == 'jpg,png' ? 'selected' : '' }}>JPG, PNG</option>
                            <option value="pdf" {{ $jenis->file_type == 'pdf' ? 'selected' : '' }}>PDF</option>
                            <option value="jpg,png,pdf" {{ $jenis->file_type == 'jpg,png,pdf' ? 'selected' : '' }}>JPG, PNG, PDF</option>
                            <option value="doc,docx" {{ $jenis->file_type == 'doc,docx' ? 'selected' : '' }}>DOC, DOCX</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Max Size (KB)</label>
                        <input type="number" name="max_size" class="form-control" value="{{ $jenis->max_size }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection