<?php

namespace Tests\Feature;

use App\Mail\AcceptedMemberMail;
use App\Mail\RejectedMemberMail;
use App\Models\Anggota;
use App\Models\BerkasPendaftaran;
use App\Models\JenisBerkas;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\DatabaseTestCase;

class AdminPendaftaranControllerTest extends DatabaseTestCase
{
    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->buatAdmin();
        Mail::fake();
    }

    // ---- index() ----
    public function test_admin_melihat_daftar_pendaftar()
    {
        $this->buatPendaftar();

        $response = $this->actingAs($this->admin)->get('/admin/pendaftaran');

        $response->assertStatus(200);
        $response->assertViewIs('admin.pendaftaran.index');
    }

    public function test_daftar_pendaftar_bisa_difilter_status()
    {
        $this->buatPendaftar(['status' => 'menunggu']);
        $this->buatPendaftar(['status' => 'diterima']);

        $response = $this->actingAs($this->admin)->get('/admin/pendaftaran?status=diterima');

        $response->assertViewHas('pendaftaran', function ($p) {
            return $p->total() == 1;
        });
    }

    public function test_daftar_pendaftar_bisa_difilter_periode()
    {
        $p1 = $this->buatPeriode();
        $p2 = $this->buatPeriode(['nama_periode' => 'Gelombang 2']);
        $this->buatPendaftar(['id_periode' => $p1->id_periode]);
        $this->buatPendaftar(['id_periode' => $p2->id_periode]);

        $response = $this->actingAs($this->admin)->get('/admin/pendaftaran?periode=' . $p2->id_periode);

        $response->assertViewHas('pendaftaran', function ($p) {
            return $p->total() == 1;
        });
    }

    public function test_daftar_pendaftar_bisa_dicari_berdasarkan_nama_atau_nim()
    {
        $this->buatPendaftar(['nama' => 'Arzu Ridho', 'nim' => '240001']);
        $this->buatPendaftar(['nama' => 'Siti', 'nim' => '240002']);

        $response = $this->actingAs($this->admin)->get('/admin/pendaftaran?search=Arzu');

        $response->assertViewHas('pendaftaran', function ($p) {
            return $p->total() == 1;
        });
    }

    // ---- show() ----
    public function test_admin_melihat_detail_pendaftar()
    {
        $pendaftar = $this->buatPendaftar();

        $response = $this->actingAs($this->admin)->get('/admin/pendaftaran/' . $pendaftar->id_pendaftaran);

        $response->assertStatus(200);
    }

    public function test_detail_pendaftar_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->get('/admin/pendaftaran/999');

        $response->assertStatus(404);
    }

    // ---- updateStatus() ----
    public function test_admin_mengubah_status_pendaftar()
    {
        $pendaftar = $this->buatPendaftar();

        $response = $this->actingAs($this->admin)->put('/admin/pendaftaran/update-status/' . $pendaftar->id_pendaftaran, ['status' => 'ditolak']);

        $response->assertSessionHas('success', 'Status pendaftaran berhasil diupdate');
        $this->assertDatabaseHas('pendaftaran', ['id_pendaftaran' => $pendaftar->id_pendaftaran, 'status' => 'ditolak']);
    }

    public function test_ubah_status_pendaftar_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->put('/admin/pendaftaran/update-status/999', ['status' => 'diterima']);

        $response->assertStatus(404);
    }

    // ---- destroy() ----
    public function test_admin_menghapus_pendaftar()
    {
        $pendaftar = $this->buatPendaftar();

        $response = $this->actingAs($this->admin)->delete('/admin/pendaftaran/' . $pendaftar->id_pendaftaran);

        $response->assertRedirect(route('admin.pendaftaran.index'));
        $this->assertDatabaseMissing('pendaftaran', ['id_pendaftaran' => $pendaftar->id_pendaftaran]);
    }

    public function test_hapus_pendaftar_ikut_menghapus_data_berkasnya()
    {
        $pendaftar = $this->buatPendaftar();
        $jenis = JenisBerkas::create(['nama_jenis' => 'KTM', 'is_required' => 1, 'file_type' => 'jpg', 'max_size' => 1024]);
        BerkasPendaftaran::create(['id_pendaftaran' => $pendaftar->id_pendaftaran, 'id_jenis' => $jenis->id_jenis, 'file_path' => 'berkas/tidak-ada.jpg']);

        $this->actingAs($this->admin)->delete('/admin/pendaftaran/' . $pendaftar->id_pendaftaran);

        $this->assertDatabaseCount('berkas_pendaftaran', 0);
    }

    public function test_hapus_pendaftar_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->delete('/admin/pendaftaran/999');

        $response->assertStatus(404);
    }

    // ---- accept() ----
    // TC-REG-006
    public function test_admin_menerima_pendaftar()
    {
        $pendaftar = $this->buatPendaftar(['nama' => 'Calon Satu', 'email' => 'calon1@mail.com', 'nim' => '240111']);

        $response = $this->actingAs($this->admin)->put('/admin/pendaftaran/accept/' . $pendaftar->id_pendaftaran);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('pendaftaran', ['id_pendaftaran' => $pendaftar->id_pendaftaran, 'status' => 'diterima']);
    }

    public function test_menerima_pendaftar_membuat_akun_anggota_dengan_nim_sebagai_password()
    {
        $pendaftar = $this->buatPendaftar(['email' => 'calon1@mail.com', 'nim' => '240111']);

        $this->actingAs($this->admin)->put('/admin/pendaftaran/accept/' . $pendaftar->id_pendaftaran);

        $user = User::where('email', 'calon1@mail.com')->first();
        $this->assertEquals(2, $user->id_role);
        $this->assertTrue(Hash::check('240111', $user->password));
    }

    public function test_menerima_pendaftar_membuat_data_anggota_dengan_jabatan_staff()
    {
        $this->buatDivisi();
        $pendaftar = $this->buatPendaftar(['email' => 'calon1@mail.com']);

        $this->actingAs($this->admin)->put('/admin/pendaftaran/accept/' . $pendaftar->id_pendaftaran);

        $user = User::where('email', 'calon1@mail.com')->first();
        $this->assertDatabaseHas('anggota', ['id_user' => $user->id_user, 'jabatan' => 'Staff', 'periode' => '2025/2026']);
    }

    public function test_menerima_pendaftar_mengirim_email_diterima()
    {
        $pendaftar = $this->buatPendaftar(['email' => 'calon1@mail.com']);

        $this->actingAs($this->admin)->put('/admin/pendaftaran/accept/' . $pendaftar->id_pendaftaran);

        Mail::assertSent(AcceptedMemberMail::class, function ($mail) {
            return $mail->hasTo('calon1@mail.com');
        });
    }

    public function test_menerima_pendaftar_yang_emailnya_sudah_punya_akun_tidak_membuat_akun_ganda()
    {
        $this->buatAnggotaUser(['email' => 'calon1@mail.com']);
        $pendaftar = $this->buatPendaftar(['email' => 'calon1@mail.com']);

        $this->actingAs($this->admin)->put('/admin/pendaftaran/accept/' . $pendaftar->id_pendaftaran);

        $this->assertEquals(1, User::where('email', 'calon1@mail.com')->count());
    }

    public function test_menerima_pendaftar_dua_kali_tidak_membuat_anggota_ganda()
    {
        $pendaftar = $this->buatPendaftar(['email' => 'calon1@mail.com']);

        $this->actingAs($this->admin)->put('/admin/pendaftaran/accept/' . $pendaftar->id_pendaftaran);
        $this->actingAs($this->admin)->put('/admin/pendaftaran/accept/' . $pendaftar->id_pendaftaran);

        $this->assertEquals(1, Anggota::count());
    }

    public function test_menerima_pendaftar_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->put('/admin/pendaftaran/accept/999');

        $response->assertStatus(404);
    }

    // ---- reject() ----
    // TC-REG-007
    public function test_admin_menolak_pendaftar()
    {
        $pendaftar = $this->buatPendaftar();

        $response = $this->actingAs($this->admin)->put('/admin/pendaftaran/reject/' . $pendaftar->id_pendaftaran);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('pendaftaran', ['id_pendaftaran' => $pendaftar->id_pendaftaran, 'status' => 'ditolak']);
    }

    public function test_menolak_pendaftar_mengirim_email_penolakan()
    {
        $pendaftar = $this->buatPendaftar(['email' => 'calon2@mail.com']);

        $this->actingAs($this->admin)->put('/admin/pendaftaran/reject/' . $pendaftar->id_pendaftaran);

        Mail::assertSent(RejectedMemberMail::class, function ($mail) {
            return $mail->hasTo('calon2@mail.com');
        });
    }

    public function test_menolak_pendaftar_tidak_membuat_akun_user()
    {
        $pendaftar = $this->buatPendaftar(['email' => 'calon2@mail.com']);

        $this->actingAs($this->admin)->put('/admin/pendaftaran/reject/' . $pendaftar->id_pendaftaran);

        $this->assertDatabaseMissing('users', ['email' => 'calon2@mail.com']);
    }

    public function test_menolak_pendaftar_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->put('/admin/pendaftaran/reject/999');

        $response->assertStatus(404);
    }

    // ---- downloadBerkas() ----
    public function test_download_berkas_yang_filenya_ada()
    {
        $folder = public_path('berkas_uji');
        File::ensureDirectoryExists($folder);
        File::put($folder . '/ktm.txt', 'isi berkas');
        $pendaftar = $this->buatPendaftar();
        $jenis = JenisBerkas::create(['nama_jenis' => 'KTM', 'is_required' => 1, 'file_type' => 'txt', 'max_size' => 1024]);
        $berkas = BerkasPendaftaran::create(['id_pendaftaran' => $pendaftar->id_pendaftaran, 'id_jenis' => $jenis->id_jenis, 'file_path' => 'berkas_uji/ktm.txt']);

        $response = $this->actingAs($this->admin)->get('/admin/pendaftaran/download-berkas/' . $berkas->id_berkas);

        File::deleteDirectory($folder);
        $response->assertStatus(200);
        $response->assertHeader('content-disposition');
    }

    public function test_download_berkas_yang_filenya_hilang()
    {
        $pendaftar = $this->buatPendaftar();
        $jenis = JenisBerkas::create(['nama_jenis' => 'KTM', 'is_required' => 1, 'file_type' => 'jpg', 'max_size' => 1024]);
        $berkas = BerkasPendaftaran::create(['id_pendaftaran' => $pendaftar->id_pendaftaran, 'id_jenis' => $jenis->id_jenis, 'file_path' => 'berkas/hilang.jpg']);

        $response = $this->actingAs($this->admin)->get('/admin/pendaftaran/download-berkas/' . $berkas->id_berkas);

        $response->assertSessionHas('error', 'File tidak ditemukan');
    }

    public function test_download_berkas_yang_tidak_ada_di_database()
    {
        $response = $this->actingAs($this->admin)->get('/admin/pendaftaran/download-berkas/999');

        $response->assertStatus(404);
    }
}
