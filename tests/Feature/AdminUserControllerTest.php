<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\DatabaseTestCase;

class AdminUserControllerTest extends DatabaseTestCase
{
    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->buatAdmin();
    }

    private function dataUser($user, array $ganti = [])
    {
        return array_merge([
            'nama' => $user->nama,
            'username' => $user->username,
            'email' => $user->email,
            'id_role' => $user->id_role,
        ], $ganti);
    }

    // ---- index() ----
    public function test_admin_melihat_daftar_user()
    {
        $response = $this->actingAs($this->admin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
    }

    public function test_daftar_user_bisa_dicari_berdasarkan_nama()
    {
        $this->buatAnggotaUser(['nama' => 'Zainal Arifin']);
        $this->buatAnggotaUser(['nama' => 'Bunga Citra']);

        $response = $this->actingAs($this->admin)->get('/admin/users?search=Zainal');

        $response->assertViewHas('users', function ($u) {
            return $u->total() == 1;
        });
    }

    public function test_daftar_user_bisa_difilter_berdasarkan_role()
    {
        $this->buatAnggotaUser();
        $this->buatAnggotaUser();

        $response = $this->actingAs($this->admin)->get('/admin/users?role=2');

        $response->assertViewHas('users', function ($u) {
            return $u->total() == 2;
        });
    }

    // ---- edit() ----
    public function test_admin_melihat_form_edit_user()
    {
        $user = $this->buatAnggotaUser();

        $response = $this->actingAs($this->admin)->get('/admin/users/' . $user->id_user . '/edit');

        $response->assertStatus(200);
    }

    public function test_edit_user_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->get('/admin/users/999/edit');

        $response->assertStatus(404);
    }

    // ---- update() ----
    public function test_admin_mengubah_data_user()
    {
        $user = $this->buatAnggotaUser();

        $response = $this->actingAs($this->admin)->put('/admin/users/' . $user->id_user, $this->dataUser($user, ['nama' => 'Nama Diubah']));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['id_user' => $user->id_user, 'nama' => 'Nama Diubah']);
    }

    public function test_admin_mengganti_password_user()
    {
        $user = $this->buatAnggotaUser();

        $this->actingAs($this->admin)->put('/admin/users/' . $user->id_user, $this->dataUser($user, ['password' => 'sandibaru1']));

        $this->assertTrue(Hash::check('sandibaru1', User::find($user->id_user)->password));
    }

    public function test_password_dibiarkan_kosong_tidak_mengubah_password()
    {
        $user = $this->buatAnggotaUser();

        $this->actingAs($this->admin)->put('/admin/users/' . $user->id_user, $this->dataUser($user));

        $this->assertTrue(Hash::check('password123', User::find($user->id_user)->password));
    }

    public function test_password_user_kurang_dari_6_karakter()
    {
        $user = $this->buatAnggotaUser();

        $response = $this->actingAs($this->admin)->put('/admin/users/' . $user->id_user, $this->dataUser($user, ['password' => '123']));

        $response->assertSessionHasErrors('password');
    }

    public function test_email_user_sudah_dipakai_user_lain()
    {
        $lain = $this->buatAnggotaUser();
        $user = $this->buatAnggotaUser();

        $response = $this->actingAs($this->admin)->put('/admin/users/' . $user->id_user, $this->dataUser($user, ['email' => $lain->email]));

        $response->assertSessionHasErrors('email');
    }

    public function test_role_user_yang_tidak_ada()
    {
        $user = $this->buatAnggotaUser();

        $response = $this->actingAs($this->admin)->put('/admin/users/' . $user->id_user, $this->dataUser($user, ['id_role' => 9]));

        $response->assertSessionHasErrors('id_role');
    }

    // ---- updateRole() ----
    public function test_admin_mengubah_role_user_menjadi_admin()
    {
        $user = $this->buatAnggotaUser();

        $response = $this->actingAs($this->admin)->put('/admin/users/' . $user->id_user . '/role', ['id_role' => 1]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['id_user' => $user->id_user, 'id_role' => 1]);
    }

    public function test_ubah_role_menampilkan_nama_role_lama_dan_baru()
    {
        $user = $this->buatAnggotaUser(['nama' => 'Rina']);

        $response = $this->actingAs($this->admin)->put('/admin/users/' . $user->id_user . '/role', ['id_role' => 1]);

        $response->assertSessionHas('success', 'Role user Rina berhasil diubah dari anggota menjadi admin');
    }

    public function test_ubah_role_dengan_role_yang_tidak_ada()
    {
        $user = $this->buatAnggotaUser();

        $response = $this->actingAs($this->admin)->put('/admin/users/' . $user->id_user . '/role', ['id_role' => 50]);

        $response->assertSessionHasErrors('id_role');
    }

    public function test_ubah_role_user_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->put('/admin/users/999/role', ['id_role' => 1]);

        $response->assertStatus(404);
    }

    // ---- destroy() ----
    public function test_admin_menghapus_user_yang_tidak_punya_data_anggota()
    {
        $user = $this->buatAnggotaUser();

        $response = $this->actingAs($this->admin)->delete('/admin/users/' . $user->id_user);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id_user' => $user->id_user]);
    }

    public function test_user_yang_punya_data_anggota_tidak_bisa_dihapus()
    {
        $user = $this->buatAnggotaUser();
        $this->buatAnggotaData($user, $this->buatDivisi());

        $response = $this->actingAs($this->admin)->delete('/admin/users/' . $user->id_user);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id_user' => $user->id_user]);
    }

    public function test_hapus_user_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->delete('/admin/users/999');

        $response->assertStatus(404);
    }
}
