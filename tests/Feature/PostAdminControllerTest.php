<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\PostGallery;
use Illuminate\Support\Facades\Storage;
use Tests\DatabaseTestCase;

class PostAdminControllerTest extends DatabaseTestCase
{
    private $admin;
    private $kategori;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->buatAdmin();
        $this->kategori = $this->buatKategori('pengumuman');
    }

    private function dataPost(array $ganti = [])
    {
        return array_merge([
            'title' => 'Pengumuman Open Recruitment',
            'post_content' => 'Open recruitment anggota baru FPCI UNEJ resmi dibuka.',
            'id_post_category' => $this->kategori->id_category,
            'post_type' => 'post',
            'status' => 'publish',
        ], $ganti);
    }

    private function postBaru(array $attr = [])
    {
        return $this->buatPost(array_merge(['id_post_category' => $this->kategori->id_category], $attr));
    }

    // ---- index() ----
    public function test_admin_melihat_daftar_postingan()
    {
        $this->postBaru();

        $response = $this->actingAs($this->admin)->get('/admin/posts');

        $response->assertStatus(200);
        $response->assertViewIs('admin.posts.index');
    }

    // TC-ANN-001
    public function test_daftar_postingan_hanya_bertipe_post()
    {
        $this->postBaru();
        $this->postBaru(['post_type' => 'page']);

        $response = $this->actingAs($this->admin)->get('/admin/posts');

        $response->assertViewHas('posts', function ($p) {
            return $p->total() == 1;
        });
    }

    public function test_daftar_postingan_bisa_difilter_status()
    {
        $this->postBaru(['status' => 'draft']);
        $this->postBaru(['status' => 'publish']);

        $response = $this->actingAs($this->admin)->get('/admin/posts?status=draft');

        $response->assertViewHas('posts', function ($p) {
            return $p->total() == 1;
        });
    }

    public function test_daftar_postingan_bisa_dicari_berdasarkan_judul()
    {
        $this->postBaru(['title' => 'Seminar Nasional']);
        $this->postBaru(['title' => 'Rapat Internal']);

        $response = $this->actingAs($this->admin)->get('/admin/posts?search=Seminar');

        $response->assertViewHas('posts', function ($p) {
            return $p->total() == 1;
        });
    }

    // TC-SOFTDELETE-002
    public function test_postingan_yang_dihapus_hanya_muncul_di_trash()
    {
        $this->postBaru();
        $dihapus = $this->postBaru();
        $dihapus->delete();

        $response = $this->actingAs($this->admin)->get('/admin/posts?trash=1');

        $response->assertViewHas('showTrash', true);
        $response->assertViewHas('posts', function ($p) {
            return $p->total() == 1;
        });
    }

    public function test_jumlah_postingan_per_status_dihitung()
    {
        $this->postBaru(['status' => 'publish']);
        $this->postBaru(['status' => 'pending']);

        $response = $this->actingAs($this->admin)->get('/admin/posts');

        $response->assertViewHas('statusCounts', function ($s) {
            return $s['all'] == 2 && $s['publish'] == 1 && $s['pending'] == 1;
        });
    }

    // ---- create() ----
    public function test_admin_melihat_form_tambah_postingan()
    {
        $response = $this->actingAs($this->admin)->get('/admin/posts/create');

        $response->assertStatus(200);
        $response->assertViewHas('isPage', false);
    }

    public function test_form_tambah_dengan_type_page()
    {
        $response = $this->actingAs($this->admin)->get('/admin/posts/create?type=page');

        $response->assertViewHas('isPage', true);
    }

    // ---- store() ----
    // TC-ANNMGMT-001, TC-PROKER-001
    public function test_admin_menambah_postingan()
    {
        $response = $this->actingAs($this->admin)->post('/admin/posts/store', $this->dataPost());

        $response->assertRedirect(route('admin.posts.index'));
        $this->assertDatabaseHas('posts', [
            'title' => 'Pengumuman Open Recruitment',
            'status' => 'publish',
            'id_user' => $this->admin->id_user,
        ]);
    }

    public function test_menambah_halaman_diarahkan_ke_daftar_halaman()
    {
        $response = $this->actingAs($this->admin)->post('/admin/posts/store', $this->dataPost(['post_type' => 'page']));

        $response->assertRedirect(route('admin.pages.list'));
    }

    public function test_meta_title_otomatis_sama_dengan_judul()
    {
        $this->actingAs($this->admin)->post('/admin/posts/store', $this->dataPost());

        $this->assertDatabaseHas('posts', ['title' => 'Pengumuman Open Recruitment', 'meta_title' => 'Pengumuman Open Recruitment']);
    }

    // TC-ANNMGMT-004, TC-PROKER-004
    public function test_tambah_postingan_dengan_data_kosong()
    {
        $response = $this->actingAs($this->admin)->post('/admin/posts/store', []);

        $response->assertSessionHasErrors(['title', 'post_content', 'id_post_category', 'post_type', 'status']);
    }

    public function test_konten_postingan_kurang_dari_10_karakter()
    {
        $response = $this->actingAs($this->admin)->post('/admin/posts/store', $this->dataPost(['post_content' => 'pendek']));

        $response->assertSessionHasErrors('post_content');
    }

    public function test_kategori_postingan_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->post('/admin/posts/store', $this->dataPost(['id_post_category' => 999]));

        $response->assertSessionHasErrors('id_post_category');
    }

    public function test_tipe_postingan_tidak_valid()
    {
        $response = $this->actingAs($this->admin)->post('/admin/posts/store', $this->dataPost(['post_type' => 'video']));

        $response->assertSessionHasErrors('post_type');
    }

    public function test_status_postingan_tidak_valid()
    {
        $response = $this->actingAs($this->admin)->post('/admin/posts/store', $this->dataPost(['status' => 'terbit']));

        $response->assertSessionHasErrors('status');
    }

    public function test_meta_description_lebih_dari_160_karakter()
    {
        $response = $this->actingAs($this->admin)->post('/admin/posts/store', $this->dataPost(['meta_description' => str_repeat('a', 161)]));

        $response->assertSessionHasErrors('meta_description');
    }

    // TC-ANNMGMT-005, batas di kode 150 karakter (TC meminta 255)
    public function test_judul_tepat_150_karakter_diterima()
    {
        $response = $this->actingAs($this->admin)->post('/admin/posts/store', $this->dataPost(['title' => str_repeat('j', 150)]));

        $response->assertSessionHasNoErrors();
    }

    // TC-ANNMGMT-006
    public function test_judul_151_karakter_ditolak()
    {
        $response = $this->actingAs($this->admin)->post('/admin/posts/store', $this->dataPost(['title' => str_repeat('j', 151)]));

        $response->assertSessionHasErrors('title');
    }

    // TC-PROKER-005
    public function test_foto_postingan_4000kb_diterima_dan_tersimpan()
    {
        $this->actingAs($this->admin)->post('/admin/posts/store', $this->dataPost(['featured_image' => $this->gambarPalsu('foto.jpg', 4000)]));

        Storage::disk('public')->assertExists(Post::first()->featured_image_path);
    }

    // TC-PROKER-006
    public function test_foto_postingan_lebih_dari_4mb_ditolak()
    {
        $response = $this->actingAs($this->admin)->post('/admin/posts/store', $this->dataPost(['featured_image' => $this->gambarPalsu('foto.jpg', 4500)]));

        $response->assertSessionHasErrors('featured_image');
    }

    public function test_gambar_gallery_tersimpan_dengan_deskripsi()
    {
        $this->actingAs($this->admin)->post('/admin/posts/store', $this->dataPost([
            'gallery_images' => [$this->gambarPalsu('g1.jpg', 100), $this->gambarPalsu('g2.jpg', 100)],
            'gallery_descriptions' => ['Foto pertama', 'Foto kedua'],
        ]));

        $this->assertEquals(2, PostGallery::count());
        $this->assertDatabaseHas('post_gallery', ['description' => 'Foto kedua']);
    }

    // TC-LOG-001
    public function test_tambah_postingan_tercatat_di_log()
    {
        $this->actingAs($this->admin)->post('/admin/posts/store', $this->dataPost());

        $this->assertDatabaseHas('activity_log', ['action' => 'create', 'model_type' => 'Post']);
    }

    // TC-LOG-005
    public function test_validasi_gagal_tidak_membuat_log()
    {
        $this->actingAs($this->admin)->post('/admin/posts/store', []);

        $this->assertDatabaseMissing('activity_log', ['action' => 'create']);
    }

    // ---- edit() ----
    public function test_admin_melihat_form_edit_postingan()
    {
        $post = $this->postBaru();

        $response = $this->actingAs($this->admin)->get('/admin/posts/edit/' . $post->id_post);

        $response->assertStatus(200);
    }

    public function test_edit_postingan_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->get('/admin/posts/edit/999');

        $response->assertStatus(404);
    }

    // ---- update() ----
    // TC-PROKER-002
    public function test_admin_mengubah_postingan()
    {
        $post = $this->postBaru();

        $response = $this->actingAs($this->admin)->put('/admin/posts/update/' . $post->id_post, $this->dataPost(['title' => 'Judul Sudah Diubah', 'status' => 'draft']));

        $response->assertRedirect(route('admin.posts.index'));
        $this->assertDatabaseHas('posts', ['id_post' => $post->id_post, 'title' => 'Judul Sudah Diubah', 'status' => 'draft']);
    }

    public function test_update_postingan_dengan_judul_kosong()
    {
        $post = $this->postBaru();

        $response = $this->actingAs($this->admin)->put('/admin/posts/update/' . $post->id_post, $this->dataPost(['title' => '']));

        $response->assertSessionHasErrors('title');
    }

    public function test_ganti_foto_postingan_menghapus_foto_lama()
    {
        Storage::disk('public')->put('img/lama.jpg', 'x');
        $post = $this->postBaru(['featured_image_path' => 'img/lama.jpg']);

        $this->actingAs($this->admin)->put('/admin/posts/update/' . $post->id_post, $this->dataPost(['featured_image' => $this->gambarPalsu('baru.jpg', 100)]));

        Storage::disk('public')->assertMissing('img/lama.jpg');
    }

    public function test_update_halaman_diarahkan_ke_daftar_halaman()
    {
        $page = $this->postBaru(['post_type' => 'page']);

        $response = $this->actingAs($this->admin)->put('/admin/posts/update/' . $page->id_post, $this->dataPost(['post_type' => 'page']));

        $response->assertRedirect(route('admin.pages.list'));
    }

    // TC-LOG-002
    public function test_update_postingan_tercatat_di_log()
    {
        $post = $this->postBaru();

        $this->actingAs($this->admin)->put('/admin/posts/update/' . $post->id_post, $this->dataPost());

        $this->assertDatabaseHas('activity_log', ['action' => 'update', 'model_type' => 'Post']);
    }

    public function test_update_postingan_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->put('/admin/posts/update/999', $this->dataPost());

        $response->assertStatus(404);
    }

    // ---- deleteGallery() ----
    public function test_admin_menghapus_gambar_gallery()
    {
        Storage::disk('public')->put('gallery/g1.jpg', 'x');
        $post = $this->postBaru();
        $gambar = PostGallery::create(['id_post' => $post->id_post, 'image_path' => 'gallery/g1.jpg']);

        $response = $this->actingAs($this->admin)->delete('/admin/posts/gallery/' . $gambar->id_gallery);

        $response->assertRedirect(route('admin.posts.edit', $post->id_post));
        $this->assertDatabaseMissing('post_gallery', ['id_gallery' => $gambar->id_gallery]);
        Storage::disk('public')->assertMissing('gallery/g1.jpg');
    }

    public function test_hapus_gambar_gallery_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->delete('/admin/posts/gallery/999');

        $response->assertStatus(404);
    }

    // ---- destroy() ----
    // TC-SOFTDELETE-001, data masih ada di database
    public function test_hapus_postingan_memakai_soft_delete()
    {
        $post = $this->postBaru();

        $response = $this->actingAs($this->admin)->delete('/admin/posts/destroy/' . $post->id_post);

        $response->assertRedirect(route('admin.posts.index'));
        $this->assertSoftDeleted('posts', ['id_post' => $post->id_post]);
    }

    public function test_hapus_halaman_diarahkan_ke_daftar_halaman()
    {
        $page = $this->postBaru(['post_type' => 'page']);

        $response = $this->actingAs($this->admin)->delete('/admin/posts/destroy/' . $page->id_post);

        $response->assertRedirect(route('admin.pages.list'));
    }

    // TC-SOFTDELETE-004, komentar tetap ada dan tidak menyebabkan error
    public function test_hapus_postingan_yang_punya_komentar_tidak_error()
    {
        $post = $this->postBaru();
        $this->buatKomentar($post);
        $this->buatKomentar($post, ['email' => 'lain@mail.com']);

        $response = $this->actingAs($this->admin)->delete('/admin/posts/destroy/' . $post->id_post);

        $response->assertSessionHas('success');
        $this->assertDatabaseCount('comments', 2);
    }

    public function test_hapus_postingan_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->delete('/admin/posts/destroy/999');

        $response->assertStatus(404);
    }

    public function test_hapus_postingan_tercatat_di_log()
    {
        $post = $this->postBaru();

        $this->actingAs($this->admin)->delete('/admin/posts/destroy/' . $post->id_post);

        $this->assertDatabaseHas('activity_log', ['action' => 'delete', 'model_type' => 'Post']);
    }

    // ---- restore() ----
    public function test_admin_mengembalikan_postingan_dari_trash()
    {
        $post = $this->postBaru();
        $post->delete();

        $response = $this->actingAs($this->admin)->put('/admin/posts/restore/' . $post->id_post);

        $response->assertRedirect(route('admin.posts.index', ['trash' => 1]));
        $this->assertNotSoftDeleted('posts', ['id_post' => $post->id_post]);
    }

    public function test_restore_postingan_yang_tidak_ada_di_trash()
    {
        $post = $this->postBaru();

        $response = $this->actingAs($this->admin)->put('/admin/posts/restore/' . $post->id_post);

        $response->assertStatus(404);
    }

    // ---- forceDelete() ----
    public function test_hapus_permanen_postingan_dari_trash()
    {
        $post = $this->postBaru();
        $post->delete();

        $response = $this->actingAs($this->admin)->delete('/admin/posts/force-delete/' . $post->id_post);

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('posts', ['id_post' => $post->id_post]);
    }

    public function test_hapus_permanen_juga_menghapus_gallery_dan_file()
    {
        Storage::disk('public')->put('img/cover.jpg', 'x');
        $post = $this->postBaru(['featured_image_path' => 'img/cover.jpg']);
        PostGallery::create(['id_post' => $post->id_post, 'image_path' => 'gallery/a.jpg']);
        $post->delete();

        $this->actingAs($this->admin)->delete('/admin/posts/force-delete/' . $post->id_post);

        $this->assertDatabaseMissing('post_gallery', ['id_post' => $post->id_post]);
        Storage::disk('public')->assertMissing('img/cover.jpg');
    }

    public function test_hapus_permanen_postingan_yang_belum_masuk_trash()
    {
        $post = $this->postBaru();

        $response = $this->actingAs($this->admin)->delete('/admin/posts/force-delete/' . $post->id_post);

        $response->assertStatus(404);
        $this->assertDatabaseHas('posts', ['id_post' => $post->id_post]);
    }
}
