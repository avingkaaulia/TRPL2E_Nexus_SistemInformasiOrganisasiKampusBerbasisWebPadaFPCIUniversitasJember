<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\File;
use Tests\DatabaseTestCase;

class HelpersTest extends DatabaseTestCase
{
    // ---- getImageUrl() ----
    public function test_getImageUrl_path_null_pakai_gambar_default()
    {
        $this->assertEquals(asset('assets/img/default-image.jpg'), getImageUrl(null));
    }

    public function test_getImageUrl_path_kosong_pakai_gambar_default()
    {
        $this->assertEquals(asset('assets/img/default-image.jpg'), getImageUrl(''));
    }

    public function test_getImageUrl_file_tidak_ditemukan_pakai_gambar_default()
    {
        $this->assertEquals(asset('assets/img/default-image.jpg'), getImageUrl('img/tidak-ada-file-ini.jpg'));
    }

    public function test_getImageUrl_file_ada_di_folder_public()
    {
        // favicon.ico memang ada di folder public
        $this->assertEquals(asset('favicon.ico'), getImageUrl('favicon.ico'));
    }

    public function test_getImageUrl_file_ada_di_storage()
    {
        $folder = storage_path('app/public/uji');
        File::ensureDirectoryExists($folder);
        File::put($folder . '/contoh.jpg', 'isi');

        $hasil = getImageUrl('uji/contoh.jpg');

        File::deleteDirectory($folder);
        $this->assertEquals(asset('storage/uji/contoh.jpg'), $hasil);
    }
}
