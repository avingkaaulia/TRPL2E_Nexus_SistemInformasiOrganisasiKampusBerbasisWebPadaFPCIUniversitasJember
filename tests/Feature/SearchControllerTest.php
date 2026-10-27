<?php

namespace Tests\Feature;

use Tests\DatabaseTestCase;

class SearchControllerTest extends DatabaseTestCase
{
    // ---- search() ----
    // TC-SEARCH-003
    public function test_cari_tanpa_kata_kunci()
    {
        $response = $this->get('/search');

        $response->assertSessionHas('error', 'Masukkan kata kunci pencarian');
    }

    // TC-SEARCH-001
    public function test_cari_kata_kunci_yang_ada_di_judul_post()
    {
        $this->buatPost(['title' => 'Diplomasi Digital Indonesia']);

        $response = $this->get('/search?q=Diplomasi');

        $response->assertStatus(200);
        $response->assertViewHas('counts', function ($c) {
            return $c['posts'] == 1;
        });
    }

    // TC-SEARCH-002
    public function test_cari_kata_kunci_yang_tidak_ada()
    {
        $this->buatPost(['title' => 'Diplomasi Digital Indonesia']);

        $response = $this->get('/search?q=zzzxxx');

        $response->assertStatus(200);
        $response->assertViewHas('totalResults', 0);
    }

    // TC-SEARCH-004
    public function test_cari_dengan_karakter_khusus_dan_tag_html()
    {
        $response = $this->get('/search?q=' . urlencode('<script>alert(1)</script>'));

        $response->assertStatus(200);
        $response->assertViewHas('totalResults', 0);
    }

    public function test_cari_tidak_menampilkan_post_yang_masih_draft()
    {
        $this->buatPost(['title' => 'Naskah Rahasia', 'status' => 'draft']);

        $response = $this->get('/search?q=Rahasia');

        $response->assertViewHas('counts', function ($c) {
            return $c['posts'] == 0;
        });
    }

    public function test_cari_dengan_type_posts_hanya_mencari_post()
    {
        $this->buatPost(['title' => 'Seminar Kebijakan']);
        $this->buatKontak(['alamat' => 'Jl Seminar Raya']);

        $response = $this->get('/search?q=Seminar&type=posts');

        $response->assertViewHas('counts', function ($c) {
            return $c['posts'] == 1 && $c['contact'] == 0;
        });
    }

    // kategori writings di kode dicari lewat id tetap (2), jadi kategori kedua yang dibuat dipakai di sini
    public function test_cari_type_writings()
    {
        $this->buatKategori('lain');
        $writings = $this->buatKategori('writings');
        $this->buatPost(['title' => 'Esai Geopolitik', 'id_post_category' => $writings->id_category]);

        $response = $this->get('/search?q=Geopolitik&type=writings');

        $response->assertViewHas('counts', function ($c) {
            return $c['writings'] == 1;
        });
    }

    public function test_cari_type_struktur_berdasarkan_nama_divisi()
    {
        $divisi = $this->buatDivisi('Penelitian dan Pengembangan');
        $this->buatAnggotaData($this->buatAnggotaUser(), $divisi);

        $response = $this->get('/search?q=Penelitian&type=struktur');

        $response->assertViewHas('counts', function ($c) {
            return $c['struktur'] == 1;
        });
    }

    public function test_cari_type_contact_berdasarkan_email()
    {
        $this->buatKontak(['email' => 'fpci.unej@gmail.com']);

        $response = $this->get('/search?q=fpci.unej&type=contact');

        $response->assertViewHas('counts', function ($c) {
            return $c['contact'] == 1;
        });
    }

    public function test_cari_type_pendaftaran_berdasarkan_nama_periode()
    {
        $this->buatPeriode(['nama_periode' => 'Open Recruitment Gelombang 1']);

        $response = $this->get('/search?q=Recruitment&type=pendaftaran');

        $response->assertViewHas('counts', function ($c) {
            return $c['pendaftaran'] == 1;
        });
    }

    public function test_cari_type_about()
    {
        $this->postDiKategori('about', ['title' => 'Sejarah FPCI']);

        $response = $this->get('/search?q=Sejarah&type=about');

        $response->assertViewHas('counts', function ($c) {
            return $c['about'] == 1;
        });
    }

    // ---- autocomplete() ----
    public function test_autocomplete_kurang_dari_2_huruf_mengembalikan_array_kosong()
    {
        $response = $this->getJson('/search/autocomplete?q=a');

        $response->assertOk();
        $response->assertExactJson([]);
    }

    public function test_autocomplete_menemukan_judul_post()
    {
        $post = $this->buatPost(['title' => 'Diplomasi Maritim']);

        $response = $this->getJson('/search/autocomplete?q=Diplo');

        $response->assertJsonFragment(['url' => '/post/' . $post->id_post, 'type' => 'Artikel']);
    }

    public function test_autocomplete_judul_panjang_dipotong_50_karakter()
    {
        $this->buatPost(['title' => 'Diplomasi ' . str_repeat('panjang ', 15)]);

        $response = $this->getJson('/search/autocomplete?q=Diplo');

        $judul = $response->json()[0]['title'];
        $this->assertStringEndsWith('...', $judul);
        $this->assertLessThanOrEqual(53, strlen($judul));
    }

    public function test_autocomplete_menemukan_divisi()
    {
        $this->buatDivisi('Hubungan Masyarakat');

        $response = $this->getJson('/search/autocomplete?q=Hubungan');

        $response->assertJsonFragment(['title' => 'Struktur Hubungan Masyarakat']);
    }

    public function test_autocomplete_menemukan_periode_pendaftaran_aktif()
    {
        $this->buatPeriode(['nama_periode' => 'Recruitment Baru', 'is_active' => 1]);

        $response = $this->getJson('/search/autocomplete?q=Recruitment');

        $response->assertJsonFragment(['type' => 'Pendaftaran - Aktif']);
    }

    public function test_autocomplete_tidak_ada_yang_cocok()
    {
        $response = $this->getJson('/search/autocomplete?q=qqqqq');

        $response->assertExactJson([]);
    }
}
