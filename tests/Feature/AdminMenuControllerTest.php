<?php

namespace Tests\Feature;

use App\Models\Menu;
use Tests\DatabaseTestCase;

class AdminMenuControllerTest extends DatabaseTestCase
{
    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->buatAdmin();
    }

    // ---- index() ----
    public function test_admin_melihat_daftar_menu()
    {
        $this->buatMenu('Home', 0, '/');

        $response = $this->actingAs($this->admin)->get('/admin/menu');

        $response->assertStatus(200);
        $response->assertViewIs('admin.menu.index');
    }

    public function test_daftar_menu_membawa_semua_menu()
    {
        $induk = $this->buatMenu('Tentang');
        $this->buatMenu('Sejarah', $induk->id_menu);

        $response = $this->actingAs($this->admin)->get('/admin/menu');

        $response->assertViewHas('menus', function ($m) {
            return $m->count() == 2;
        });
    }

    // ---- create() ----
    public function test_admin_melihat_form_tambah_menu()
    {
        $response = $this->actingAs($this->admin)->get('/admin/menu/create');

        $response->assertStatus(200);
    }

    public function test_form_tambah_menu_hanya_menawarkan_menu_utama_sebagai_parent()
    {
        $induk = $this->buatMenu('Tentang');
        $this->buatMenu('Sejarah', $induk->id_menu);

        $response = $this->actingAs($this->admin)->get('/admin/menu/create');

        $response->assertViewHas('parents', function ($p) {
            return $p->count() == 1;
        });
    }

    // ---- store() ----
    // TC-MENU-001
    public function test_admin_menambah_menu_navigasi()
    {
        $response = $this->actingAs($this->admin)->post('/admin/menu', ['menu_label' => 'Galeri', 'link' => '/galeri']);

        $response->assertRedirect(route('admin.menu.index'));
        $this->assertDatabaseHas('menu', ['menu_label' => 'Galeri', 'link' => '/galeri', 'id_menu_parent' => 0]);
    }

    public function test_tambah_menu_dengan_custom_link()
    {
        $this->actingAs($this->admin)->post('/admin/menu', [
            'menu_label' => 'Instagram', 'link' => 'custom', 'custom_link' => 'https://instagram.com/fpciunej',
        ]);

        $this->assertDatabaseHas('menu', ['menu_label' => 'Instagram', 'link' => 'https://instagram.com/fpciunej']);
    }

    public function test_tambah_menu_custom_link_dikosongkan()
    {
        $response = $this->actingAs($this->admin)->post('/admin/menu', ['menu_label' => 'Instagram', 'link' => 'custom']);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('menu', 0);
    }

    public function test_tambah_menu_custom_link_format_salah()
    {
        $response = $this->actingAs($this->admin)->post('/admin/menu', [
            'menu_label' => 'Aneh', 'link' => 'custom', 'custom_link' => 'bukan url',
        ]);

        $response->assertSessionHas('error');
    }

    // TC-MENU-004
    public function test_tambah_menu_tanpa_nama()
    {
        $response = $this->actingAs($this->admin)->post('/admin/menu', ['menu_label' => '', 'link' => '/galeri']);

        $response->assertSessionHasErrors('menu_label');
    }

    // TC-MENU-006, batas di kode 100 karakter (TC meminta 255)
    public function test_nama_menu_101_karakter_ditolak()
    {
        $response = $this->actingAs($this->admin)->post('/admin/menu', ['menu_label' => str_repeat('m', 101), 'link' => '/x']);

        $response->assertSessionHasErrors('menu_label');
    }

    public function test_nama_menu_tepat_100_karakter_diterima()
    {
        $this->actingAs($this->admin)->post('/admin/menu', ['menu_label' => str_repeat('m', 100), 'link' => '/x']);

        $this->assertDatabaseCount('menu', 1);
    }

    public function test_tambah_menu_dengan_nama_yang_sudah_ada()
    {
        $this->buatMenu('Galeri');

        $response = $this->actingAs($this->admin)->post('/admin/menu', ['menu_label' => 'Galeri', 'link' => '/galeri']);

        $response->assertSessionHas('error');
        $this->assertEquals(1, Menu::where('menu_label', 'Galeri')->count());
    }

    public function test_tambah_menu_dengan_parent_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->post('/admin/menu', [
            'menu_label' => 'Anak', 'link' => '/anak', 'id_menu_parent' => 99,
        ]);

        $response->assertSessionHas('error');
    }

    // ---- edit() ----
    public function test_admin_melihat_form_edit_menu()
    {
        $menu = $this->buatMenu('Galeri');

        $response = $this->actingAs($this->admin)->get('/admin/menu/' . $menu->id_menu . '/edit');

        $response->assertStatus(200);
    }

    public function test_edit_menu_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->get('/admin/menu/99/edit');

        $response->assertStatus(404);
    }

    // ---- update() ----
    // TC-MENU-002
    public function test_admin_mengubah_nama_menu()
    {
        $menu = $this->buatMenu('Galeri');

        $response = $this->actingAs($this->admin)->put('/admin/menu/' . $menu->id_menu, ['menu_label' => 'Dokumentasi', 'link' => '/galeri']);

        $response->assertRedirect(route('admin.menu.index'));
        $this->assertDatabaseHas('menu', ['id_menu' => $menu->id_menu, 'menu_label' => 'Dokumentasi']);
    }

    public function test_update_menu_dengan_nama_milik_menu_lain()
    {
        $this->buatMenu('Home');
        $menu = $this->buatMenu('Galeri');

        $response = $this->actingAs($this->admin)->put('/admin/menu/' . $menu->id_menu, ['menu_label' => 'Home', 'link' => '/galeri']);

        $response->assertSessionHas('error');
    }

    // TC-MENU-005, urutan menu mengikuti id sehingga yang diubah adalah induknya
    public function test_update_menu_menjadi_sub_menu_dari_menu_lain()
    {
        $induk = $this->buatMenu('Tentang');
        $menu = $this->buatMenu('Galeri');

        $this->actingAs($this->admin)->put('/admin/menu/' . $menu->id_menu, [
            'menu_label' => 'Galeri', 'link' => '/galeri', 'id_menu_parent' => $induk->id_menu,
        ]);

        $this->assertDatabaseHas('menu', ['id_menu' => $menu->id_menu, 'id_menu_parent' => $induk->id_menu]);
    }

    public function test_update_menu_tanpa_link()
    {
        $menu = $this->buatMenu('Galeri');

        $response = $this->actingAs($this->admin)->put('/admin/menu/' . $menu->id_menu, ['menu_label' => 'Galeri', 'link' => '']);

        $response->assertSessionHasErrors('link');
    }

    public function test_update_menu_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->put('/admin/menu/99', ['menu_label' => 'x', 'link' => '/x']);

        $response->assertStatus(404);
    }

    // ---- destroy() ----
    // TC-MENU-003
    public function test_admin_menghapus_menu()
    {
        $menu = $this->buatMenu('Galeri');

        $response = $this->actingAs($this->admin)->delete('/admin/menu/' . $menu->id_menu);

        $response->assertRedirect(route('admin.menu.index'));
        $this->assertDatabaseMissing('menu', ['id_menu' => $menu->id_menu]);
    }

    public function test_menu_yang_punya_sub_menu_tidak_bisa_dihapus()
    {
        $induk = $this->buatMenu('Tentang');
        $this->buatMenu('Sejarah', $induk->id_menu);

        $response = $this->actingAs($this->admin)->delete('/admin/menu/' . $induk->id_menu);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('menu', ['id_menu' => $induk->id_menu]);
    }

    public function test_hapus_menu_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->delete('/admin/menu/99');

        $response->assertStatus(404);
    }
}
