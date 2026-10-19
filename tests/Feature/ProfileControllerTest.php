<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\DatabaseTestCase;

class ProfileControllerTest extends DatabaseTestCase
{
    // ---- index() ----
    public function test_user_belum_login_tidak_bisa_melihat_profile()
    {
        $response = $this->get('/profile');

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_user_login_melihat_halaman_profile()
    {
        $user = $this->buatAnggotaUser();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);
    }

    public function test_halaman_profile_membawa_data_user_yang_login()
    {
        $user = $this->buatAnggotaUser(['nama' => 'Martha Dwi']);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertViewHas('user', function ($u) {
            return $u->nama == 'Martha Dwi';
        });
    }

    // ---- update() ----
    // TC-MYPROFILE-001
    public function test_user_update_data_diri_dengan_data_valid()
    {
        $user = $this->buatAnggotaUser();

        $response = $this->actingAs($user)->put('/profile', [
            'nama' => 'Nama Baru',
            'username' => 'usernamebaru',
            'email' => 'baru@fpci.test',
        ]);

        $response->assertSessionHas('success', 'Profil berhasil diupdate');
        $this->assertDatabaseHas('users', ['id_user' => $user->id_user, 'nama' => 'Nama Baru', 'email' => 'baru@fpci.test']);
    }

    // TC-MYPROFILE-002
    public function test_update_profile_dengan_nama_kosong()
    {
        $user = $this->buatAnggotaUser();

        $response = $this->actingAs($user)->put('/profile', [
            'nama' => '',
            'username' => 'usernamebaru',
            'email' => 'baru@fpci.test',
        ]);

        $response->assertSessionHasErrors('nama');
    }

    // TC-MYPROFILE-004
    public function test_update_profile_dengan_email_milik_user_lain()
    {
        $this->buatAnggotaUser(['email' => 'dipakai@fpci.test']);
        $user = $this->buatAnggotaUser();

        $response = $this->actingAs($user)->put('/profile', [
            'nama' => $user->nama,
            'username' => $user->username,
            'email' => 'dipakai@fpci.test',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_update_profile_dengan_username_milik_user_lain()
    {
        $this->buatAnggotaUser(['username' => 'sudahada']);
        $user = $this->buatAnggotaUser();

        $response = $this->actingAs($user)->put('/profile', [
            'nama' => $user->nama,
            'username' => 'sudahada',
            'email' => $user->email,
        ]);

        $response->assertSessionHasErrors('username');
    }

    // TC-MYPROFILE-006
    public function test_simpan_profile_tanpa_perubahan_tetap_berhasil()
    {
        $user = $this->buatAnggotaUser();

        $response = $this->actingAs($user)->put('/profile', [
            'nama' => $user->nama,
            'username' => $user->username,
            'email' => $user->email,
        ]);

        $response->assertSessionHas('success');
        $response->assertSessionHasNoErrors();
    }

    // batas di kode 100 karakter (TC-MYPROFILE-005 meminta 255)
    public function test_nama_101_karakter_ditolak()
    {
        $user = $this->buatAnggotaUser();

        $response = $this->actingAs($user)->put('/profile', [
            'nama' => str_repeat('a', 101),
            'username' => $user->username,
            'email' => $user->email,
        ]);

        $response->assertSessionHasErrors('nama');
    }

    public function test_nama_tepat_100_karakter_diterima()
    {
        $user = $this->buatAnggotaUser();

        $response = $this->actingAs($user)->put('/profile', [
            'nama' => str_repeat('a', 100),
            'username' => $user->username,
            'email' => $user->email,
        ]);

        $response->assertSessionHasNoErrors();
    }

    public function test_user_mengganti_password_dengan_benar()
    {
        $user = $this->buatAnggotaUser();

        $this->actingAs($user)->put('/profile', [
            'nama' => $user->nama,
            'username' => $user->username,
            'email' => $user->email,
            'current_password' => 'password123',
            'password' => 'passwordbaru1',
            'password_confirmation' => 'passwordbaru1',
        ]);

        $this->assertTrue(Hash::check('passwordbaru1', User::find($user->id_user)->password));
    }

    public function test_ganti_password_dengan_password_lama_salah()
    {
        $user = $this->buatAnggotaUser();

        $response = $this->actingAs($user)->put('/profile', [
            'nama' => $user->nama,
            'username' => $user->username,
            'email' => $user->email,
            'current_password' => 'passwordngawur',
            'password' => 'passwordbaru1',
            'password_confirmation' => 'passwordbaru1',
        ]);

        $response->assertSessionHas('error', 'Password saat ini tidak sesuai!');
    }

    public function test_ganti_password_dengan_konfirmasi_tidak_sama()
    {
        $user = $this->buatAnggotaUser();

        $response = $this->actingAs($user)->put('/profile', [
            'nama' => $user->nama,
            'username' => $user->username,
            'email' => $user->email,
            'current_password' => 'password123',
            'password' => 'passwordbaru1',
            'password_confirmation' => 'beda-sendiri',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_password_baru_tidak_boleh_sama_dengan_password_lama()
    {
        $user = $this->buatAnggotaUser();

        $response = $this->actingAs($user)->put('/profile', [
            'nama' => $user->nama,
            'username' => $user->username,
            'email' => $user->email,
            'current_password' => 'password123',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHas('error', 'Password baru tidak boleh sama dengan password saat ini!');
    }

    public function test_update_profile_tanpa_login_diarahkan_ke_login()
    {
        $response = $this->put('/profile', ['nama' => 'x']);

        $response->assertRedirect(route('login'));
    }
}
