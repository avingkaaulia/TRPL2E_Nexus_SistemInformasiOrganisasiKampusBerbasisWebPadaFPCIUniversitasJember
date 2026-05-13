{{-- resources/views/admin/pendaftaran/form-fields.blade.php --}}
@extends('layouts.admin')

@section('title', 'Kelola Form Fields - Admin FPCI UNEJ')
@section('page-title', 'Kelola Form Fields Pendaftaran')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="mb-3">
    <a href="{{ route('admin.pendaftaran.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Pendaftaran
    </a>
</div>

<div class="row">
    <!-- Form Tambah Field -->
    <div class="col-md-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h4><i class="bi bi-plus-circle me-2"></i> Tambah Field Baru</h4>
            </div>
            <form action="{{ route('admin.pendaftaran.form-fields.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>Nama Field <span class="text-danger">*</span></label>
                    <input type="text" name="field_name" class="form-control" required 
                           placeholder="contoh: nama_ibu, twitter">
                    <small class="text-muted">Hanya huruf kecil dan underscore</small>
                </div>
                <div class="mb-3">
                    <label>Label Field <span class="text-danger">*</span></label>
                    <input type="text" name="field_label" class="form-control" required 
                           placeholder="contoh: Nama Ibu">
                </div>
                <div class="mb-3">
                    <label>Tipe Field <span class="text-danger">*</span></label>
                    <select name="field_type" class="form-select" required>
                        <option value="text">Text</option>
                        <option value="email">Email</option>
                        <option value="tel">Telepon</option>
                        <option value="textarea">Textarea</option>
                        <option value="number">Number</option>
                        <option value="date">Date</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Placeholder</label>
                    <input type="text" name="placeholder" class="form-control" placeholder="Contoh: Masukkan nama ibu">
                </div>
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input type="checkbox" name="is_required" value="1" class="form-check-input" id="is_required_new">
                        <label class="form-check-label" for="is_required_new">Wajib diisi</label>
                    </div>
                </div>
                <div class="mb-3">
                    <label>Urutan</label>
                    <input type="number" name="sort_order" class="form-control" value="0">
                </div>
                <button type="submit" class="btn btn-success w-100">
                    <i class="bi bi-save me-1"></i> Simpan Field
                </button>
            </form>
        </div>
    </div>
    
    <!-- Daftar Field -->
    <div class="col-md-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h4><i class="bi bi-layout-text-window me-2"></i> Daftar Form Fields</h4>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Field</th>
                            <th>Label</th>
                            <th>Tipe</th>
                            <th>Wajib</th>
                            <th>Urutan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($formFields as $field)
                        <tr>
                            <td>{{ $field->id_field }}</td>
                            <td>{{ $field->field_name }}</td>
                            <td>{{ $field->field_label }}</td>
                            <td>{{ $field->field_type }}</td>
                            <td>
                                @if($field->is_required)
                                    <span class="badge-status badge-publish">✅ Ya</span>
                                @else
                                    <span class="badge-status badge-draft">❌ Tidak</span>
                                @endif
                            </td>
                            <td>{{ $field->sort_order }}</td>
                            <td>
                                @if($field->is_active)
                                    <span class="badge-status badge-publish">Aktif</span>
                                @else
                                    <span class="badge-status badge-draft">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" 
                                        data-bs-target="#editModal{{ $field->id_field }}">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <form action="{{ route('admin.pendaftaran.form-fields.destroy', $field->id_field) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('Hapus field {{ $field->field_label }}?')">
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

<!-- Modal Edit Form Field -->
@foreach($formFields as $field)
<div class="modal fade" id="editModal{{ $field->id_field }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.pendaftaran.form-fields.update', $field->id_field) }}" method="POST" id="formEditField{{ $field->id_field }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Field: {{ $field->field_label }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nama Field</label>
                        <input type="text" name="field_name" class="form-control" value="{{ $field->field_name }}" required>
                    </div>
                    <div class="mb-3">
                        <label>Label Field</label>
                        <input type="text" name="field_label" class="form-control" value="{{ $field->field_label }}" required>
                    </div>
                    <div class="mb-3">
                        <label>Tipe Field</label>
                        <select name="field_type" class="form-select">
                            <option value="text" {{ $field->field_type == 'text' ? 'selected' : '' }}>Text</option>
                            <option value="email" {{ $field->field_type == 'email' ? 'selected' : '' }}>Email</option>
                            <option value="tel" {{ $field->field_type == 'tel' ? 'selected' : '' }}>Telepon</option>
                            <option value="textarea" {{ $field->field_type == 'textarea' ? 'selected' : '' }}>Textarea</option>
                            <option value="number" {{ $field->field_type == 'number' ? 'selected' : '' }}>Number</option>
                            <option value="date" {{ $field->field_type == 'date' ? 'selected' : '' }}>Date</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Placeholder</label>
                        <input type="text" name="placeholder" class="form-control" value="{{ $field->placeholder }}">
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="is_required" value="1" class="form-check-input" 
                                   id="is_required_checkbox_{{ $field->id_field }}" {{ $field->is_required ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_required_checkbox_{{ $field->id_field }}">
                                Wajib diisi
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Urutan</label>
                        <input type="number" name="sort_order" class="form-control" value="{{ $field->sort_order }}">
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" 
                                   id="is_active_checkbox_{{ $field->id_field }}" {{ $field->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active_checkbox_{{ $field->id_field }}">
                                Aktif
                            </label>
                        </div>
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