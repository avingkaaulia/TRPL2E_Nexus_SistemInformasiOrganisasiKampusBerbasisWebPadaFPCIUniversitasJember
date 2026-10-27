<?php

namespace Tests\Unit;

use App\Models\BerkasPendaftaran;
use App\Models\JenisBerkas;
use Tests\DatabaseTestCase;

class RelasiPendaftaranTest extends DatabaseTestCase
{
    private function buatBerkas($pendaftar, $jenis)
    {
        return BerkasPendaftaran::create([
            'id_pendaftaran' => $pendaftar->id_pendaftaran,
            'id_jenis' => $jenis->id_jenis,
            'file_path' => 'berkas/' . $pendaftar->id_pendaftaran . '/ktm.jpg',
        ]);
    }

    private function buatJenis()
    {
        return JenisBerkas::create(['nama_jenis' => 'KTM', 'is_required' => 1, 'file_type' => 'jpg', 'max_size' => 1024]);
    }

    // ---- Pendaftaran::periode() ----
    public function test_pendaftaran_terhubung_ke_periode()
    {
        $periode = $this->buatPeriode(['nama_periode' => 'Gelombang 1']);
        $pendaftar = $this->buatPendaftar(['id_periode' => $periode->id_periode]);

        $this->assertEquals('Gelombang 1', $pendaftar->periode->nama_periode);
    }

    public function test_periode_pendaftaran_kosong_kalau_belum_terhubung()
    {
        $pendaftar = $this->buatPendaftar(['id_periode' => null]);

        $this->assertNull($pendaftar->periode);
    }

    // ---- Pendaftaran::berkas() ----
    public function test_pendaftaran_punya_beberapa_berkas()
    {
        $pendaftar = $this->buatPendaftar();
        $jenis = $this->buatJenis();
        $this->buatBerkas($pendaftar, $jenis);
        $this->buatBerkas($pendaftar, $jenis);

        $this->assertCount(2, $pendaftar->berkas);
    }

    public function test_pendaftaran_tanpa_berkas()
    {
        $pendaftar = $this->buatPendaftar();

        $this->assertCount(0, $pendaftar->berkas);
    }

    // ---- BerkasPendaftaran::pendaftaran() ----
    public function test_berkas_terhubung_ke_pendaftar()
    {
        $pendaftar = $this->buatPendaftar(['nama' => 'Arzu']);
        $berkas = $this->buatBerkas($pendaftar, $this->buatJenis());

        $this->assertEquals('Arzu', $berkas->pendaftaran->nama);
    }

    public function test_pendaftar_berkas_kosong_kalau_id_tidak_ada()
    {
        $berkas = BerkasPendaftaran::create(['id_pendaftaran' => 404, 'id_jenis' => 1, 'file_path' => 'x.jpg']);

        $this->assertNull($berkas->pendaftaran);
    }

    // ---- BerkasPendaftaran::jenisBerkas() ----
    public function test_berkas_terhubung_ke_jenis_berkas()
    {
        $berkas = $this->buatBerkas($this->buatPendaftar(), $this->buatJenis());

        $this->assertEquals('KTM', $berkas->jenisBerkas->nama_jenis);
    }

    public function test_jenis_berkas_kosong_kalau_id_jenis_tidak_ada()
    {
        $pendaftar = $this->buatPendaftar();
        $berkas = BerkasPendaftaran::create(['id_pendaftaran' => $pendaftar->id_pendaftaran, 'id_jenis' => 404, 'file_path' => 'x.jpg']);

        $this->assertNull($berkas->jenisBerkas);
    }
}
