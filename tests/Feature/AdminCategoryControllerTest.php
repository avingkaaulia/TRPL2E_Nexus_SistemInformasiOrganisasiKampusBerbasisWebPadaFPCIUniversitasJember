<?php

namespace Tests\Feature;

use App\Models\PostCategory;
use Tests\DatabaseTestCase;

class AdminCategoryControllerTest extends DatabaseTestCase
{
    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->buatAdmin();
    }

    // ---- index() ----
    public function test_admin_melihat_daftar_kategori()
    {
        $this->buatKategori('writings');

        $response = $this->actingAs($this->admin)->get('/admin/categories');

        $response->assertStatus(200);
        $response->assertViewIs('admin.categories.index');
    }

    public function test_daftar_kategori_dibentuk_menjadi_pohon_parent_dan_anak()
    {
        $induk = $this->buatKategori('writings');
        $this->buatKategori('opini', $induk->id_category);

        $response = $this->actingAs($this->admin)->get('/admin/categories');

        $response->assertViewHas('categoryTree', function ($tree) {
            return count($tree) == 1 && count($tree[0]->children) == 1;
        });
    }

    public function test_total_post_pada_pohon_kategori_dijumlah_dengan_sub_kategori()
    {
        $induk = $this->buatKategori('writings');
        $anak = $this->buatKategori('opini', $induk->id_category);
        $this->buatPost(['id_post_category' => $anak->id_category]);

        $response = $this->actingAs($this->admin)->get('/admin/categories');

        $response->assertViewHas('categoryTree', function ($tree) {
            return $tree[0]->total_posts == 1;
        });
    }

    // ---- create() ----
    public function test_admin_melihat_form_tambah_kategori()
    {
        $response = $this->actingAs($this->admin)->get('/admin/categories/create');

        $response->assertStatus(200);
    }

    public function test_form_tambah_kategori_membawa_daftar_kategori_untuk_pilihan_parent()
    {
        $this->buatKategori('writings');

        $response = $this->actingAs($this->admin)->get('/admin/categories/create');

        $response->assertViewHas('categories', function ($c) {
            return $c->count() == 1;
        });
    }

    // ---- store() ----
    // TC-KATEGORI-001
    public function test_admin_menambah_kategori()
    {
        $response = $this->actingAs($this->admin)->post('/admin/categories', ['category_name' => 'Technology']);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('post_category', ['category_name' => 'Technology']);
    }

    public function test_tambah_kategori_dengan_parent()
    {
        $induk = $this->buatKategori('writings');

        $this->actingAs($this->admin)->post('/admin/categories', ['category_name' => 'Opini', 'parent_id' => $induk->id_category]);

        $this->assertDatabaseHas('post_category', ['category_name' => 'Opini', 'parent_id' => $induk->id_category]);
    }

    // TC-KATEGORI-004
    public function test_tambah_kategori_tanpa_nama()
    {
        $response = $this->actingAs($this->admin)->post('/admin/categories', ['category_name' => '']);

        $response->assertSessionHasErrors('category_name');
    }

    // TC-KATEGORI-005
    public function test_tambah_kategori_dengan_nama_yang_sudah_ada()
    {
        $this->buatKategori('Technology');

        $response = $this->actingAs($this->admin)->post('/admin/categories', ['category_name' => 'Technology']);

        $response->assertSessionHasErrors('category_name');
        $this->assertEquals(1, PostCategory::where('category_name', 'Technology')->count());
    }

    // TC-KATEGORI-007, batas di kode 50 karakter (TC meminta 255)
    public function test_nama_kategori_tepat_50_karakter_diterima()
    {
        $response = $this->actingAs($this->admin)->post('/admin/categories', ['category_name' => str_repeat('k', 50)]);

        $response->assertSessionHasNoErrors();
    }

    // TC-KATEGORI-008
    public function test_nama_kategori_lebih_dari_50_karakter_ditolak()
    {
        $response = $this->actingAs($this->admin)->post('/admin/categories', ['category_name' => str_repeat('k', 51)]);

        $response->assertSessionHasErrors('category_name');
    }

    public function test_tambah_kategori_dengan_parent_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->post('/admin/categories', ['category_name' => 'Baru', 'parent_id' => 999]);

        $response->assertSessionHasErrors('parent_id');
    }

    public function test_tambah_kategori_tercatat_di_log()
    {
        $this->actingAs($this->admin)->post('/admin/categories', ['category_name' => 'Technology']);

        $this->assertDatabaseHas('activity_log', ['action' => 'create', 'model_type' => 'PostCategory']);
    }

    // ---- edit() ----
    public function test_admin_melihat_form_edit_kategori()
    {
        $kategori = $this->buatKategori('Technology');

        $response = $this->actingAs($this->admin)->get('/admin/categories/' . $kategori->id_category . '/edit');

        $response->assertStatus(200);
    }

    public function test_form_edit_tidak_menawarkan_kategori_itu_sendiri_sebagai_parent()
    {
        $kategori = $this->buatKategori('Technology');
        $this->buatKategori('Lain');

        $response = $this->actingAs($this->admin)->get('/admin/categories/' . $kategori->id_category . '/edit');

        $response->assertViewHas('categories', function ($c) {
            return $c->count() == 1;
        });
    }

    public function test_edit_kategori_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->get('/admin/categories/999/edit');

        $response->assertStatus(404);
    }

    // ---- update() ----
    public function test_admin_mengubah_nama_kategori()
    {
        $kategori = $this->buatKategori('Lama');

        $response = $this->actingAs($this->admin)->put('/admin/categories/' . $kategori->id_category, ['category_name' => 'Baru']);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('post_category', ['id_category' => $kategori->id_category, 'category_name' => 'Baru']);
    }

    public function test_update_kategori_dengan_nama_sendiri_tidak_dianggap_duplikat()
    {
        $kategori = $this->buatKategori('Sama');

        $response = $this->actingAs($this->admin)->put('/admin/categories/' . $kategori->id_category, ['category_name' => 'Sama']);

        $response->assertSessionHasNoErrors();
    }

    public function test_update_kategori_dengan_nama_milik_kategori_lain()
    {
        $this->buatKategori('Teknologi');
        $kategori = $this->buatKategori('Politik');

        $response = $this->actingAs($this->admin)->put('/admin/categories/' . $kategori->id_category, ['category_name' => 'Teknologi']);

        $response->assertSessionHasErrors('category_name');
    }

    public function test_kategori_tidak_boleh_jadi_parent_dirinya_sendiri()
    {
        $kategori = $this->buatKategori('Politik');

        $response = $this->actingAs($this->admin)->put('/admin/categories/' . $kategori->id_category, [
            'category_name' => 'Politik',
            'parent_id' => $kategori->id_category,
        ]);

        $response->assertSessionHas('error', 'Kategori tidak bisa menjadi parent untuk dirinya sendiri!');
    }

    public function test_update_kategori_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->put('/admin/categories/999', ['category_name' => 'x']);

        $response->assertStatus(404);
    }

    // ---- destroy() ----
    // TC-KATEGORI-003
    public function test_admin_menghapus_kategori_yang_belum_dipakai()
    {
        $kategori = $this->buatKategori('Sementara');

        $response = $this->actingAs($this->admin)->delete('/admin/categories/' . $kategori->id_category);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseMissing('post_category', ['id_category' => $kategori->id_category]);
    }

    public function test_kategori_yang_punya_sub_kategori_tidak_bisa_dihapus()
    {
        $induk = $this->buatKategori('writings');
        $this->buatKategori('opini', $induk->id_category);

        $response = $this->actingAs($this->admin)->delete('/admin/categories/' . $induk->id_category);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('post_category', ['id_category' => $induk->id_category]);
    }

    // TC-KATEGORI-006
    public function test_kategori_yang_dipakai_postingan_tidak_bisa_dihapus()
    {
        $kategori = $this->buatKategori('event');
        $this->buatPost(['id_post_category' => $kategori->id_category]);

        $response = $this->actingAs($this->admin)->delete('/admin/categories/' . $kategori->id_category);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('post_category', ['id_category' => $kategori->id_category]);
    }

    public function test_hapus_kategori_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->delete('/admin/categories/999');

        $response->assertStatus(404);
    }

    // TC-LOG-003
    public function test_hapus_kategori_tercatat_di_log()
    {
        $kategori = $this->buatKategori('Sementara');

        $this->actingAs($this->admin)->delete('/admin/categories/' . $kategori->id_category);

        $this->assertDatabaseHas('activity_log', ['action' => 'delete', 'model_type' => 'PostCategory']);
    }
}
