<?php

namespace Tests\Unit;

use App\Models\PostCategory;
use Tests\DatabaseTestCase;

class PostCategoryModelTest extends DatabaseTestCase
{
    // ---- parent() ----
    public function test_sub_kategori_punya_parent()
    {
        $induk = $this->buatKategori('writings');
        $anak = $this->buatKategori('opini', $induk->id_category);

        $this->assertEquals('writings', $anak->parent->category_name);
    }

    public function test_kategori_utama_tidak_punya_parent()
    {
        $induk = $this->buatKategori('event');

        $this->assertNull($induk->parent);
    }

    public function test_parent_kosong_kalau_parent_id_tidak_ada_di_tabel()
    {
        $kategori = $this->buatKategori('yatim', 500);

        $this->assertNull($kategori->parent);
    }

    // ---- children() ----
    public function test_kategori_punya_beberapa_sub_kategori()
    {
        $induk = $this->buatKategori('program');
        $this->buatKategori('Sedang Direncanakan', $induk->id_category);
        $this->buatKategori('Sedang Berlangsung', $induk->id_category);

        $this->assertCount(2, $induk->children);
    }

    public function test_kategori_tanpa_sub_kategori()
    {
        $kategori = $this->buatKategori('urgent');

        $this->assertCount(0, $kategori->children);
    }

    public function test_children_tidak_ikut_menghitung_cucu()
    {
        $a = $this->buatKategori('a');
        $b = $this->buatKategori('b', $a->id_category);
        $this->buatKategori('c', $b->id_category);

        $this->assertCount(1, $a->children);
    }

    // ---- getDisplayCategoryNameAttribute() ----
    public function test_display_name_pakai_nama_parent_kalau_ada()
    {
        $induk = $this->buatKategori('writings');
        $anak = $this->buatKategori('opini', $induk->id_category);

        $this->assertEquals('writings', $anak->display_category_name);
    }

    public function test_display_name_pakai_nama_sendiri_kalau_tanpa_parent()
    {
        $kategori = $this->buatKategori('pengumuman');

        $this->assertEquals('pengumuman', $kategori->display_category_name);
    }

    public function test_display_name_nama_sendiri_kalau_parent_hilang()
    {
        $kategori = $this->buatKategori('yatim', 500);

        $this->assertEquals('yatim', $kategori->display_category_name);
    }

    // ---- getAllChildrenIds() ----
    public function test_all_children_ids_tanpa_anak_hanya_id_sendiri()
    {
        $kategori = $this->buatKategori('about');

        $this->assertEquals([$kategori->id_category], $kategori->getAllChildrenIds());
    }

    public function test_all_children_ids_memuat_anak()
    {
        $induk = $this->buatKategori('writings');
        $anak1 = $this->buatKategori('opini', $induk->id_category);
        $anak2 = $this->buatKategori('esai', $induk->id_category);

        $ids = $induk->getAllChildrenIds();

        $this->assertCount(3, $ids);
        $this->assertContains($anak1->id_category, $ids);
        $this->assertContains($anak2->id_category, $ids);
    }

    public function test_all_children_ids_sampai_ke_cucu()
    {
        $a = $this->buatKategori('a');
        $b = $this->buatKategori('b', $a->id_category);
        $c = $this->buatKategori('c', $b->id_category);

        $this->assertContains($c->id_category, $a->getAllChildrenIds());
    }

    // ---- getMainCategories() ----
    public function test_main_categories_hanya_yang_tidak_punya_parent()
    {
        $induk = $this->buatKategori('writings');
        $this->buatKategori('opini', $induk->id_category);
        $this->buatKategori('event');

        $this->assertCount(2, PostCategory::getMainCategories());
    }

    public function test_main_categories_ikut_menghitung_parent_id_nol()
    {
        $this->buatKategori('lama', 0);
        $this->buatKategori('baru');

        $this->assertCount(2, PostCategory::getMainCategories());
    }

    public function test_main_categories_kosong_kalau_semua_kategori_punya_parent()
    {
        $this->buatKategori('anak', 3);

        $this->assertCount(0, PostCategory::getMainCategories());
    }

    // ---- posts() ----
    public function test_kategori_punya_banyak_post()
    {
        $kategori = $this->buatKategori('event');
        $this->buatPost(['id_post_category' => $kategori->id_category]);
        $this->buatPost(['id_post_category' => $kategori->id_category]);

        $this->assertCount(2, $kategori->posts);
    }

    public function test_kategori_tanpa_post()
    {
        $kategori = $this->buatKategori('kosong');

        $this->assertCount(0, $kategori->posts);
    }

    public function test_post_kategori_lain_tidak_ikut_terhitung()
    {
        $a = $this->buatKategori('a');
        $b = $this->buatKategori('b');
        $this->buatPost(['id_post_category' => $b->id_category]);

        $this->assertCount(0, $a->posts);
    }

    // ---- getTotalPostCount() ----
    public function test_total_post_count_kategori_tanpa_post_adalah_nol()
    {
        $kategori = $this->buatKategori('sepi');

        $this->assertEquals(0, PostCategory::getTotalPostCount($kategori->id_category));
    }

    public function test_total_post_count_menghitung_post_di_kategori_itu()
    {
        $kategori = $this->buatKategori('event');
        $this->buatPost(['id_post_category' => $kategori->id_category]);
        $this->buatPost(['id_post_category' => $kategori->id_category]);
        $this->buatPost(['id_post_category' => $kategori->id_category]);

        $this->assertEquals(3, PostCategory::getTotalPostCount($kategori->id_category));
    }

    public function test_total_post_count_ikut_menjumlah_sub_kategori()
    {
        $induk = $this->buatKategori('writings');
        $anak = $this->buatKategori('opini', $induk->id_category);
        $this->buatPost(['id_post_category' => $induk->id_category]);
        $this->buatPost(['id_post_category' => $anak->id_category]);
        $this->buatPost(['id_post_category' => $anak->id_category]);

        $this->assertEquals(3, PostCategory::getTotalPostCount($induk->id_category));
    }

    // ---- getTotalPostsCountAttribute() ----
    public function test_total_posts_count_attribute_sama_dengan_hasil_method_static()
    {
        $kategori = $this->buatKategori('event');
        $this->buatPost(['id_post_category' => $kategori->id_category]);

        $this->assertEquals(1, $kategori->total_posts_count);
    }

    public function test_total_posts_count_attribute_nol_untuk_kategori_kosong()
    {
        $kategori = $this->buatKategori('sepi');

        $this->assertEquals(0, $kategori->total_posts_count);
    }

    public function test_total_posts_count_attribute_termasuk_sub_kategori()
    {
        $induk = $this->buatKategori('program');
        $anak = $this->buatKategori('Selesai', $induk->id_category);
        $this->buatPost(['id_post_category' => $anak->id_category]);

        $this->assertEquals(1, $induk->total_posts_count);
    }
}
