@extends('layouts.app')

@section('content')
<div class="pendaftaran-page">
    <div class="container">
        
        <!-- 🔥 TAMPILAN ALERT SUCCESS/ERROR -->
        @if(session('success'))
            <div class="alert alert-success-custom alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill"></i> 
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error-custom alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> 
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error-custom alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <strong>Terjadi kesalahan:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

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
                    {{ $periodeAktif->deskripsi ?? ($config->welcome_text ?? '') }}<br>
                    <small>🎯 Kuota tersisa: {{ $periodeAktif->getSisaKuotaAttribute() }} dari {{ $periodeAktif->kuota }}</small>
                </div>
            </div>

            <form action="{{ route('pendaftaran.store') }}" method="POST" class="form-pendaftaran" enctype="multipart/form-data" id="formPendaftaran">
                @csrf
                <input type="hidden" name="id_periode" value="{{ $periodeAktif->id_periode }}">
                
                <!-- 🔥 FORM FIELD DINAMIS DARI DATABASE -->
                <div class="form-section">
                    <h3>Data Diri</h3>
                    
                    @foreach($formFields as $field)
                    <div class="form-group">
                        <label>
                            {{ $field->field_label }}
                            @if($field->is_required)
                                <span class="required-star">*</span>
                            @endif
                        </label>
                        
                        @if($field->field_type == 'textarea')
                            <textarea name="{{ $field->field_name }}" 
                                      class="form-control" 
                                      rows="4"
                                      placeholder="{{ $field->placeholder }}"
                                      {{ $field->is_required ? 'required' : '' }}>{{ old($field->field_name) }}</textarea>
                        @else
                            <input type="{{ $field->field_type }}" 
                                   name="{{ $field->field_name }}" 
                                   class="form-control" 
                                   placeholder="{{ $field->placeholder }}"
                                   value="{{ old($field->field_name) }}"
                                   {{ $field->is_required ? 'required' : '' }}>
                        @endif
                        
                        @error($field->field_name)
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    @endforeach
                </div>
                
                <!-- 🔥 UPLOAD BERKAS DINAMIS DARI DATABASE -->
                <div class="form-section">
                    <h3>Upload Berkas Pendaftaran</h3>
                    <p class="text-muted mb-3">Silahkan upload berkas yang diperlukan</p>
                    
                    @foreach($jenisBerkas as $berkas)
                    <div class="form-group">
                        <label>
                            {{ $berkas->nama_jenis }}
                            @if($berkas->is_required)
                                <span class="required-star">*</span>
                            @else
                                <span class="optional-text">(Opsional)</span>
                            @endif
                        </label>
                        <div class="file-upload-wrapper">
                            <input type="file" 
                                   name="berkas_{{ $berkas->id_jenis }}" 
                                   class="file-upload-input" 
                                   accept=".{{ $berkas->file_type }}"
                                   data-max-size="{{ $berkas->max_size }}"
                                   {{ $berkas->is_required ? 'required' : '' }}>
                            <div class="file-upload-info">
                                <small class="text-muted">
                                    Format: {{ strtoupper($berkas->file_type) }} | 
                                    Maksimal: {{ $berkas->max_size >= 1024 ? round($berkas->max_size/1024,1).' MB' : $berkas->max_size.' KB' }}
                                </small>
                            </div>
                        </div>
                        @error('berkas_' . $berkas->id_jenis)
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    @endforeach
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-submit" id="btnSubmit">
                        <i class="bi bi-check-circle"></i> Daftar Sekarang
                    </button>
                    <a href="/" class="btn-cancel">
                        <i class="bi bi-x-circle"></i> Batal
                    </a>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto hide alert after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-success-custom, .alert-error-custom');
        alerts.forEach(alert => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);

    // File size validation
    const fileInputs = document.querySelectorAll('.file-upload-input');
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = this.files[0];
            if (file) {
                const maxSizeKB = parseInt(this.getAttribute('data-max-size'));
                const fileSizeKB = file.size / 1024;
                
                if (fileSizeKB > maxSizeKB) {
                    const maxSizeMB = (maxSizeKB / 1024).toFixed(2);
                    alert(`Ukuran file terlalu besar! Maksimal ${maxSizeMB} MB`);
                    this.value = '';
                }
            }
        });
    });

    // Prevent double submit
    const form = document.getElementById('formPendaftaran');
    const submitBtn = document.getElementById('btnSubmit');
    
    if (form) {
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Memproses...';
        });
    }
});
</script>
@endsection