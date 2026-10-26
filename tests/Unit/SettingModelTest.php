<?php

namespace Tests\Unit;

use App\Models\Setting;
use Tests\DatabaseTestCase;

class SettingModelTest extends DatabaseTestCase
{
    // ---- get() ----
    public function test_get_mengembalikan_nilai_yang_tersimpan()
    {
        Setting::set('site_logo', 'storage/settings/logo.png', 'image');

        $this->assertEquals('storage/settings/logo.png', Setting::get('site_logo'));
    }

    public function test_get_mengembalikan_default_kalau_key_tidak_ada()
    {
        $this->assertEquals('assets/img/logo.png', Setting::get('site_logo', 'assets/img/logo.png'));
    }

    public function test_get_tanpa_default_mengembalikan_null()
    {
        $this->assertNull(Setting::get('key_yang_tidak_ada'));
    }

    // ---- set() ----
    public function test_set_membuat_setting_baru()
    {
        Setting::set('nama_situs', 'FPCI UNEJ');

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'nama_situs',
            'setting_value' => 'FPCI UNEJ',
        ]);
    }

    public function test_set_dua_kali_untuk_key_sama_hanya_mengupdate()
    {
        Setting::set('nama_situs', 'Lama');
        Setting::set('nama_situs', 'Baru');

        $this->assertEquals(1, Setting::where('setting_key', 'nama_situs')->count());
        $this->assertEquals('Baru', Setting::get('nama_situs'));
    }

    public function test_set_menyimpan_tipe_setting()
    {
        Setting::set('site_favicon', 'storage/settings/fav.ico', 'image');

        $this->assertDatabaseHas('settings', ['setting_key' => 'site_favicon', 'setting_type' => 'image']);
    }

    // ---- isCommentsEnabled() ----
    public function test_komentar_aktif_secara_default_kalau_setting_belum_ada()
    {
        $this->assertTrue(Setting::isCommentsEnabled());
    }

    public function test_komentar_nonaktif_kalau_nilainya_false()
    {
        Setting::set('comments_enabled', 'false', 'boolean');

        $this->assertFalse(Setting::isCommentsEnabled());
    }

    public function test_komentar_aktif_kalau_nilainya_angka_1()
    {
        Setting::set('comments_enabled', '1', 'boolean');

        $this->assertTrue(Setting::isCommentsEnabled());
    }

    public function test_komentar_nonaktif_kalau_nilainya_angka_0()
    {
        Setting::set('comments_enabled', '0', 'boolean');

        $this->assertFalse(Setting::isCommentsEnabled());
    }

    // ---- toggleComments() ----
    public function test_toggle_komentar_ke_true_menyimpan_string_true()
    {
        Setting::toggleComments(true);

        $this->assertEquals('true', Setting::get('comments_enabled'));
    }

    public function test_toggle_komentar_ke_false_menyimpan_string_false()
    {
        Setting::toggleComments(false);

        $this->assertEquals('false', Setting::get('comments_enabled'));
        $this->assertFalse(Setting::isCommentsEnabled());
    }

    public function test_toggle_komentar_mengembalikan_objek_setting()
    {
        $hasil = Setting::toggleComments(true);

        $this->assertInstanceOf(Setting::class, $hasil);
    }
}
