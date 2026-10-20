<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\DatabaseTestCase;

class AdminAnggotaControllerTest extends DatabaseTestCase
{
    private $admin;
    private $divisi;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->buatAdmin();
        $this->divisi = $this->buatDivisi('Hubungan Masyarakat');
    }

    private function anggotaBaru(array $attr = [], array $userAttr = [])
    {
        return $this->buatAnggotaData($this->buatAnggotaUser($userAttr), $this->divisi, $attr);
    }

    private function dataUpdate($anggota, array $ganti = [])
    {
        return array_merge([
            'nama' => $anggota->user->nama,
            'email' => $anggota->user->email,
            'username' => $anggota->user->username,
            'id_divisi' => $anggota->id_divisi,
            'jabatan' => $anggota->jabatan,
            'periode' => $anggota->periode,
            'no_urut' => $anggota->no_urut,
        ], $ganti);
    }

    // ---- index() ----
    // TC-STRUCT-004
    public function test_admin_melihat_daftar_anggota()
    {
        $this->anggotaBaru();

        $response = $this->actingAs($this->admin)->get('/admin/anggota');

        $response->assertStatus(200);
        $response->assertViewIs('admin.anggota.index');
    }

    // TC-SORT-001
    public function test_daftar_anggota_bisa_difilter_berdasarkan_divisi()
    {
        $litbang = $this->buatDivisi('Litbang');
        $this->anggotaBaru(['no_urut' => 1]);
        $this->buatAnggotaData($this->buatAnggotaUser(), $litbang, ['no_urut' => 2]);

        $response = $this->actingAs($this->admin)->get('/admin/anggota?divisi=' . $litbang->id_divisi);

        $response->assertViewHas('anggota', function ($a) {
            return $a->total() == 1;
        });
    }

    // TC-STRUCT-005
    public function test_daftar_anggota_bisa_difilter_berdasarkan_periode()
    {
        $this->anggotaBaru(['periode' => '2024/2025', 'no_urut' => 1]);
        $this->anggotaBaru(['periode' => '2025/2026', 'no_urut' => 2]);

        $response = $this->actingAs($this->admin)->get('/admin/anggota?periode=2025/2026');

        $response->assertViewHas('anggota', function ($a) {
            return $a->total() == 1;
        });
    }

    public function test_daftar_anggota_bisa_dicari_berdasarkan_nama()
    {
        $this->anggotaBaru(['no_urut' => 1], ['nama' => 'Arzu Ridho']);
        $this->anggotaBaru(['no_urut' => 2], ['nama' => 'Siti Nailatul']);

        $response = $this->actingAs($this->admin)->get('/admin/anggota?search=Arzu');

        $response->assertViewHas('anggota', function ($a) {
            return $a->total() == 1;
        });
    }

    public function test_pendaftar_diterima_yang_belum_jadi_anggota_ditampilkan()
    {
        $this->buatPendaftar(['status' => 'diterima', 'email' => 'baru@mail.com']);

        $response = $this->actingAs($this->admin)->get('/admin/anggota');

        $response->assertViewHas('pendaftaranDiterima', function ($p) {
            return $p->count() == 1;
        });
    }

    // ---- show() ----
    public function test_admin_melihat_detail_anggota()
    {
        $anggota = $this->anggotaBaru();

        $response = $this->actingAs($this->admin)->get('/admin/anggota/' . $anggota->id_anggota);

        $response->assertStatus(200);
    }

    public function test_detail_anggota_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->get('/admin/anggota/999');

        $response->assertStatus(404);
    }

    // ---- edit() ----
    public function test_admin_melihat_form_edit_anggota()
    {
        $anggota = $this->anggotaBaru();

        $response = $this->actingAs($this->admin)->get('/admin/anggota/' . $anggota->id_anggota . '/edit');

        $response->assertStatus(200);
    }

    public function test_form_edit_anggota_menyediakan_pilihan_periode()
    {
        $anggota = $this->anggotaBaru();

        $response = $this->actingAs($this->admin)->get('/admin/anggota/' . $anggota->id_anggota . '/edit');

        $response->assertViewHas('periodeList', ['2024/2025', '2025/2026', '2026/2027']);
    }

    // ---- update() ----
    // TC-STRUCT-002
    public function test_admin_mengubah_jabatan_anggota()
    {
        $anggota = $this->anggotaBaru();

        $response = $this->actingAs($this->admin)->put('/admin/anggota/' . $anggota->id_anggota, $this->dataUpdate($anggota, ['jabatan' => 'Head of Humas']));

        $response->assertRedirect(route('admin.anggota.index'));
        $this->assertDatabaseHas('anggota', ['id_anggota' => $anggota->id_anggota, 'jabatan' => 'Head of Humas']);
    }

    public function test_update_anggota_ikut_mengubah_data_user()
    {
        $anggota = $this->anggotaBaru();

        $this->actingAs($this->admin)->put('/admin/anggota/' . $anggota->id_anggota, $this->dataUpdate($anggota, ['nama' => 'Nama Baru']));

        $this->assertDatabaseHas('users', ['id_user' => $anggota->id_user, 'nama' => 'Nama Baru']);
    }

    // TC-STRUCT-003
    public function test_update_anggota_tanpa_nama_jabatan_dan_periode()
    {
        $anggota = $this->anggotaBaru();

        $response = $this->actingAs($this->admin)->put('/admin/anggota/' . $anggota->id_anggota, $this->dataUpdate($anggota, [
            'nama' => '', 'jabatan' => '', 'periode' => '',
        ]));

        $response->assertSessionHasErrors(['nama', 'jabatan', 'periode']);
    }

    public function test_update_anggota_dengan_email_milik_user_lain()
    {
        $lain = $this->anggotaBaru(['no_urut' => 2]);
        $anggota = $this->anggotaBaru(['no_urut' => 1]);

        $response = $this->actingAs($this->admin)->put('/admin/anggota/' . $anggota->id_anggota, $this->dataUpdate($anggota, ['email' => $lain->user->email]));

        $response->assertSessionHasErrors('email');
    }

    public function test_update_anggota_dengan_no_urut_yang_sudah_dipakai()
    {
        $this->anggotaBaru(['no_urut' => 5]);
        $anggota = $this->anggotaBaru(['no_urut' => 6]);

        $response = $this->actingAs($this->admin)->put('/admin/anggota/' . $anggota->id_anggota, $this->dataUpdate($anggota, ['no_urut' => 5]));

        $response->assertSessionHasErrors('no_urut');
    }

    public function test_update_anggota_dengan_divisi_yang_tidak_ada()
    {
        $anggota = $this->anggotaBaru();

        $response = $this->actingAs($this->admin)->put('/admin/anggota/' . $anggota->id_anggota, $this->dataUpdate($anggota, ['id_divisi' => 99]));

        $response->assertSessionHasErrors('id_divisi');
    }

    public function test_update_anggota_dengan_foto_terlalu_besar()
    {
        $anggota = $this->anggotaBaru();

        $response = $this->actingAs($this->admin)->put('/admin/anggota/' . $anggota->id_anggota, $this->dataUpdate($anggota, [
            'foto' => $this->gambarPalsu('foto.jpg', 5000),
        ]));

        $response->assertSessionHasErrors('foto');
    }

    public function test_update_anggota_dengan_foto_baru_tersimpan_di_storage()
    {
        // foto lama dikosongkan supaya tidak ada file asli di folder public yang ikut terhapus
        $anggota = $this->anggotaBaru(['foto' => '']);

        $this->actingAs($this->admin)->put('/admin/anggota/' . $anggota->id_anggota, $this->dataUpdate($anggota, [
            'foto' => $this->gambarPalsu('foto.jpg', 100),
        ]));

        $this->assertStringStartsWith('storage/anggota/', Anggota::find($anggota->id_anggota)->foto);
    }

    public function test_update_anggota_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->put('/admin/anggota/999', []);

        $response->assertStatus(404);
    }

    // ---- destroy() ----
    public function test_admin_menghapus_anggota_beserta_akun_usernya()
    {
        $anggota = $this->anggotaBaru();

        $response = $this->actingAs($this->admin)->delete('/admin/anggota/' . $anggota->id_anggota);

        $response->assertRedirect(route('admin.anggota.index'));
        $this->assertDatabaseMissing('anggota', ['id_anggota' => $anggota->id_anggota]);
        $this->assertDatabaseMissing('users', ['id_user' => $anggota->id_user]);
    }

    public function test_hapus_anggota_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->delete('/admin/anggota/999');

        $response->assertStatus(404);
    }

    public function test_hapus_anggota_tercatat_di_log()
    {
        $anggota = $this->anggotaBaru();

        $this->actingAs($this->admin)->delete('/admin/anggota/' . $anggota->id_anggota);

        $this->assertDatabaseHas('activity_log', ['action' => 'delete', 'model_type' => 'Anggota']);
    }

    // ---- convertFromPendaftaran() ----
    public function test_pendaftar_diubah_menjadi_anggota()
    {
        $pendaftar = $this->buatPendaftar(['nama' => 'Calon Satu', 'email' => 'calon1@mail.com', 'nim' => '240111']);

        $response = $this->actingAs($this->admin)->post('/admin/anggota/convert/' . $pendaftar->id_pendaftaran);

        $response->assertRedirect(route('admin.anggota.index'));
        $user = User::where('email', 'calon1@mail.com')->first();
        $this->assertEquals(2, $user->id_role);
        $this->assertDatabaseHas('anggota', ['id_user' => $user->id_user, 'jabatan' => 'Staff']);
    }

    public function test_konversi_mengubah_status_pendaftar_jadi_diterima()
    {
        $pendaftar = $this->buatPendaftar(['status' => 'menunggu']);

        $this->actingAs($this->admin)->post('/admin/anggota/convert/' . $pendaftar->id_pendaftaran);

        $this->assertDatabaseHas('pendaftaran', ['id_pendaftaran' => $pendaftar->id_pendaftaran, 'status' => 'diterima']);
    }

    public function test_konversi_memakai_nim_sebagai_username_awal()
    {
        $pendaftar = $this->buatPendaftar(['nim' => '240777', 'email' => 'nim@mail.com']);

        $this->actingAs($this->admin)->post('/admin/anggota/convert/' . $pendaftar->id_pendaftaran);

        $this->assertDatabaseHas('users', ['email' => 'nim@mail.com', 'username' => '240777']);
    }

    public function test_konversi_no_urut_melanjutkan_nomor_terakhir()
    {
        $this->anggotaBaru(['no_urut' => 7]);
        $pendaftar = $this->buatPendaftar(['email' => 'urut@mail.com']);

        $this->actingAs($this->admin)->post('/admin/anggota/convert/' . $pendaftar->id_pendaftaran);

        $user = User::where('email', 'urut@mail.com')->first();
        $this->assertDatabaseHas('anggota', ['id_user' => $user->id_user, 'no_urut' => 8]);
    }

    public function test_konversi_memakai_divisi_pilihan_pendaftar()
    {
        $litbang = $this->buatDivisi('Litbang');
        $pendaftar = $this->buatPendaftar(['email' => 'lit@mail.com', 'divisi' => 'Litbang']);

        $this->actingAs($this->admin)->post('/admin/anggota/convert/' . $pendaftar->id_pendaftaran);

        $user = User::where('email', 'lit@mail.com')->first();
        $this->assertDatabaseHas('anggota', ['id_user' => $user->id_user, 'id_divisi' => $litbang->id_divisi]);
    }

    public function test_konversi_pendaftar_yang_sudah_jadi_anggota()
    {
        $anggota = $this->anggotaBaru([], ['email' => 'sudah@mail.com']);
        $pendaftar = $this->buatPendaftar(['email' => 'sudah@mail.com']);

        $response = $this->actingAs($this->admin)->post('/admin/anggota/convert/' . $pendaftar->id_pendaftaran);

        $response->assertSessionHas('error');
        $this->assertEquals(1, Anggota::count());
    }

    public function test_konversi_pendaftar_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->post('/admin/anggota/convert/999');

        $response->assertStatus(404);
    }
}
