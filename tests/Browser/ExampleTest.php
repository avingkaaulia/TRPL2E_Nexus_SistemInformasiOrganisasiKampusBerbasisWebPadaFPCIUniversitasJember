<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ExampleTest extends DuskTestCase
{
    /**
     * Test halaman Home dapat dibuka.
     */
    public function testHomePageCanBeOpened(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/');
        });
    }
}
