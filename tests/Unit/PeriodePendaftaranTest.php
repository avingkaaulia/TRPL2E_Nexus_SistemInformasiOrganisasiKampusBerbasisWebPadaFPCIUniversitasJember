<?php

namespace Tests\Unit;

use Tests\DatabaseTestCase;

class PeriodePendaftaranTest extends DatabaseTestCase
{
    // ---- getJumlahPendaftarAttribute() ----
    public function test_jumlah_pendaftar_nol_kalau_belum_ada_yang_daftar()
    {
        $periode = $this->buatPeriode();

        $this->assertEquals(0, $periode->jumlah_pendaftar);
    }

    public function test_jumlah_pendaftar_menghitung_semua_pendaftar_periode_itu()
    {
        $periode = $this->buatPeriode();
        $this->buatPendaftar(['id_periode' => $periode->id_periode]);
        $this->buatPendaftar(['id_periode' => $periode->id_periode]);

        $this->assertEquals(2, $periode->jumlah_pendaftar);
    }

    public function test_jumlah_pendaftar_tidak_menghitung_periode_lain()
    {
        $periode1 = $this->buatPeriode();
        $periode2 = $this->buatPeriode(['nama_periode' => 'Gelombang 2']);
        $this->buatPendaftar(['id_periode' => $periode2->id_periode]);

        $this->assertEquals(0, $periode1->jumlah_pendaftar);
    }

    // ---- getSisaKuotaAttribute() ----
    public function test_sisa_kuota_sama_dengan_kuota_kalau_belum_ada_pendaftar()
    {
        $periode = $this->buatPeriode(['kuota' => 10]);

        $this->assertEquals(10, $periode->sisa_kuota);
    }

    public function test_sisa_kuota_berkurang_setiap_ada_pendaftar()
    {
        $periode = $this->buatPeriode(['kuota' => 3]);
        $this->buatPendaftar(['id_periode' => $periode->id_periode]);

        $this->assertEquals(2, $periode->sisa_kuota);
    }

    public function test_sisa_kuota_nol_kalau_kuota_penuh()
    {
        $periode = $this->buatPeriode(['kuota' => 2]);
        $this->buatPendaftar(['id_periode' => $periode->id_periode]);
        $this->buatPendaftar(['id_periode' => $periode->id_periode]);

        $this->assertEquals(0, $periode->sisa_kuota);
    }

    // ---- isAktif() ----
    public function test_periode_aktif_kalau_hari_ini_di_dalam_rentang_tanggal()
    {
        $periode = $this->buatPeriode();

        $this->assertTrue($periode->isAktif());
    }

    public function test_periode_tidak_aktif_kalau_is_active_nol()
    {
        $periode = $this->buatPeriode(['is_active' => 0]);

        $this->assertFalse($periode->isAktif());
    }

    public function test_periode_belum_dimulai_tidak_aktif()
    {
        $periode = $this->buatPeriode([
            'tanggal_mulai' => now()->addDays(2)->toDateString(),
            'tanggal_selesai' => now()->addDays(9)->toDateString(),
        ]);

        $this->assertFalse($periode->isAktif());
    }

    public function test_periode_sudah_berakhir_tidak_aktif()
    {
        $periode = $this->buatPeriode([
            'tanggal_mulai' => now()->subDays(9)->toDateString(),
            'tanggal_selesai' => now()->subDay()->toDateString(),
        ]);

        $this->assertFalse($periode->isAktif());
    }

    // TC-REG-008, hari terakhir pendaftaran masih dihitung aktif
    public function test_periode_masih_aktif_di_hari_terakhir()
    {
        $periode = $this->buatPeriode(['tanggal_selesai' => now()->toDateString()]);

        $this->assertTrue($periode->isAktif());
    }

    public function test_periode_aktif_di_hari_pertama()
    {
        $periode = $this->buatPeriode(['tanggal_mulai' => now()->toDateString()]);

        $this->assertTrue($periode->isAktif());
    }
}
