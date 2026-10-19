<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use Tests\DatabaseTestCase;

class AdminActivityLogControllerTest extends DatabaseTestCase
{
    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->buatAdmin();
    }

    private function buatLog($aksi, $umurHari = 0, $deskripsi = 'uji', $userId = null)
    {
        return ActivityLog::create([
            'user_id' => $userId,
            'user_name' => 'Admin',
            'action' => $aksi,
            'description' => $deskripsi,
            'created_at' => now()->subDays($umurHari),
        ]);
    }

    // ---- index() ----
    public function test_admin_melihat_log_aktivitas()
    {
        $response = $this->actingAs($this->admin)->get('/admin/activity-log');

        $response->assertStatus(200);
        $response->assertViewIs('admin.activity-log.index');
    }

    public function test_log_aktivitas_bisa_difilter_berdasarkan_aksi()
    {
        $this->buatLog('create');
        $this->buatLog('delete');

        $response = $this->actingAs($this->admin)->get('/admin/activity-log?action=delete');

        $response->assertViewHas('logs', function ($l) {
            return $l->total() == 1;
        });
    }

    public function test_log_aktivitas_bisa_dicari_dari_deskripsi()
    {
        $this->buatLog('create', 0, 'Menambah kategori Technology');
        $this->buatLog('create', 0, 'Menambah menu Galeri');

        $response = $this->actingAs($this->admin)->get('/admin/activity-log?cari=Technology');

        $response->assertViewHas('logs', function ($l) {
            return $l->total() == 1;
        });
    }

    public function test_log_aktivitas_bisa_difilter_berdasarkan_tanggal()
    {
        $this->buatLog('create', 10);
        $this->buatLog('create', 0);

        $response = $this->actingAs($this->admin)->get('/admin/activity-log?dari=' . now()->subDays(2)->toDateString());

        $response->assertViewHas('logs', function ($l) {
            return $l->total() == 1;
        });
    }

    public function test_statistik_log_hari_ini()
    {
        $this->buatLog('create', 0, 'a', 1);
        $this->buatLog('create', 0, 'b', 2);
        $this->buatLog('create', 5, 'c', 1);

        $response = $this->actingAs($this->admin)->get('/admin/activity-log');

        $response->assertViewHas('totalLogs', 3);
        $response->assertViewHas('todayLogs', 2);
        $response->assertViewHas('activeUsers', 2);
    }

    // ---- clear() ----
    public function test_hapus_log_lama_lebih_dari_n_hari()
    {
        $this->buatLog('create', 30);
        $this->buatLog('create', 20);
        $this->buatLog('create', 1);

        $response = $this->actingAs($this->admin)->delete('/admin/activity-log/clear', ['hari' => 10]);

        $response->assertSessionHas('success', '2 log lama berhasil dihapus.');
        $this->assertEquals(1, ActivityLog::count());
    }

    public function test_hapus_log_tanpa_mengisi_jumlah_hari()
    {
        $response = $this->actingAs($this->admin)->delete('/admin/activity-log/clear', []);

        $response->assertSessionHasErrors('hari');
    }

    public function test_hapus_log_dengan_hari_nol_ditolak()
    {
        $response = $this->actingAs($this->admin)->delete('/admin/activity-log/clear', ['hari' => 0]);

        $response->assertSessionHasErrors('hari');
    }

    public function test_hapus_log_dengan_hari_bukan_angka_ditolak()
    {
        $response = $this->actingAs($this->admin)->delete('/admin/activity-log/clear', ['hari' => 'abc']);

        $response->assertSessionHasErrors('hari');
    }

    // TC-LOG-004
    public function test_anggota_tidak_boleh_membuka_log_aktivitas()
    {
        $response = $this->actingAs($this->buatAnggotaUser())->get('/admin/activity-log');

        $response->assertRedirect('/');
    }
}
