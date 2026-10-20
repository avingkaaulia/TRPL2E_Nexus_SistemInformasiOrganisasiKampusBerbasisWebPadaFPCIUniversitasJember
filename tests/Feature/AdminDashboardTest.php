<?php

namespace Tests\Feature;

use Tests\DatabaseTestCase;

class AdminDashboardTest extends DatabaseTestCase
{
    public function test_admin_melihat_dashboard()
    {
        $response = $this->actingAs($this->buatAdmin())->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }

    public function test_dashboard_menghitung_user_admin_dan_anggota()
    {
        $admin = $this->buatAdmin();
        $this->buatAnggotaUser();
        $this->buatAnggotaUser();

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertViewHas('totalAdmin', 1);
        $response->assertViewHas('totalAnggotaUser', 2);
    }

    public function test_dashboard_menghitung_pendaftar_yang_menunggu()
    {
        $this->buatPendaftar(['status' => 'menunggu']);
        $this->buatPendaftar(['status' => 'menunggu']);
        $this->buatPendaftar(['status' => 'diterima']);

        $response = $this->actingAs($this->buatAdmin())->get('/admin/dashboard');

        $response->assertViewHas('totalPendaftaran', 3);
        $response->assertViewHas('pendingPendaftaran', 2);
    }

    public function test_total_karya_hanya_dari_kategori_writings_dan_sub_kategorinya()
    {
        $writings = $this->buatKategori('writings');
        $sub = $this->buatKategori('opini', $writings->id_category);
        $lain = $this->buatKategori('event');
        $this->buatPost(['id_post_category' => $writings->id_category]);
        $this->buatPost(['id_post_category' => $sub->id_category]);
        $this->buatPost(['id_post_category' => $lain->id_category]);

        $response = $this->actingAs($this->buatAdmin())->get('/admin/dashboard');

        $response->assertViewHas('totalKarya', 2);
        $response->assertViewHas('totalPosts', 3);
    }

    public function test_total_karya_nol_kalau_kategori_writings_belum_ada()
    {
        $response = $this->actingAs($this->buatAdmin())->get('/admin/dashboard');

        $response->assertViewHas('totalKarya', 0);
    }

    public function test_dashboard_menghitung_anggota_dan_komentar()
    {
        $post = $this->buatPost();
        $this->buatKomentar($post);
        $this->buatAnggotaData($this->buatAnggotaUser(), $this->buatDivisi());

        $response = $this->actingAs($this->buatAdmin())->get('/admin/dashboard');

        $response->assertViewHas('totalComments', 1);
        $response->assertViewHas('totalAnggota', 1);
    }
}
