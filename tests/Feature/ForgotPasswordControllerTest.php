<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\DatabaseTestCase;

class ForgotPasswordControllerTest extends DatabaseTestCase
{
    // ---- showForgotForm() ----
    public function test_melihat_halaman_lupa_password()
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_halaman_lupa_password_memakai_view_yang_benar()
    {
        $response = $this->get(route('password.request'));

        $response->assertViewIs('auth.forgot-password');
    }

    // ---- sendResetLink() ----
    // TC-RESET-001
    public function test_kirim_link_reset_dengan_email_terdaftar()
    {
        Mail::fake();
        $user = $this->buatAnggotaUser(['email' => 'anggota@fpci.test']);

        $response = $this->post('/forgot-password', ['email' => 'anggota@fpci.test']);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');
        $this->assertNotNull(User::find($user->id_user)->reset_token);
    }

    public function test_token_reset_berlaku_sekitar_60_menit()
    {
        Mail::fake();
        $user = $this->buatAnggotaUser(['email' => 'anggota@fpci.test']);

        $this->post('/forgot-password', ['email' => 'anggota@fpci.test']);

        $berlakuSampai = \Carbon\Carbon::parse(User::find($user->id_user)->reset_token_expires);
        $this->assertTrue($berlakuSampai->greaterThan(now()->addMinutes(59)));
        $this->assertTrue($berlakuSampai->lessThanOrEqualTo(now()->addMinutes(61)));
    }

    // TC-RESET-002
    public function test_kirim_link_reset_dengan_email_tidak_terdaftar()
    {
        $response = $this->post('/forgot-password', ['email' => 'hantu@fpci.test']);

        $response->assertSessionHasErrors('email');
    }

    public function test_kirim_link_reset_dengan_email_kosong()
    {
        $response = $this->post('/forgot-password', ['email' => '']);

        $response->assertSessionHasErrors('email');
    }

    public function test_kirim_link_reset_dengan_format_email_salah()
    {
        $response = $this->post('/forgot-password', ['email' => 'asal-ketik']);

        $response->assertSessionHasErrors('email');
    }

    // ---- showResetForm() ----
    public function test_form_reset_tampil_untuk_token_yang_valid()
    {
        $this->buatAnggotaUser(['reset_token' => 'tokenbagus', 'reset_token_expires' => now()->addMinutes(30)]);

        $response = $this->get('/reset-password/tokenbagus');

        $response->assertStatus(200);
        $response->assertViewHas('token', 'tokenbagus');
    }

    // TC-RESET-003
    public function test_form_reset_untuk_token_yang_tidak_dikenal()
    {
        $response = $this->get('/reset-password/tokenasal');

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
    }

    public function test_form_reset_untuk_token_yang_sudah_kedaluwarsa()
    {
        $this->buatAnggotaUser(['reset_token' => 'tokenlama', 'reset_token_expires' => now()->subMinute()]);

        $response = $this->get('/reset-password/tokenlama');

        $response->assertRedirect(route('login'));
    }

    // ---- resetPassword() ----
    public function test_reset_password_dengan_data_valid()
    {
        $user = $this->buatAnggotaUser(['reset_token' => 'tokenbagus', 'reset_token_expires' => now()->addMinutes(30)]);

        $response = $this->post('/reset-password', [
            'token' => 'tokenbagus',
            'password' => 'sandibaru99',
            'password_confirmation' => 'sandibaru99',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');
        $baru = User::find($user->id_user);
        $this->assertTrue(Hash::check('sandibaru99', $baru->password));
        $this->assertNull($baru->reset_token);
    }

    // TC-RESET-004
    public function test_reset_password_dengan_konfirmasi_tidak_sama()
    {
        $this->buatAnggotaUser(['reset_token' => 'tokenbagus', 'reset_token_expires' => now()->addMinutes(30)]);

        $response = $this->post('/reset-password', [
            'token' => 'tokenbagus',
            'password' => 'sandibaru99',
            'password_confirmation' => 'sandibeda99',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_reset_password_kurang_dari_6_karakter()
    {
        $this->buatAnggotaUser(['reset_token' => 'tokenbagus', 'reset_token_expires' => now()->addMinutes(30)]);

        $response = $this->post('/reset-password', [
            'token' => 'tokenbagus',
            'password' => '12345',
            'password_confirmation' => '12345',
        ]);

        $response->assertSessionHasErrors('password');
    }

    // TC-RESET-005, batas minimal di kode 6 karakter (di dokumen TC tertulis 8)
    public function test_reset_password_tepat_6_karakter_diterima()
    {
        $this->buatAnggotaUser(['reset_token' => 'tokenbagus', 'reset_token_expires' => now()->addMinutes(30)]);

        $response = $this->post('/reset-password', [
            'token' => 'tokenbagus',
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('login'));
    }

    public function test_reset_password_dengan_token_tidak_valid()
    {
        $response = $this->post('/reset-password', [
            'token' => 'tokenpalsu',
            'password' => 'sandibaru99',
            'password_confirmation' => 'sandibaru99',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
    }

    // TC-RESET-006
    public function test_token_reset_tidak_bisa_dipakai_dua_kali()
    {
        $user = $this->buatAnggotaUser(['reset_token' => 'sekalipakai', 'reset_token_expires' => now()->addMinutes(30)]);
        $data = ['token' => 'sekalipakai', 'password' => 'sandibaru99', 'password_confirmation' => 'sandibaru99'];
        $this->post('/reset-password', $data);

        $response = $this->post('/reset-password', array_merge($data, ['password' => 'sandikedua1', 'password_confirmation' => 'sandikedua1']));

        $response->assertSessionHas('error');
        $this->assertTrue(Hash::check('sandibaru99', User::find($user->id_user)->password));
    }
}
