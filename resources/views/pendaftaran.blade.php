@extends('layouts.app')

@section('content')
<div class="pendaftaran-page">
    <div class="container">
        <div class="pendaftaran-header">
            <h1>Form Pendaftaran Anggota FPCI UNEJ</h1>
            <p>Isi data diri Anda dengan lengkap dan benar</p>
        </div>

        @if($periodeAktif && isset($config) && $config->is_open)
        <div class="pendaftaran-card">
            <div class="pendaftaran-info">
                <div class="alert-info">
                    <strong>📢 {{ $periodeAktif->nama_periode }}</strong><br>
                    Periode: {{ Carbon\Carbon::parse($periodeAktif->tanggal_mulai)->format('d M Y') }} - {{ Carbon\Carbon::parse($periodeAktif->tanggal_selesai)->format('d M Y') }}<br>
                    {{ $periodeAktif->deskripsi ?? ($config->welcome_text ?? '') }}
                </div>
            </div>

            <form action="{{ route('pendaftaran.store') }}" method="POST" class="form-pendaftaran">
                @csrf
                <input type="hidden" name="id_periode" value="{{ $periodeAktif->id_periode }}">
                
                <div class="form-section">
                    <h3>Data Diri</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nama Lengkap *</label>
                            <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" required>
                        </div>
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>No. HP/WA *</label>
                            <input type="text" name="no_hp" class="form-control" value="{{ old('no_hp') }}" required>
                        </div>
                        <div class="form-group">
                            <label>NIM *</label>
                            <input type="text" name="nim" class="form-control" value="{{ old('nim') }}" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Jurusan *</label>
                            <input type="text" name="jurusan" class="form-control" value="{{ old('jurusan') }}" required>
                        </div>
                        <div class="form-group">
                            <label>Fakultas *</label>
                            <input type="text" name="fakultas" class="form-control" value="{{ old('fakultas') }}" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Alamat Lengkap *</label>
                        <textarea name="alamat" class="form-control" rows="3" required>{{ old('alamat') }}</textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Alasan Bergabung dengan FPCI UNEJ *</label>
                        <textarea name="alasan" class="form-control" rows="4" required>{{ old('alasan') }}</textarea>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Daftar Sekarang</button>
                    <a href="/" class="btn-cancel">Batal</a>
                </div>
            </form>
        </div>
        @else
        <div class="pendaftaran-closed">
            <div class="alert-closed">
                <h3>🔒 {{ isset($config) ? ($config->closing_text ?? 'Pendaftaran Ditutup') : 'Pendaftaran Ditutup' }}</h3>
                <p>Terima kasih atas minat Anda bergabung dengan FPCI UNEJ.</p>
                <a href="/" class="btn-back">Kembali ke Beranda</a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection