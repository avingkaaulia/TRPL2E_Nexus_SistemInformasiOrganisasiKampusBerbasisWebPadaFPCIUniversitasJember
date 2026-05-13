{{-- resources/views/admin/anggota/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Detail Anggota - Admin FPCI UNEJ')
@section('page-title', 'Detail Anggota')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h4><i class="bi bi-person-badge me-2"></i> Detail Anggota</h4>
        <a href="{{ route('admin.anggota.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
    
    <div class="row">
        <div class="col-md-4 text-center">
            @if($anggota->foto)
                <img src="{{ asset($anggota->foto) }}" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
            @else
                <div class="rounded-circle bg-secondary mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 150px; height: 150px;">
                    <i class="bi bi-person fs-1 text-white"></i>
                </div>
            @endif
            
            @if($anggota->link)
                <a href="{{ $anggota->link }}" target="_blank" class="btn btn-primary btn-sm">
                    <i class="bi bi-instagram"></i> Instagram
                </a>
            @endif
        </div>
        
        <div class="col-md-8">
            <table class="table table-bordered">
                <tr>
                    <th width="30%">No Urut</th>
                    <td>{{ $anggota->no_urut }}</td>
                </tr>
                <tr>
                    <th>Nama Lengkap</th>
                    <td>{{ $anggota->user->nama }}</td>
                </tr>
                <tr>
                    <th>Username</th>
                    <td>{{ $anggota->user->username }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $anggota->user->email }}</td>
                </tr>
                <tr>
                    <th>Divisi</th>
                    <td>{{ $anggota->divisi->nama_divisi ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Jabatan</th>
                    <td>{{ $anggota->jabatan }}</td>
                </tr>
                <tr>
                    <th>Periode</th>
                    <td>{{ $anggota->periode }}</td>
                </tr>
                <tr>
                    <th>Tanggal Bergabung</th>
                    <td>{{ \Carbon\Carbon::parse($anggota->user->tanggal_daftar)->format('d F Y') }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>
@endsection