<?php

namespace Tests\Feature;

use Tests\DatabaseTestCase;

class WritingsControllerTest extends DatabaseTestCase
{
    private function subWritings($nama = 'opini')
    {
        $induk = \App\Models\PostCategory::where('category_name', 'writings')->first() ?? $this->buatKategori('writings');

        return $this->buatKategori($nama, $induk->id_category);
    }

    // ---- index() ----
    // TC-PUB-001
    public function test_melihat_halaman_writings()
    {
        $response = $this->get('/writings');

        $response->assertStatus(200);
        $response->assertViewIs('writings.index');
    }

    public function test_writings_menampilkan_karya_publish_di_sub_kategori()
    {
        $sub = $this->subWritings();
        $this->buatPost(['id_post_category' => $sub->id_category]);
        $this->buatPost(['id_post_category' => $sub->id_category]);

        $response = $this->get('/writings');

        $response->assertViewHas('posts', function ($p) {
            return $p->count() == 2;
        });
    }

    // TC-PUB-002
    public function test_writings_tidak_menampilkan_karya_pending_atau_draft()
    {
        $sub = $this->subWritings();
        $this->buatPost(['id_post_category' => $sub->id_category, 'status' => 'pending']);
        $this->buatPost(['id_post_category' => $sub->id_category, 'status' => 'draft']);

        $response = $this->get('/writings');

        $response->assertViewHas('posts', function ($p) {
            return $p->count() == 0;
        });
    }

    public function test_writings_pencarian_berdasarkan_judul()
    {
        $sub = $this->subWritings();
        $this->buatPost(['id_post_category' => $sub->id_category, 'title' => 'Geopolitik Asia']);
        $this->buatPost(['id_post_category' => $sub->id_category, 'title' => 'Ekonomi Hijau']);

        $response = $this->get('/writings?search=Geopolitik');

        $response->assertViewHas('posts', function ($p) {
            return $p->count() == 1;
        });
    }

    // ---- all() ----
    public function test_melihat_semua_writings()
    {
        $response = $this->get('/writings/all');

        $response->assertStatus(200);
        $response->assertViewIs('writings.all');
    }

    public function test_semua_writings_dipaginasi_9_per_halaman()
    {
        $sub = $this->subWritings();
        for ($i = 0; $i < 11; $i++) {
            $this->buatPost(['id_post_category' => $sub->id_category]);
        }

        $response = $this->get('/writings/all');

        $response->assertViewHas('posts', function ($p) {
            return $p->count() == 9 && $p->total() == 11;
        });
    }

    public function test_semua_writings_urut_judul_a_ke_z()
    {
        $sub = $this->subWritings();
        $this->buatPost(['id_post_category' => $sub->id_category, 'title' => 'Zona Baru']);
        $this->buatPost(['id_post_category' => $sub->id_category, 'title' => 'Asia Tenggara']);

        $response = $this->get('/writings/all?sort=az');

        $response->assertViewHas('sort', 'az');
    }

    // ---- category() ----
    // TC-SORT-002
    public function test_writings_per_kategori()
    {
        $opini = $this->subWritings('opini');
        $esai = $this->subWritings('esai');
        $this->buatPost(['id_post_category' => $opini->id_category]);
        $this->buatPost(['id_post_category' => $esai->id_category]);

        $response = $this->get('/writings/category/' . $opini->id_category);

        $response->assertStatus(200);
        $response->assertViewHas('posts', function ($p) {
            return $p->count() == 1;
        });
    }

    // TC-SORT-003
    public function test_writings_kategori_tanpa_karya()
    {
        $kosong = $this->subWritings('kosong');

        $response = $this->get('/writings/category/' . $kosong->id_category);

        $response->assertStatus(200);
        $response->assertViewHas('posts', function ($p) {
            return $p->count() == 0;
        });
    }

    // TC-SORT-004
    public function test_writings_kategori_tidak_ada_dialihkan_ke_halaman_writings()
    {
        $response = $this->get('/writings/category/9999');

        $response->assertRedirect(route('writings'));
    }

    // ---- show() ----
    public function test_melihat_detail_karya()
    {
        $sub = $this->subWritings();
        $post = $this->buatPost(['id_post_category' => $sub->id_category]);

        $response = $this->get('/writings/' . $post->id_post);

        $response->assertStatus(200);
    }

    public function test_detail_karya_yang_belum_divalidasi_tidak_bisa_dibuka()
    {
        $post = $this->buatPost(['status' => 'pending']);

        $response = $this->get('/writings/' . $post->id_post);

        $response->assertStatus(404);
    }

    public function test_detail_karya_dengan_id_tidak_ada()
    {
        $response = $this->get('/writings/9999');

        $response->assertStatus(404);
    }
}
