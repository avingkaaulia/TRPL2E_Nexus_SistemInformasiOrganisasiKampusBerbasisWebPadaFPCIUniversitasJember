<?php

namespace Tests\Feature;

use Tests\DatabaseTestCase;

// HomeController, AboutController, ContactController
class HalamanPublikTest extends DatabaseTestCase
{
    // ---- HomeController::index() ----
    public function test_melihat_homepage()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_homepage_memakai_view_home()
    {
        $response = $this->get('/');

        $response->assertViewIs('home');
    }

    public function test_carousel_home_hanya_post_publish_dari_kategori_carousel_home()
    {
        $this->postDiKategori('carousel_home');
        $this->postDiKategori('carousel_home');
        $this->postDiKategori('carousel_home', ['status' => 'draft']);

        $response = $this->get('/');

        $response->assertViewHas('carousel', function ($c) {
            return $c->count() == 2;
        });
    }

    public function test_carousel_home_memakai_kategori_carousel_kalau_kosong()
    {
        $this->postDiKategori('carousel');

        $response = $this->get('/');

        $response->assertViewHas('carousel', function ($c) {
            return $c->count() == 1;
        });
    }

    public function test_kegiatan_di_home_dibatasi_4_post()
    {
        for ($i = 0; $i < 6; $i++) {
            $this->postDiKategori('kegiatan');
        }

        $response = $this->get('/');

        $response->assertViewHas('kegiatan', function ($k) {
            return $k->count() == 4;
        });
    }

    public function test_pendaftaran_terbuka_kalau_ada_periode_aktif()
    {
        $this->buatPeriode();

        $response = $this->get('/');

        $response->assertViewHas('isPendaftaranOpen', true);
    }

    public function test_pendaftaran_tertutup_kalau_tidak_ada_periode_aktif()
    {
        $response = $this->get('/');

        $response->assertViewHas('isPendaftaranOpen', false);
    }

    // ---- AboutController::index() ----
    public function test_melihat_halaman_about()
    {
        $response = $this->get('/about');

        $response->assertStatus(200);
        $response->assertViewIs('about.index');
    }

    public function test_about_menampilkan_bagian_tentang_dari_post_kategori_about()
    {
        $this->postDiKategori('about', ['title' => 'Tentang FPCI UNEJ']);

        $response = $this->get('/about');

        $response->assertViewHas('tentang', function ($t) {
            return $t->title == 'Tentang FPCI UNEJ';
        });
    }

    public function test_about_bagian_selain_yang_dikenal_masuk_other_sections()
    {
        $this->postDiKategori('about', ['title' => 'Visi']);
        $this->postDiKategori('about', ['title' => 'Nilai Organisasi']);

        $response = $this->get('/about');

        $response->assertViewHas('otherSections', function ($o) {
            return $o->count() == 1 && $o->first()->title == 'Nilai Organisasi';
        });
    }

    public function test_about_hanya_menampilkan_president_dan_head()
    {
        $divisi = $this->buatDivisi();
        $this->buatAnggotaData($this->buatAnggotaUser(), $divisi, ['jabatan' => 'President', 'no_urut' => 1]);
        $this->buatAnggotaData($this->buatAnggotaUser(), $divisi, ['jabatan' => 'Head of Humas', 'no_urut' => 2]);
        $this->buatAnggotaData($this->buatAnggotaUser(), $divisi, ['jabatan' => 'Staff', 'no_urut' => 3]);

        $response = $this->get('/about');

        $response->assertViewHas('anggota', function ($a) {
            return $a->count() == 2;
        });
    }

    // ---- ContactController::index() ----
    public function test_melihat_halaman_contact()
    {
        $response = $this->get('/contact');

        $response->assertStatus(200);
        $response->assertViewIs('contact.index');
    }

    public function test_halaman_contact_menampilkan_data_kontak_dari_admin()
    {
        $this->buatKontak(['email' => 'resmi@fpci.test']);

        $response = $this->get('/contact');

        $response->assertViewHas('contact', function ($c) {
            return $c->email == 'resmi@fpci.test';
        });
    }

    // TC-CONTACT-005
    public function test_halaman_contact_saat_data_belum_diinput()
    {
        $response = $this->get('/contact');

        $response->assertStatus(200);
        $this->assertNull($response->viewData('contact'));
    }

    // TC-CONTACT-006
    public function test_halaman_contact_dengan_alamat_255_karakter()
    {
        $this->buatKontak(['alamat' => str_repeat('a', 255)]);

        $response = $this->get('/contact');

        $response->assertStatus(200);
    }
}
