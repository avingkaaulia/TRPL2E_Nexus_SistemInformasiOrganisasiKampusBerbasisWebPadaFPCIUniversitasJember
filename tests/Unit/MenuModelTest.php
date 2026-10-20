<?php

namespace Tests\Unit;

use App\Models\Menu;
use Tests\DatabaseTestCase;

class MenuModelTest extends DatabaseTestCase
{
    // ---- parent() ----
    public function test_sub_menu_punya_parent()
    {
        $induk = $this->buatMenu('Tentang');
        $anak = $this->buatMenu('Sejarah', $induk->id_menu);

        $this->assertEquals('Tentang', $anak->parent->menu_label);
    }

    public function test_menu_utama_tidak_punya_parent()
    {
        $menu = $this->buatMenu('Home', 0, '/');

        $this->assertNull($menu->parent);
    }

    public function test_parent_kosong_kalau_id_parent_tidak_ada()
    {
        $menu = $this->buatMenu('Yatim', 77);

        $this->assertNull($menu->parent);
    }

    // ---- children() ----
    public function test_menu_punya_beberapa_sub_menu()
    {
        $induk = $this->buatMenu('Kegiatan');
        $this->buatMenu('Event', $induk->id_menu);
        $this->buatMenu('Program', $induk->id_menu);

        $this->assertCount(2, $induk->children);
    }

    public function test_menu_tanpa_sub_menu()
    {
        $menu = $this->buatMenu('Contact');

        $this->assertCount(0, $menu->children);
    }

    public function test_children_hanya_milik_parent_yang_sama()
    {
        $a = $this->buatMenu('A');
        $b = $this->buatMenu('B');
        $this->buatMenu('anak B', $b->id_menu);

        $this->assertCount(0, $a->children);
    }

    // ---- getMainMenus() ----
    public function test_main_menus_hanya_menu_dengan_parent_nol()
    {
        $induk = $this->buatMenu('Tentang');
        $this->buatMenu('Sejarah', $induk->id_menu);
        $this->buatMenu('Contact');

        $this->assertCount(2, Menu::getMainMenus());
    }

    public function test_main_menus_urut_berdasarkan_id()
    {
        $pertama = $this->buatMenu('Home');
        $kedua = $this->buatMenu('About');

        $hasil = Menu::getMainMenus();

        $this->assertEquals($pertama->id_menu, $hasil->first()->id_menu);
        $this->assertEquals($kedua->id_menu, $hasil->last()->id_menu);
    }

    public function test_main_menus_kosong_kalau_belum_ada_menu()
    {
        $this->assertCount(0, Menu::getMainMenus());
    }

    // ---- getSubMenus() ----
    public function test_sub_menus_milik_parent_tertentu()
    {
        $induk = $this->buatMenu('Kegiatan');
        $this->buatMenu('Event', $induk->id_menu);
        $this->buatMenu('Program', $induk->id_menu);

        $this->assertCount(2, Menu::getSubMenus($induk->id_menu));
    }

    public function test_sub_menus_kosong_kalau_parent_tidak_punya_anak()
    {
        $induk = $this->buatMenu('Contact');

        $this->assertCount(0, Menu::getSubMenus($induk->id_menu));
    }

    public function test_sub_menus_tidak_mengambil_anak_parent_lain()
    {
        $a = $this->buatMenu('A');
        $b = $this->buatMenu('B');
        $this->buatMenu('anak A', $a->id_menu);

        $this->assertCount(0, Menu::getSubMenus($b->id_menu));
    }

    // ---- hasChildren() ----
    public function test_hasChildren_true_kalau_punya_sub_menu()
    {
        $induk = $this->buatMenu('Tentang');
        $this->buatMenu('Sejarah', $induk->id_menu);

        $this->assertTrue($induk->hasChildren());
    }

    public function test_hasChildren_false_kalau_tidak_punya_sub_menu()
    {
        $menu = $this->buatMenu('Contact');

        $this->assertFalse($menu->hasChildren());
    }

    public function test_hasChildren_false_untuk_sub_menu_yang_tidak_punya_cucu()
    {
        $induk = $this->buatMenu('Tentang');
        $anak = $this->buatMenu('Sejarah', $induk->id_menu);

        $this->assertFalse($anak->hasChildren());
    }
}
