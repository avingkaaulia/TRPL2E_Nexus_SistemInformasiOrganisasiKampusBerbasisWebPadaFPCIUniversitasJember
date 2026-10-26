<?php

namespace Tests\Unit;

use App\Models\ActivityLog;
use Tests\DatabaseTestCase;

class ActivityLogModelTest extends DatabaseTestCase
{
    private function buatLog($aksi, $userId = null)
    {
        return ActivityLog::create([
            'user_id' => $userId,
            'user_name' => 'Admin FPCI',
            'action' => $aksi,
            'description' => 'uji coba log',
            'created_at' => now(),
        ]);
    }

    // ---- user() ----
    public function test_log_terhubung_ke_user_pelaku()
    {
        $admin = $this->buatAdmin(['nama' => 'Arzu Ridho']);
        $log = $this->buatLog('create', $admin->id_user);

        $this->assertEquals('Arzu Ridho', $log->user->nama);
    }

    public function test_user_log_kosong_untuk_aktivitas_guest()
    {
        $log = $this->buatLog('login', null);

        $this->assertNull($log->user);
    }

    public function test_user_log_kosong_kalau_user_sudah_tidak_ada()
    {
        $log = $this->buatLog('login', 999);

        $this->assertNull($log->user);
    }

    // ---- getActionLabelAttribute() ----
    public function test_label_aksi_login()
    {
        $this->assertEquals('Login', $this->buatLog('login')->action_label);
    }

    public function test_label_aksi_create_jadi_tambah_data()
    {
        $this->assertEquals('Tambah Data', $this->buatLog('create')->action_label);
    }

    public function test_label_aksi_yang_tidak_terdaftar_pakai_huruf_besar_di_awal()
    {
        $this->assertEquals('Restore', $this->buatLog('restore')->action_label);
    }

    // ---- getActionColorAttribute() ----
    public function test_warna_aksi_login_adalah_success()
    {
        $this->assertEquals('success', $this->buatLog('login')->action_color);
    }

    public function test_warna_aksi_delete_adalah_danger()
    {
        $this->assertEquals('danger', $this->buatLog('delete')->action_color);
    }

    public function test_warna_aksi_yang_tidak_terdaftar_adalah_secondary()
    {
        $this->assertEquals('secondary', $this->buatLog('restore')->action_color);
    }
}
