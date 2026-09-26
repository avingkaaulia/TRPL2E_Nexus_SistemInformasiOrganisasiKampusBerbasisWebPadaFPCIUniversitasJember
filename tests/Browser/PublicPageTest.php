<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PublicPageTest extends DuskTestCase
{
    public function test_public_pages()
    {
        $this->browse(function (Browser $browser) {

            // Home
            $browser
                ->visit('/')
                ->assertPathIs('/')
                ->pause(1000);

            // About
            $browser
                ->visit('/about')
                ->assertPathIs('/about')
                ->pause(1000);

            // Writings
            $browser
                ->visit('/writings')
                ->assertPathIs('/writings')
                ->pause(1000);

            // Semua Writings
            $browser
                ->visit('/writings/all')
                ->assertPathIs('/writings/all')
                ->pause(1000);

            // Events
            $browser
                ->visit('/events')
                ->assertPathIs('/events')
                ->pause(1000);

            // Contact
            $browser
                ->visit('/contact')
                ->assertPathIs('/contact')
                ->pause(1000);

            // Pages
            $browser
                ->visit('/post')
                ->assertPathIs('/post')
                ->pause(1000);

            // Login
            $browser
                ->visit('/login')
                ->assertPathIs('/login')
                ->pause(1000);

            // Forgot Password
            $browser
                ->visit('/forgot-password')
                ->assertPathIs('/forgot-password')
                ->pause(1000);

            // Pendaftaran
            $browser
                ->visit('/pendaftaran')
                ->assertPathIs('/pendaftaran')
                ->pause(1000);
        });
    }
}