<?php

namespace Tests\Feature;

use Tests\DatabaseTestCase;

// PostController dan PageController
class PostPageControllerTest extends DatabaseTestCase
{
    // ---- PostController::show() ----
    public function test_melihat_detail_post()
    {
        $post = $this->buatPost();

        $response = $this->get('/post/' . $post->id_post);

        $response->assertStatus(200);
        $response->assertViewIs('show');
    }

    // TC-ANN-003
    public function test_detail_post_dengan_id_tidak_ada()
    {
        $response = $this->get('/post/9999');

        $response->assertStatus(404);
    }

    public function test_detail_post_draft_tidak_bisa_dibuka()
    {
        $post = $this->buatPost(['status' => 'draft']);

        $response = $this->get('/post/' . $post->id_post);

        $response->assertStatus(404);
    }

    public function test_detail_post_yang_sudah_dihapus_soft_delete_tidak_bisa_dibuka()
    {
        $post = $this->buatPost();
        $post->delete();

        $response = $this->get('/post/' . $post->id_post);

        $response->assertStatus(404);
    }

    public function test_detail_post_menampilkan_maksimal_3_post_terkait()
    {
        $kategori = $this->buatKategori('event');
        $utama = $this->buatPost(['id_post_category' => $kategori->id_category]);
        for ($i = 0; $i < 5; $i++) {
            $this->buatPost(['id_post_category' => $kategori->id_category]);
        }

        $response = $this->get('/post/' . $utama->id_post);

        $response->assertViewHas('relatedPosts', function ($r) {
            return $r->count() == 3;
        });
    }

    // ---- PageController::show() ----
    public function test_melihat_halaman_page()
    {
        $page = $this->buatPost(['post_type' => 'page']);

        $response = $this->get('/page/' . $page->id_post);

        $response->assertStatus(200);
        $response->assertViewIs('page.show');
    }

    public function test_post_biasa_tidak_bisa_dibuka_lewat_route_page()
    {
        $post = $this->buatPost(['post_type' => 'post']);

        $response = $this->get('/page/' . $post->id_post);

        $response->assertStatus(404);
    }

    public function test_page_draft_tidak_bisa_dibuka()
    {
        $page = $this->buatPost(['post_type' => 'page', 'status' => 'draft']);

        $response = $this->get('/page/' . $page->id_post);

        $response->assertStatus(404);
    }

    public function test_page_membawa_post_terkait_dari_kategori_yang_sama()
    {
        $kategori = $this->buatKategori('about');
        $page = $this->buatPost(['post_type' => 'page', 'id_post_category' => $kategori->id_category]);
        $this->buatPost(['id_post_category' => $kategori->id_category]);
        $this->buatPost(['id_post_category' => $kategori->id_category, 'status' => 'draft']);

        $response = $this->get('/page/' . $page->id_post);

        $response->assertViewHas('postsInPage', function ($p) {
            return $p->count() == 1;
        });
    }

    // ---- PageController::all() ----
    public function test_melihat_daftar_semua_page()
    {
        $response = $this->get('/pages');

        $response->assertStatus(200);
        $response->assertViewIs('pages.index');
    }

    public function test_daftar_page_hanya_berisi_page_yang_publish()
    {
        $this->buatPost(['post_type' => 'page']);
        $this->buatPost(['post_type' => 'page', 'status' => 'draft']);
        $this->buatPost(['post_type' => 'post']);

        $response = $this->get('/pages');

        $response->assertViewHas('pages', function ($p) {
            return $p->total() == 1;
        });
    }

    public function test_daftar_page_dipaginasi_12_per_halaman()
    {
        for ($i = 0; $i < 13; $i++) {
            $this->buatPost(['post_type' => 'page']);
        }

        $response = $this->get('/pages');

        $response->assertViewHas('pages', function ($p) {
            return $p->count() == 12;
        });
    }
}
