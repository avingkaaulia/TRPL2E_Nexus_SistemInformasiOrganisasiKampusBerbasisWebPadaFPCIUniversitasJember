<?php

namespace Tests\Unit;

use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\Schema;
use Tests\DatabaseTestCase;

class ActivityLoggerTest extends DatabaseTestCase
{
    // TC-LOG-001
    public function test_log_mencatat_aktivitas_user_yang_sedang_login()
    {
        $admin = $this->buatAdmin(['nama' => 'Atmaja Eka']);
        $this->actingAs($admin);

        ActivityLogger::log('create', 'Menambah postingan', 'Post', 5);

        $this->assertDatabaseHas('activity_log', [
            'user_id' => $admin->id_user,
            'user_name' => 'Atmaja Eka',
            'action' => 'create',
            'description' => 'Menambah postingan',
            'model_type' => 'Post',
            'model_id' => 5,
        ]);
    }

    public function test_log_tanpa_login_dicatat_sebagai_guest()
    {
        ActivityLogger::log('login', 'percobaan login');

        $this->assertDatabaseHas('activity_log', [
            'user_id' => null,
            'user_name' => 'Guest',
            'action' => 'login',
        ]);
    }

    public function test_log_hanya_dengan_parameter_aksi()
    {
        ActivityLogger::log('logout');

        $this->assertDatabaseHas('activity_log', ['action' => 'logout', 'description' => null]);
    }

    public function test_log_mencatat_ip_dan_waktu()
    {
        ActivityLogger::log('update', 'edit data');

        $log = \App\Models\ActivityLog::first();
        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->created_at);
    }

    public function test_log_tidak_melempar_error_kalau_tabel_log_bermasalah()
    {
        Schema::drop('activity_log');

        // di kode aslinya error log memang dibiarkan supaya proses utama tetap jalan
        ActivityLogger::log('create', 'tabel tidak ada');

        $this->assertTrue(true);
    }
}
