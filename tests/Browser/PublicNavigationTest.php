<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PublicNavigationTest extends DuskTestCase
{
    public function test_home_to_about()
    {
        $this->browse(function (Browser $browser) {

            // Buka Home
            $browser
                ->visit('/')
                ->assertPathIs('/')
                ->pause(1000);

            // Scroll ke bawah
            $browser->script('window.scrollTo(0, document.body.scrollHeight);');
            $browser->pause(1000);

            // Klik Learn More
            $browser
                ->clickLink('Learn More')
                ->pause(2000);

            // Pastikan masuk ke About
            $browser->assertPathIs('/about');
        });
    }

    public function test_about_structure_organization_link()
    {
        $this->browse(function (Browser $browser) {

            // Buka About
            $browser
                ->visit('/about')
                ->assertPathIs('/about')
                ->pause(1000);

            // Scroll ke bagian bawah sampai struktur organisasi
            $browser->script('window.scrollTo(0, document.body.scrollHeight);');
            $browser->pause(1500);

            // Memastikan bagian Struktur Organisasi ada
            $browser
                ->assertSee('Struktur Organisasi');

            // Memastikan foto organisasi memiliki link
            $browser
                ->assertPresent('.anggota-foto-link');
        });
    }

    public function test_about_structure_instagram_link()
    {
        $this->browse(function (Browser $browser) {

            $browser
                ->visit('/about')
                ->assertPathIs('/about')
                ->pause(1000);

            // Scroll ke Struktur Organisasi
            $browser->script('window.scrollTo(0, document.body.scrollHeight);');
            $browser->pause(1500);

            // Ambil link dari foto anggota pertama
            $links = $browser->attribute('.anggota-foto-link', 'href');

            // Pastikan link tersedia
            $this->assertNotNull($links);
            $this->assertNotEmpty($links);

            // Pastikan link mengarah ke Instagram
            $this->assertStringContainsString('instagram.com', $links);
        });
    }

    public function test_home_to_writings()
    {
        $this->browse(function (Browser $browser) {

            // Buka Home
            $browser
                ->visit('/')
                ->assertPathIs('/')
                ->pause(1000);

            // Scroll ke bagian Latest Writings
            $browser->script('window.scrollTo(0, 700);');
            $browser->pause(1000);

            // Pastikan ada kartu tulisan
            $browser->assertPresent('.home-latest-grid a');

            // Klik tulisan pertama
            $browser
                ->click('.home-latest-grid a')
                ->pause(2000);

            // Pastikan masuk halaman detail post
            $browser->assertPathBeginsWith('/post/');
        });
    }

    public function test_writings_category_navigation()
    {
        $this->browse(function (Browser $browser) {

            $browser
                ->resize(1366, 768)
                ->visit('/writings')
                ->assertPathIs('/writings')
                ->pause(3000);

            // Scroll ke bagian kategori
            $browser->script('window.scrollTo(0, 700);');
            $browser->pause(1000);

            // Pastikan kategori tersedia
            $browser->assertPresent('.category-grid a');

            // Klik kategori pertama
            $browser
                ->click('.category-grid a')
                ->pause(2000);

            // Pastikan masuk halaman kategori
            $browser->assertPathBeginsWith('/writings/category/');
        });
    }

    public function test_writings_category_view_all()
    {
        $this->browse(function (Browser $browser) {

            $browser
                ->resize(1366, 768)
                ->visit('/writings')
                ->assertPathIs('/writings')
                ->pause(1500);

            // Scroll ke kategori
            $browser->script('window.scrollTo(0, 700);');
            $browser->pause(1000);

            // Klik kategori pertama
            $browser
                ->click('.category-grid a')
                ->pause(2000);

            // Pastikan informasi kategori muncul
            $browser
                ->assertSee('Showing:')
                ->assertSee('View all');

            // Klik View all
            $browser
                ->clickLink('View all →')
                ->pause(2000);

            // Pastikan kembali ke halaman Writings
            $browser->assertPathIs('/writings');
        });
    }

    public function test_writings_hot_topic_to_detail()
    {
        $this->browse(function (Browser $browser) {

            $browser
                ->resize(1366, 768)
                ->visit('/writings')
                ->assertPathIs('/writings')
                ->pause(1500);

            // Scroll ke bagian Hot Topics
            $browser->script('window.scrollTo(0, 900);');
            $browser->pause(1000);

            // Pastikan Hot Topics ada
            $browser
                ->assertSee('Our Hot Topics Of Writings')
                ->assertPresent('.hot-topics a');

            // Klik Hot Topic pertama
            $browser
                ->click('.hot-topics a')
                ->pause(2000);

            // Pastikan masuk detail tulisan
            $browser->assertPathBeginsWith('/writings/');
        });
    }

    public function test_writings_read_more_to_detail()
    {
        $this->browse(function (Browser $browser) {

            $browser
                ->resize(1366, 768)
                ->visit('/writings')
                ->assertPathIs('/writings')
                ->pause(1500);

            // Scroll ke daftar tulisan
            $browser->script('window.scrollTo(0, 1300);');
            $browser->pause(1000);

            // Pastikan ada tulisan
            $browser->assertPresent('.writing-card');

            // Klik tulisan pertama
            $browser
                ->click('.writing-card')
                ->pause(2000);

            // Pastikan masuk detail tulisan
            $browser->assertPathBeginsWith('/writings/');
        });
    }

    public function test_writings_view_all()
    {
        $this->browse(function (Browser $browser) {

            $browser
                ->resize(1366, 768)
                ->visit('/writings')
                ->assertPathIs('/writings')
                ->pause(1500);

            // Cari tombol View All Writings
            $browser->assertPresent('a[href*="/writings/all"]');

            // Scroll langsung ke tombol View All Writings
            $browser->script("
                document.querySelector('a[href*=\"/writings/all\"]')
                    .scrollIntoView({block: 'center'});
            ");
            $browser->pause(1000);

            // Klik tombol
            $browser
                ->click('a[href*="/writings/all"]')
                ->pause(2000);

            // Pastikan masuk halaman semua writings
            $browser->assertPathIs('/writings/all');
        });
    }

    public function test_writings_create_requires_login()
    {
        $this->browse(function (Browser $browser) {

            $browser
                ->resize(1366, 768)
                ->visit('/writings')
                ->assertPathIs('/writings')
                ->pause(1500);

            // Scroll ke bagian Create Your Own
            $browser->script("
                document.querySelector('.create-own-section')
                    .scrollIntoView({block: 'center'});
            ");
            $browser->pause(1000);

            // Pastikan tombol Login pada bagian Create Your Own tersedia
            $browser->assertPresent('.create-own-section a[href*="/login"]');

            // Klik tombol Login
            $browser
                ->click('.create-own-section a[href*="/login"]')
                ->pause(1500);

            // Pastikan masuk halaman login
            $browser->assertPathIs('/login');
        });
    }
}