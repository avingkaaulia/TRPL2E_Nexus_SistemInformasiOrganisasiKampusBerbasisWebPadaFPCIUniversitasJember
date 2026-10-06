<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class EmailValidationTest extends TestCase
{
    public function test_email_dengan_format_valid(): void
    {
        $email = 'anggota@example.com';

        $this->assertMatchesRegularExpression(
            '/^[^@\s]+@[^@\s]+\.[^@\s]+$/',
            $email
        );
    }

    public function test_email_tanpa_at_tidak_valid(): void
    {
        $email = 'anggotaexample.com';

        $this->assertDoesNotMatchRegularExpression(
            '/^[^@\s]+@[^@\s]+\.[^@\s]+$/',
            $email
        );
    }
}