<?php

namespace Tests\Unit;

use App\Models\Post;
use App\Models\PostGallery;
use Tests\DatabaseTestCase;

class PostModelTest extends DatabaseTestCase
{
    // ---- category() ----
    public function test_post_punya_kategori()
    {
        $kategori = $this->buatKategori('event');
        $post = $this->buatPost(['id_post_category' => $kategori->id_category]);

        $this->assertEquals('event', $post->category->category_name);
    }

    public function test_kategori_post_kosong_kalau_id_kategori_tidak_ada()
    {
        $post = $this->buatPost(['id_post_category' => 999]);

        $this->assertNull($post->category);
    }

    public function test_dua_post_bisa_satu_kategori()
    {
        $kategori = $this->buatKategori('writings');
        $a = $this->buatPost(['id_post_category' => $kategori->id_category]);
        $b = $this->buatPost(['id_post_category' => $kategori->id_category]);

        $this->assertEquals($a->category->id_category, $b->category->id_category);
    }

    // ---- user() ----
    public function test_post_punya_penulis()
    {
        $user = $this->buatAnggotaUser(['nama' => 'Siti Nailatul']);
        $post = $this->buatPost(['id_user' => $user->id_user]);

        $this->assertEquals('Siti Nailatul', $post->user->nama);
    }

    public function test_penulis_post_kosong_kalau_user_tidak_ada()
    {
        $post = $this->buatPost(['id_user' => 999]);

        $this->assertNull($post->user);
    }

    // ---- gallery() ----
    public function test_gallery_post_berisi_semua_gambar()
    {
        $post = $this->buatPost();
        PostGallery::create(['id_post' => $post->id_post, 'image_path' => 'gallery/a.jpg']);
        PostGallery::create(['id_post' => $post->id_post, 'image_path' => 'gallery/b.jpg']);

        $this->assertCount(2, $post->gallery);
    }

    public function test_gallery_post_kosong_kalau_belum_ada_gambar()
    {
        $post = $this->buatPost();

        $this->assertCount(0, $post->gallery);
    }

    public function test_gallery_tidak_tercampur_dengan_post_lain()
    {
        $post1 = $this->buatPost();
        $post2 = $this->buatPost();
        PostGallery::create(['id_post' => $post2->id_post, 'image_path' => 'gallery/x.jpg']);

        $this->assertCount(0, $post1->gallery);
        $this->assertCount(1, $post2->gallery);
    }

    // ---- getImageAttribute() ----
    public function test_image_mengembalikan_url_lengkap_dari_path()
    {
        $post = $this->buatPost(['featured_image_path' => 'img/foto.jpg']);

        $this->assertEquals(asset('img/foto.jpg'), $post->image);
    }

    public function test_image_dari_folder_storage()
    {
        $post = $this->buatPost(['featured_image_path' => 'storage/img/poster.png']);

        $this->assertEquals(asset('storage/img/poster.png'), $post->image);
    }

    public function test_image_diawali_http()
    {
        $post = $this->buatPost(['featured_image_path' => 'img/foto.jpg']);

        $this->assertStringStartsWith('http', $post->image);
    }

    // ---- comments() ----
    public function test_post_punya_komentar()
    {
        $post = $this->buatPost();
        $this->buatKomentar($post);
        $this->buatKomentar($post, ['email' => 'rina@mail.com']);

        $this->assertCount(2, $post->comments);
    }

    public function test_post_tanpa_komentar()
    {
        $post = $this->buatPost();

        $this->assertCount(0, $post->comments);
    }

    public function test_komentar_hanya_milik_post_yang_bersangkutan()
    {
        $post1 = $this->buatPost();
        $post2 = $this->buatPost();
        $this->buatKomentar($post2);

        $this->assertCount(0, $post1->comments);
    }

    // ---- getParentCategoryNameAttribute() ----
    public function test_parent_category_name_kalau_kategori_punya_parent()
    {
        $induk = $this->buatKategori('writings');
        $anak = $this->buatKategori('opini', $induk->id_category);
        $post = $this->buatPost(['id_post_category' => $anak->id_category]);

        $this->assertEquals('writings', $post->parent_category_name);
    }

    public function test_parent_category_name_kalau_kategori_tanpa_parent()
    {
        $kategori = $this->buatKategori('pengumuman');
        $post = $this->buatPost(['id_post_category' => $kategori->id_category]);

        $this->assertEquals('pengumuman', $post->parent_category_name);
    }

    public function test_parent_category_name_uncategorized_kalau_kategori_hilang()
    {
        $post = $this->buatPost(['id_post_category' => 999]);

        $this->assertEquals('Uncategorized', $post->parent_category_name);
    }

    // ---- getParentCategoryIdAttribute() ----
    public function test_parent_category_id_kalau_punya_parent()
    {
        $induk = $this->buatKategori('writings');
        $anak = $this->buatKategori('opini', $induk->id_category);
        $post = $this->buatPost(['id_post_category' => $anak->id_category]);

        $this->assertEquals($induk->id_category, $post->parent_category_id);
    }

    public function test_parent_category_id_kalau_tanpa_parent()
    {
        $kategori = $this->buatKategori('event');
        $post = $this->buatPost(['id_post_category' => $kategori->id_category]);

        $this->assertEquals($kategori->id_category, $post->parent_category_id);
    }

    public function test_parent_category_id_null_kalau_kategori_hilang()
    {
        $post = $this->buatPost(['id_post_category' => 999]);

        $this->assertNull($post->parent_category_id);
    }

    // ---- getSlugAttribute() ----
    public function test_slug_dari_judul_biasa()
    {
        $post = new Post();
        $post->title = 'FPCI Goes to Campus 2026';

        $this->assertEquals('fpci-goes-to-campus-2026', $post->slug);
    }

    public function test_slug_membuang_tanda_baca()
    {
        $post = new Post();
        $post->title = 'Seminar Nasional, FPCI UNEJ 2026!';

        $this->assertEquals('seminar-nasional-fpci-unej-2026', $post->slug);
    }

    public function test_slug_judul_kosong()
    {
        $post = new Post();
        $post->title = '';

        $this->assertEquals('', $post->slug);
    }

    // ---- getPageUrlAttribute() ----
    public function test_page_url_mengarah_ke_route_page_show()
    {
        $post = new Post();
        $post->title = 'Diskusi Publik';

        $this->assertEquals(route('page.show', 'diskusi-publik'), $post->page_url);
    }

    public function test_page_url_memuat_path_page()
    {
        $post = new Post();
        $post->title = 'Tentang Kami';

        $this->assertStringContainsString('/page/tentang-kami', $post->page_url);
    }

    // ---- soft delete ----
    public function test_post_yang_dihapus_masih_ada_di_database()
    {
        $post = $this->buatPost();
        $post->delete();

        $this->assertSoftDeleted('posts', ['id_post' => $post->id_post]);
    }

    public function test_post_yang_dihapus_tidak_ikut_terambil_query_biasa()
    {
        $post = $this->buatPost();
        $post->delete();

        $this->assertEquals(0, Post::count());
        $this->assertEquals(1, Post::withTrashed()->count());
    }

    public function test_post_yang_dihapus_bisa_dikembalikan()
    {
        $post = $this->buatPost();
        $post->delete();
        $post->restore();

        $this->assertEquals(1, Post::count());
    }
}
