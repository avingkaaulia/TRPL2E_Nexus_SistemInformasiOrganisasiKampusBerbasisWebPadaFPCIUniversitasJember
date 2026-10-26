<?php

namespace Tests\Feature;

use App\Models\BerkasPendaftaran;
use App\Models\JenisBerkas;
use Tests\DatabaseTestCase;

class PendaftaranControllerTest extends DatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->isiFormFieldDefault();
    }

    private function dataDaftar($periode, array $ganti = [])
    {
        return array_merge([
            'nama' => 'Siti Nailatul',
            'email' => 'siti@mail.com',
            'nim' => '240123456',
            'no_hp' => '081234567890',
            'id_periode' => $periode->id_periode,
        ], $ganti);
    }

    // ---- index() ----
    public function test_melihat_halaman_pendaftaran()
    {
        $response = $this->get('/pendaftaran');

        $response->assertStatus(200);
    }

    public function test_halaman_pendaftaran_membawa_periode_yang_sedang_aktif()
    {
        $this->buatPeriode(['nama_periode' => 'Gelombang Aktif']);

        $response = $this->get('/pendaftaran');

        $response->assertViewHas('periodeAktif', function ($p) {
            return $p->nama_periode == 'Gelombang Aktif';
        });
    }

    public function test_halaman_pendaftaran_tanpa_periode_aktif()
    {
        $this->buatPeriode(['is_active' => 0]);

        $response = $this->get('/pendaftaran');

        $this->assertNull($response->viewData('periodeAktif'));
    }

    // ---- store() ----
    // TC-REG-001
    public function test_pendaftaran_dengan_data_valid()
    {
        $periode = $this->buatPeriode();

        $response = $this->post('/pendaftaran', $this->dataDaftar($periode));

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('pendaftaran', ['email' => 'siti@mail.com', 'nim' => '240123456', 'status' => 'menunggu']);
    }

    // TC-REG-004
    public function test_pendaftaran_tanpa_data_wajib()
    {
        $periode = $this->buatPeriode();

        $response = $this->post('/pendaftaran', ['id_periode' => $periode->id_periode]);

        $response->assertSessionHasErrors(['nama', 'email', 'nim']);
        $this->assertDatabaseCount('pendaftaran', 0);
    }

    public function test_nim_harus_diawali_240()
    {
        $periode = $this->buatPeriode();

        $response = $this->post('/pendaftaran', $this->dataDaftar($periode, ['nim' => '230123456']));

        $response->assertSessionHasErrors('nim');
    }

    public function test_nim_yang_sudah_terdaftar_ditolak()
    {
        $periode = $this->buatPeriode();
        $this->buatPendaftar(['nim' => '240123456', 'id_periode' => $periode->id_periode]);

        $response = $this->post('/pendaftaran', $this->dataDaftar($periode, ['email' => 'lain@mail.com']));

        $response->assertSessionHasErrors('nim');
    }

    public function test_email_yang_sudah_terdaftar_ditolak()
    {
        $periode = $this->buatPeriode();
        $this->buatPendaftar(['email' => 'siti@mail.com', 'id_periode' => $periode->id_periode]);

        $response = $this->post('/pendaftaran', $this->dataDaftar($periode, ['nim' => '240999999']));

        $response->assertSessionHasErrors('email');
    }

    // TC-REG-002
    public function test_pendaftaran_saat_periode_ditutup()
    {
        $periode = $this->buatPeriode(['is_active' => 0]);

        $response = $this->post('/pendaftaran', $this->dataDaftar($periode));

        $response->assertSessionHas('error', 'Periode pendaftaran sudah berakhir!');
        $this->assertDatabaseCount('pendaftaran', 0);
    }

    // TC-REG-003
    public function test_pendaftaran_saat_kuota_penuh()
    {
        $periode = $this->buatPeriode(['kuota' => 1]);
        $this->buatPendaftar(['id_periode' => $periode->id_periode]);

        $response = $this->post('/pendaftaran', $this->dataDaftar($periode));

        $response->assertSessionHas('error', 'Maaf, kuota pendaftaran sudah penuh!');
    }

    // TC-REG-008
    public function test_pendaftaran_di_hari_terakhir_periode_masih_diterima()
    {
        $periode = $this->buatPeriode(['tanggal_selesai' => now()->toDateString()]);

        $response = $this->post('/pendaftaran', $this->dataDaftar($periode));

        $response->assertSessionHas('success');
    }

    public function test_pendaftaran_dengan_periode_yang_tidak_ada()
    {
        $response = $this->post('/pendaftaran', [
            'nama' => 'Siti', 'email' => 'siti@mail.com', 'nim' => '240123456', 'id_periode' => 999,
        ]);

        $response->assertSessionHasErrors('id_periode');
    }

    public function test_berkas_wajib_tidak_diupload()
    {
        $periode = $this->buatPeriode();
        $jenis = JenisBerkas::create(['nama_jenis' => 'KTM', 'is_required' => 1, 'file_type' => 'jpg,png', 'max_size' => 2048]);

        $response = $this->post('/pendaftaran', $this->dataDaftar($periode));

        $response->assertSessionHasErrors('berkas_' . $jenis->id_jenis);
    }

    public function test_berkas_melebihi_ukuran_maksimal()
    {
        $periode = $this->buatPeriode();
        $jenis = JenisBerkas::create(['nama_jenis' => 'KTM', 'is_required' => 1, 'file_type' => 'jpg,png', 'max_size' => 2048]);

        $response = $this->post('/pendaftaran', $this->dataDaftar($periode, [
            'berkas_' . $jenis->id_jenis => $this->gambarPalsu('ktm.jpg', 3000),
        ]));

        $response->assertSessionHasErrors('berkas_' . $jenis->id_jenis);
    }

    public function test_berkas_dengan_format_yang_tidak_didukung()
    {
        $periode = $this->buatPeriode();
        $jenis = JenisBerkas::create(['nama_jenis' => 'KTM', 'is_required' => 1, 'file_type' => 'jpg,png', 'max_size' => 2048]);
        $pdf = \Illuminate\Http\UploadedFile::fake()->create('ktm.pdf', 100, 'application/pdf');

        $response = $this->post('/pendaftaran', $this->dataDaftar($periode, ['berkas_' . $jenis->id_jenis => $pdf]));

        $response->assertSessionHasErrors('berkas_' . $jenis->id_jenis);
    }

    // ---- cekStatus() ----
    public function test_cek_status_dengan_email_terdaftar()
    {
        $this->buatPendaftar(['nama' => 'Arzu', 'email' => 'arzu@mail.com']);

        $response = $this->getJson('/cek-pendaftaran/arzu@mail.com');

        $response->assertOk();
        $response->assertJsonFragment(['nama' => 'Arzu']);
    }

    public function test_cek_status_dengan_email_tidak_terdaftar()
    {
        $response = $this->getJson('/cek-pendaftaran/hantu@mail.com');

        $response->assertOk();
        $this->assertSame([], $response->json());
    }

    public function test_cek_status_ikut_membawa_berkas_pendaftar()
    {
        $pendaftar = $this->buatPendaftar(['email' => 'arzu@mail.com']);
        $jenis = JenisBerkas::create(['nama_jenis' => 'KTM', 'is_required' => 1, 'file_type' => 'jpg', 'max_size' => 1024]);
        BerkasPendaftaran::create(['id_pendaftaran' => $pendaftar->id_pendaftaran, 'id_jenis' => $jenis->id_jenis, 'file_path' => 'berkas/1/ktm.jpg']);

        $response = $this->getJson('/cek-pendaftaran/arzu@mail.com');

        $response->assertJsonCount(1, 'berkas');
    }
}
