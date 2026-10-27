<?php

namespace Tests\Unit;

use App\Models\PostGallery;
use App\Models\Role;
use Tests\DatabaseTestCase;

// relasi kecil-kecil dari model Anggota, Divisi, Role, dan PostGallery
class RelasiModelLainTest extends DatabaseTestCase
{
    // ---- Anggota::user() ----
    public function test_anggota_terhubung_ke_user()
    {
        $user = $this->buatAnggotaUser(['nama' => 'Martha Dwi']);
        $anggota = $this->buatAnggotaData($user, $this->buatDivisi());

        $this->assertEquals('Martha Dwi', $anggota->user->nama);
    }

    public function test_user_anggota_kosong_kalau_user_dihapus()
    {
        $user = $this->buatAnggotaUser();
        $anggota = $this->buatAnggotaData($user, $this->buatDivisi());
        $user->delete();

        $this->assertNull($anggota->fresh()->user);
    }

    // ---- Anggota::divisi() ----
    public function test_anggota_terhubung_ke_divisi()
    {
        $divisi = $this->buatDivisi('Penelitian dan Pengembangan');
        $anggota = $this->buatAnggotaData($this->buatAnggotaUser(), $divisi);

        $this->assertEquals('Penelitian dan Pengembangan', $anggota->divisi->nama_divisi);
    }

    public function test_divisi_anggota_kosong_kalau_id_divisi_tidak_ada()
    {
        $divisi = $this->buatDivisi();
        $anggota = $this->buatAnggotaData($this->buatAnggotaUser(), $divisi, ['id_divisi' => 404]);

        $this->assertNull($anggota->divisi);
    }

    // ---- Divisi::anggota() ----
    public function test_divisi_punya_banyak_anggota()
    {
        $divisi = $this->buatDivisi();
        $this->buatAnggotaData($this->buatAnggotaUser(), $divisi, ['no_urut' => 1]);
        $this->buatAnggotaData($this->buatAnggotaUser(), $divisi, ['no_urut' => 2]);

        $this->assertCount(2, $divisi->anggota);
    }

    public function test_divisi_tanpa_anggota()
    {
        $divisi = $this->buatDivisi('Kosong');

        $this->assertCount(0, $divisi->anggota);
    }

    public function test_anggota_divisi_lain_tidak_ikut_terhitung()
    {
        $humas = $this->buatDivisi('Humas');
        $litbang = $this->buatDivisi('Litbang');
        $this->buatAnggotaData($this->buatAnggotaUser(), $litbang);

        $this->assertCount(0, $humas->anggota);
    }

    // ---- Role::users() ----
    public function test_role_admin_punya_user_admin()
    {
        $this->buatAdmin();
        $this->buatAdmin();

        $this->assertCount(2, Role::find(1)->users);
    }

    public function test_role_tanpa_user()
    {
        $this->buatAnggotaUser();

        $this->assertCount(0, Role::find(1)->users);
    }

    public function test_user_role_lain_tidak_masuk_daftar()
    {
        $this->buatAdmin();
        $this->buatAnggotaUser();

        $this->assertCount(1, Role::find(2)->users);
    }

    // ---- PostGallery::post() ----
    public function test_gambar_gallery_terhubung_ke_post()
    {
        $post = $this->buatPost(['title' => 'Dokumentasi Seminar']);
        $gambar = PostGallery::create(['id_post' => $post->id_post, 'image_path' => 'gallery/a.jpg']);

        $this->assertEquals('Dokumentasi Seminar', $gambar->post->title);
    }

    public function test_post_gallery_kosong_kalau_id_post_tidak_ada()
    {
        $gambar = PostGallery::create(['id_post' => 404, 'image_path' => 'gallery/a.jpg']);

        $this->assertNull($gambar->post);
    }

    public function test_dua_gambar_satu_post()
    {
        $post = $this->buatPost();
        $a = PostGallery::create(['id_post' => $post->id_post, 'image_path' => 'gallery/a.jpg']);
        $b = PostGallery::create(['id_post' => $post->id_post, 'image_path' => 'gallery/b.jpg']);

        $this->assertEquals($a->post->id_post, $b->post->id_post);
    }
}
