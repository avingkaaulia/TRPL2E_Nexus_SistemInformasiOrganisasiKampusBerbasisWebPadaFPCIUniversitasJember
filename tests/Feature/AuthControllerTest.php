<?php

namespace Tests\Feature;

use Tests\DatabaseTestCase;

class AuthControllerTest extends DatabaseTestCase
{
    // ---- showLoginForm() ----
    public function test_melihat_halaman_login()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_halaman_login_memakai_view_auth_login()
    {
        $response = $this->get(route('login'));

        $response->assertViewIs('auth.login');
    }

    // TC-AUTH-006, di kode asli halaman login tidak mengalihkan user yang sudah login
    public function test_user_yang_sudah_login_tetap_bisa_membuka_halaman_login()
    {
        $user = $this->buatAnggotaUser();

        $response = $this->actingAs($user)->get('/login');

        $response->assertStatus(200);
    }

    // ---- login() ----
    // TC-AUTH-001
    public function test_admin_login_diarahkan_ke_dashboard()
    {
        $admin = $this->buatAdmin(['email' => 'admin@fpci.test']);

        $response = $this->post('/login', ['email' => 'admin@fpci.test', 'password' => 'password123']);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    // TC-AUTH-002
    public function test_anggota_login_diarahkan_ke_home()
    {
        $anggota = $this->buatAnggotaUser(['email' => 'anggota@fpci.test']);

        $response = $this->post('/login', ['email' => 'anggota@fpci.test', 'password' => 'password123']);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($anggota);
    }

    public function test_login_berhasil_menampilkan_pesan_selamat_datang()
    {
        $this->buatAnggotaUser(['email' => 'rina@fpci.test', 'nama' => 'Rina']);

        $response = $this->post('/login', ['email' => 'rina@fpci.test', 'password' => 'password123']);

        $response->assertSessionHas('success', 'Selamat datang Rina!');
    }

    // TC-AUTH-003
    public function test_login_dengan_password_salah()
    {
        $this->buatAnggotaUser(['email' => 'anggota@fpci.test']);

        $response = $this->post('/login', ['email' => 'anggota@fpci.test', 'password' => 'salahbanget']);

        $response->assertSessionHas('error', 'Email atau password salah!');
        $this->assertGuest();
    }

    public function test_login_dengan_email_yang_belum_terdaftar()
    {
        $response = $this->post('/login', ['email' => 'hantu@fpci.test', 'password' => 'password123']);

        $response->assertSessionHas('error', 'Email atau password salah!');
        $this->assertGuest();
    }

    // TC-AUTH-004
    public function test_login_tanpa_mengisi_email_dan_password()
    {
        $response = $this->post('/login', []);

        $response->assertSessionHasErrors(['email', 'password']);
    }

    public function test_login_dengan_password_kurang_dari_6_karakter()
    {
        $response = $this->post('/login', ['email' => 'anggota@fpci.test', 'password' => '12345']);

        $response->assertSessionHasErrors('password');
    }

    public function test_login_dengan_format_email_salah()
    {
        $response = $this->post('/login', ['email' => 'bukan-email', 'password' => 'password123']);

        $response->assertSessionHasErrors('email');
    }

    // TC-AUTH-005, spasi di awal dan akhir email dibuang oleh middleware TrimStrings
    public function test_login_dengan_spasi_di_awal_dan_akhir_email()
    {
        $anggota = $this->buatAnggotaUser(['email' => 'anggota@fpci.test']);

        $this->post('/login', ['email' => '  anggota@fpci.test  ', 'password' => 'password123']);

        $this->assertAuthenticatedAs($anggota);
    }

    public function test_login_tercatat_di_activity_log()
    {
        $admin = $this->buatAdmin(['email' => 'admin@fpci.test']);

        $this->post('/login', ['email' => 'admin@fpci.test', 'password' => 'password123']);

        $this->assertDatabaseHas('activity_log', ['action' => 'login', 'user_id' => $admin->id_user]);
    }

    // ---- logout() ----
    // TC-LOGOUT-001
    public function test_user_logout_kembali_ke_home()
    {
        $user = $this->buatAnggotaUser();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_logout_tercatat_di_activity_log()
    {
        $user = $this->buatAnggotaUser();

        $this->actingAs($user)->post('/logout');

        $this->assertDatabaseHas('activity_log', ['action' => 'logout', 'user_id' => $user->id_user]);
    }

    public function test_logout_tanpa_login_tidak_error()
    {
        $response = $this->post('/logout');

        $response->assertRedirect(route('home'));
    }

    // TC-LOGOUT-002
    public function test_setelah_logout_halaman_profile_diarahkan_ke_login()
    {
        $user = $this->buatAnggotaUser();
        $this->actingAs($user)->post('/logout');

        $response = $this->get('/profile');

        $response->assertRedirect(route('login'));
    }

    // TC-LOGOUT-004
    public function test_logout_lalu_login_dengan_akun_lain()
    {
        $pertama = $this->buatAnggotaUser();
        $kedua = $this->buatAdmin(['email' => 'kedua@fpci.test']);
        $this->actingAs($pertama)->post('/logout');

        $this->post('/login', ['email' => 'kedua@fpci.test', 'password' => 'password123']);

        $this->assertAuthenticatedAs($kedua);
    }
}
