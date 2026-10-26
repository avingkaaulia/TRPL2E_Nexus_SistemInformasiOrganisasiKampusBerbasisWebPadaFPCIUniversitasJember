<?php

namespace Tests\Unit;

use App\Models\BerkasPendaftaran;
use App\Models\JenisBerkas;
use Tests\DatabaseTestCase;

class JenisBerkasTest extends DatabaseTestCase
{
    private function buatJenis($ukuran = 2048, $nama = 'KTM')
    {
        return JenisBerkas::create([
            'nama_jenis' => $nama,
            'is_required' => 1,
            'file_type' => 'jpg,png,pdf',
            'max_size' => $ukuran,
        ]);
    }

    // ---- berkasPendaftaran() ----
    public function test_jenis_berkas_punya_banyak_berkas_pendaftaran()
    {
        $jenis = $this->buatJenis();
        $pendaftar = $this->buatPendaftar();
        BerkasPendaftaran::create(['id_pendaftaran' => $pendaftar->id_pendaftaran, 'id_jenis' => $jenis->id_jenis, 'file_path' => 'berkas/1/ktm.jpg']);
        BerkasPendaftaran::create(['id_pendaftaran' => $pendaftar->id_pendaftaran, 'id_jenis' => $jenis->id_jenis, 'file_path' => 'berkas/1/ktm2.jpg']);

        $this->assertCount(2, $jenis->berkasPendaftaran);
    }

    public function test_jenis_berkas_tanpa_berkas_yang_diupload()
    {
        $jenis = $this->buatJenis();

        $this->assertCount(0, $jenis->berkasPendaftaran);
    }

    public function test_berkas_jenis_lain_tidak_ikut_terhitung()
    {
        $ktm = $this->buatJenis(2048, 'KTM');
        $cv = $this->buatJenis(1024, 'CV');
        $pendaftar = $this->buatPendaftar();
        BerkasPendaftaran::create(['id_pendaftaran' => $pendaftar->id_pendaftaran, 'id_jenis' => $cv->id_jenis, 'file_path' => 'berkas/1/cv.pdf']);

        $this->assertCount(0, $ktm->berkasPendaftaran);
    }

    // ---- getMaxSizeReadableAttribute() ----
    public function test_ukuran_di_bawah_1024_ditampilkan_dalam_kb()
    {
        $jenis = $this->buatJenis(500);

        $this->assertEquals('500 KB', $jenis->max_size_readable);
    }

    public function test_ukuran_2048_ditampilkan_2_mb()
    {
        $jenis = $this->buatJenis(2048);

        $this->assertEquals('2 MB', $jenis->max_size_readable);
    }

    public function test_ukuran_tepat_1024_ditampilkan_1_mb()
    {
        $jenis = $this->buatJenis(1024);

        $this->assertEquals('1 MB', $jenis->max_size_readable);
    }

    public function test_ukuran_1536_ditampilkan_satu_setengah_mb()
    {
        $jenis = $this->buatJenis(1536);

        $this->assertEquals('1.5 MB', $jenis->max_size_readable);
    }
}
