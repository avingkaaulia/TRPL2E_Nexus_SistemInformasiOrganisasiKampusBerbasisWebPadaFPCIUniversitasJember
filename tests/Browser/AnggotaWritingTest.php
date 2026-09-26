<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AnggotaWritingTest extends DuskTestCase
{
    public function test_anggota_create_writing()
    {
        $this->browse(function (Browser $browser) {

            // Login sebagai anggota
            $browser
                ->resize(1366, 768)
                ->visit('/login')
                ->assertPathIs('/login')
                ->pause(1000)
                ->type('#email', 'Aa@gmail.com')
                ->type('#password', 'aulia123')
                ->click('.btn-login-submit')
                ->pause(2000);

            // Buka halaman Create Writing
            $browser
                ->visit('/writings/submit')
                ->assertPathIs('/writings/submit')
                ->pause(3000);

            // Isi judul
            $browser
                ->type('#title', 'Test Writing Otomatis Dusk');

            // Pilih kategori Foreign Policy (ID 8)
            $browser->script("
                const category = document.querySelector('#id_post_category');
                category.value = '8';
                category.dispatchEvent(new Event('change', { bubbles: true }));
            ");

            $browser->pause(1000);

            // Pastikan judul dan kategori sudah terisi
            $browser
                ->assertInputValue('#title', 'Test Writing Otomatis Dusk')
                ->assertSelected('#id_post_category', '8');

            // Isi TinyMCE
            $browser->script("
                tinymce.get('tiny-editor').setContent(
                    '<p>Ini adalah tulisan pengujian yang dibuat secara otomatis menggunakan Laravel Dusk. Tulisan ini digunakan untuk menguji proses submit writing sebagai anggota.</p>'
                );
            ");

            $browser->pause(1000);

            // Scroll ke tombol Submit
            $browser->script("
                document.querySelector('#submitBtn').scrollIntoView({
                    behavior: 'instant',
                    block: 'center'
                });
            ");

            $browser->pause(500);

            // Submit for Review
            $browser
                ->click('#submitBtn')
                ->pause(3000);
            
        });
    }
}