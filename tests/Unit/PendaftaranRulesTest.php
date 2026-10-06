<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class PendaftaranRulesTest extends TestCase
{
    public function test_nim_yang_diawali_240_valid(): void
    {
        $nim = '240123456';

        $this->assertMatchesRegularExpression('/^240/', $nim);
    }

    public function test_nim_yang_tidak_diawali_240_tidak_valid(): void
    {
        $nim = '230123456';

        $this->assertDoesNotMatchRegularExpression('/^240/', $nim);
    }

    public function test_nim_kosong_tidak_memenuhi_format(): void
    {
        $nim = '';

        $this->assertDoesNotMatchRegularExpression('/^240/', $nim);
    }
}