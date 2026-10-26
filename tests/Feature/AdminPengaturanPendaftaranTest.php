<?php

namespace Tests\Feature;

use App\Models\PeriodePendaftaran;
use Illuminate\Support\Facades\DB;
use Tests\DatabaseTestCase;

// config dan periode pendaftaran di AdminPendaftaranController
class AdminPengaturanPendaftaranTest extends DatabaseTestCase
{
    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->buatAdmin();
    }

    private function dataPeriode(array $ganti = [])
    {
        return array_merge([
            'tahun_ajaran' => '2026/2027',
            'nama_periode' => 'Open Recruitment Gelombang 1',
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-10-31',
            'kuota' => 30,
        ], $ganti);
    }

    // ---- config() ----
    public function test_admin_melihat_konfigurasi_pendaftaran()
    {
        $response = $this->actingAs($this->admin)->get('/admin/pendaftaran/config');

        $response->assertStatus(200);
        $response->assertViewIs('admin.pendaftaran.config');
    }

    public function test_konfigurasi_membawa_periode_yang_sedang_aktif()
    {
        $this->buatPeriode(['nama_periode' => 'Gelombang Aktif']);

        $response = $this->actingAs($this->admin)->get('/admin/pendaftaran/config');

        $response->assertViewHas('periodeAktif', function ($p) {
            return $p->nama_periode == 'Gelombang Aktif';
        });
    }

    // ---- updateConfig() ----
    public function test_admin_membuka_pendaftaran()
    {
        $response = $this->actingAs($this->admin)->put('/admin/pendaftaran/config', ['is_open' => 1, 'welcome_text' => 'Selamat datang']);

        $response->assertRedirect(route('admin.pendaftaran.config'));
        $this->assertDatabaseHas('pendaftaran_config', ['id_config' => 1, 'is_open' => 1, 'welcome_text' => 'Selamat datang']);
    }

    public function test_menutup_pendaftaran_menonaktifkan_periode_yang_aktif()
    {
        $periode = $this->buatPeriode();
        $this->bukaPendaftaran();

        $this->actingAs($this->admin)->put('/admin/pendaftaran/config', ['closing_text' => 'Sampai jumpa']);

        $this->assertDatabaseHas('pendaftaran_config', ['id_config' => 1, 'is_open' => 0]);
        $this->assertDatabaseHas('periode_pendaftaran', ['id_periode' => $periode->id_periode, 'is_active' => 0]);
    }

    public function test_update_config_dua_kali_tidak_menambah_baris()
    {
        $this->actingAs($this->admin)->put('/admin/pendaftaran/config', ['is_open' => 1]);
        $this->actingAs($this->admin)->put('/admin/pendaftaran/config', ['is_open' => 1]);

        $this->assertEquals(1, DB::table('pendaftaran_config')->count());
    }

    // ---- periode() ----
    public function test_admin_melihat_daftar_periode()
    {
        $this->buatPeriode();

        $response = $this->actingAs($this->admin)->get('/admin/pendaftaran/periode');

        $response->assertStatus(200);
        $response->assertViewHas('periode', function ($p) {
            return $p->count() == 1;
        });
    }

    public function test_daftar_periode_diurutkan_dari_tanggal_mulai_terbaru()
    {
        $this->buatPeriode(['nama_periode' => 'Lama', 'tanggal_mulai' => '2025-01-01', 'tanggal_selesai' => '2025-02-01']);
        $this->buatPeriode(['nama_periode' => 'Baru', 'tanggal_mulai' => '2026-01-01', 'tanggal_selesai' => '2026-02-01']);

        $response = $this->actingAs($this->admin)->get('/admin/pendaftaran/periode');

        $response->assertViewHas('periode', function ($p) {
            return $p->first()->nama_periode == 'Baru';
        });
    }

    // ---- storePeriode() ----
    // TC-REG-005
    public function test_admin_menambah_periode_dengan_tanggal_dan_kuota()
    {
        $response = $this->actingAs($this->admin)->post('/admin/pendaftaran/periode', $this->dataPeriode());

        $response->assertRedirect(route('admin.pendaftaran.periode'));
        $this->assertDatabaseHas('periode_pendaftaran', ['nama_periode' => 'Open Recruitment Gelombang 1', 'kuota' => 30]);
    }

    public function test_periode_baru_selalu_dibuat_dalam_keadaan_nonaktif()
    {
        $this->actingAs($this->admin)->post('/admin/pendaftaran/periode', $this->dataPeriode());

        $this->assertDatabaseHas('periode_pendaftaran', ['nama_periode' => 'Open Recruitment Gelombang 1', 'is_active' => 0]);
    }

    public function test_periode_dengan_tanggal_selesai_sebelum_tanggal_mulai()
    {
        $response = $this->actingAs($this->admin)->post('/admin/pendaftaran/periode', $this->dataPeriode([
            'tanggal_mulai' => '2026-10-31', 'tanggal_selesai' => '2026-10-01',
        ]));

        $response->assertSessionHasErrors('tanggal_selesai');
    }

    public function test_periode_dengan_kuota_nol()
    {
        $response = $this->actingAs($this->admin)->post('/admin/pendaftaran/periode', $this->dataPeriode(['kuota' => 0]));

        $response->assertSessionHasErrors('kuota');
    }

    public function test_periode_tanpa_data_wajib()
    {
        $response = $this->actingAs($this->admin)->post('/admin/pendaftaran/periode', []);

        $response->assertSessionHasErrors(['tahun_ajaran', 'nama_periode', 'tanggal_mulai', 'tanggal_selesai', 'kuota']);
    }

    // ---- updatePeriode() ----
    public function test_admin_mengaktifkan_periode_saat_pendaftaran_dibuka()
    {
        $this->bukaPendaftaran(1);
        $periode = $this->buatPeriode(['is_active' => 0]);

        $response = $this->actingAs($this->admin)->put('/admin/pendaftaran/periode/' . $periode->id_periode, $this->dataPeriode(['is_active' => 1]));

        $response->assertRedirect(route('admin.pendaftaran.periode'));
        $this->assertDatabaseHas('periode_pendaftaran', ['id_periode' => $periode->id_periode, 'is_active' => 1]);
    }

    public function test_mengaktifkan_periode_saat_pendaftaran_ditutup_ditolak()
    {
        $this->bukaPendaftaran(0);
        $periode = $this->buatPeriode(['is_active' => 0]);

        $response = $this->actingAs($this->admin)->put('/admin/pendaftaran/periode/' . $periode->id_periode, $this->dataPeriode(['is_active' => 1]));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('periode_pendaftaran', ['id_periode' => $periode->id_periode, 'is_active' => 0]);
    }

    public function test_mengaktifkan_satu_periode_menonaktifkan_periode_lain()
    {
        $this->bukaPendaftaran(1);
        $lama = $this->buatPeriode(['nama_periode' => 'Lama', 'is_active' => 1]);
        $baru = $this->buatPeriode(['nama_periode' => 'Baru', 'is_active' => 0]);

        $this->actingAs($this->admin)->put('/admin/pendaftaran/periode/' . $baru->id_periode, $this->dataPeriode(['is_active' => 1]));

        $this->assertDatabaseHas('periode_pendaftaran', ['id_periode' => $lama->id_periode, 'is_active' => 0]);
    }

    public function test_update_periode_dengan_tanggal_selesai_sebelum_mulai()
    {
        $periode = $this->buatPeriode();

        $response = $this->actingAs($this->admin)->put('/admin/pendaftaran/periode/' . $periode->id_periode, $this->dataPeriode([
            'tanggal_mulai' => '2026-10-31', 'tanggal_selesai' => '2026-10-01',
        ]));

        $response->assertSessionHasErrors('tanggal_selesai');
    }

    public function test_update_periode_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->put('/admin/pendaftaran/periode/999', $this->dataPeriode());

        $response->assertStatus(404);
    }

    // ---- destroyPeriode() ----
    public function test_admin_menghapus_periode()
    {
        $periode = $this->buatPeriode();

        $response = $this->actingAs($this->admin)->delete('/admin/pendaftaran/periode/' . $periode->id_periode);

        $response->assertRedirect(route('admin.pendaftaran.periode'));
        $this->assertDatabaseMissing('periode_pendaftaran', ['id_periode' => $periode->id_periode]);
    }

    public function test_menghapus_periode_aktif_terakhir_menutup_pendaftaran()
    {
        $this->bukaPendaftaran(1);
        $periode = $this->buatPeriode();

        $this->actingAs($this->admin)->delete('/admin/pendaftaran/periode/' . $periode->id_periode);

        $this->assertDatabaseHas('pendaftaran_config', ['id_config' => 1, 'is_open' => 0]);
    }

    public function test_hapus_periode_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->delete('/admin/pendaftaran/periode/999');

        $response->assertStatus(404);
    }
}
