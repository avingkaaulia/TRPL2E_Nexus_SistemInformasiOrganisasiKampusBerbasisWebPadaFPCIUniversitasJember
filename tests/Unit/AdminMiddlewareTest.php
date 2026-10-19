<?php

namespace Tests\Unit;

use App\Http\Middleware\AdminMiddleware;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\DatabaseTestCase;

class AdminMiddlewareTest extends DatabaseTestCase
{
    private function jalankan()
    {
        $request = Request::create('/admin/dashboard', 'GET');

        return (new AdminMiddleware())->handle($request, function ($req) {
            return response('lolos');
        });
    }

    // TC-RBAC-001
    public function test_admin_dilewatkan_ke_request_berikutnya()
    {
        Auth::login($this->buatAdmin());

        $respon = $this->jalankan();

        $this->assertEquals('lolos', $respon->getContent());
    }

    // TC-RBAC-003
    public function test_anggota_dialihkan_ke_halaman_utama()
    {
        Auth::login($this->buatAnggotaUser());

        $respon = $this->jalankan();

        $this->assertInstanceOf(RedirectResponse::class, $respon);
        $this->assertEquals(url('/'), $respon->getTargetUrl());
    }

    // TC-RBAC-002
    public function test_pengunjung_belum_login_dialihkan_ke_halaman_utama()
    {
        $respon = $this->jalankan();

        $this->assertInstanceOf(RedirectResponse::class, $respon);
        $this->assertEquals(url('/'), $respon->getTargetUrl());
    }

    public function test_pesan_error_ikut_dikirim_saat_akses_ditolak()
    {
        Auth::login($this->buatAnggotaUser());

        $respon = $this->jalankan();

        $this->assertEquals('Akses ditolak! Anda bukan admin.', $respon->getSession()->get('error'));
    }
}
