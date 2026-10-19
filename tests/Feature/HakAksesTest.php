<?php

namespace Tests\Feature;

use Tests\DatabaseTestCase;

// pengujian hak akses (KF06) untuk halaman admin, memakai AdminMiddleware dan Authenticate
class HakAksesTest extends DatabaseTestCase
{
    public function urlAdmin()
    {
        return [
            ['/admin/dashboard'],
            ['/admin/posts'],
            ['/admin/categories'],
            ['/admin/menu'],
            ['/admin/comments'],
            ['/admin/users'],
            ['/admin/anggota'],
            ['/admin/pendaftaran'],
            ['/admin/carousel'],
            ['/admin/contact'],
            ['/admin/logo'],
            ['/admin/activity-log'],
            ['/admin/writings/pending'],
        ];
    }

    // TC-RBAC-002
    /**
     * @dataProvider urlAdmin
     */
    public function test_pengunjung_tanpa_login_diarahkan_ke_login($url)
    {
        $response = $this->get($url);

        $response->assertRedirect(route('login'));
    }

    // TC-RBAC-003
    /**
     * @dataProvider urlAdmin
     */
    public function test_anggota_tidak_bisa_membuka_halaman_admin($url)
    {
        $response = $this->actingAs($this->buatAnggotaUser())->get($url);

        $response->assertRedirect('/');
        $response->assertSessionHas('error', 'Akses ditolak! Anda bukan admin.');
    }

    // TC-RBAC-001
    public function test_admin_bisa_membuka_dashboard()
    {
        $response = $this->actingAs($this->buatAdmin())->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    // TC-RBAC-004
    public function test_pengunjung_tanpa_login_tidak_bisa_membuka_halaman_anggota()
    {
        $this->get('/profile')->assertRedirect(route('login'));
        $this->get('/writings/submit')->assertRedirect(route('login'));
    }

    // TC-RBAC-005
    public function test_akses_halaman_terbatas_setelah_sesi_berakhir()
    {
        $admin = $this->buatAdmin();
        $this->actingAs($admin)->get('/admin/dashboard')->assertStatus(200);
        $this->post('/logout');

        $response = $this->get('/admin/dashboard');

        $response->assertRedirect(route('login'));
    }

    // TC-RBAC-006
    public function test_url_admin_dengan_garis_miring_di_akhir_tetap_dibatasi()
    {
        $response = $this->get('/admin/dashboard/');

        $response->assertRedirect(route('login'));
    }

    public function test_url_admin_dengan_huruf_kapital_tidak_ditemukan()
    {
        $response = $this->actingAs($this->buatAnggotaUser())->get('/ADMIN/dashboard');

        $response->assertStatus(404);
    }
}
