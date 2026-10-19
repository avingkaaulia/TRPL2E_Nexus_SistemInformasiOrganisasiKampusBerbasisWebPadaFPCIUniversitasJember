<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\DatabaseTestCase;

// AdminContactController, AdminLogoController, AdminPageController
class AdminContactLogoPageTest extends DatabaseTestCase
{
    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->buatAdmin();
    }

    private function dataKontak(array $ganti = [])
    {
        return array_merge([
            'email' => 'fpci.unej@gmail.com',
            'no_hp' => '081200000000',
            'alamat' => 'Jl. Kalimantan No.37, Jember',
        ], $ganti);
    }

    // ---- AdminContactController::index() ----
    public function test_admin_melihat_informasi_kontak()
    {
        $this->buatKontak();

        $response = $this->actingAs($this->admin)->get('/admin/contact');

        $response->assertStatus(200);
        $response->assertViewIs('admin.contact.index');
    }

    public function test_informasi_kontak_kosong_saat_belum_diinput()
    {
        $response = $this->actingAs($this->admin)->get('/admin/contact');

        $this->assertNull($response->viewData('contact'));
    }

    // ---- AdminContactController::edit() ----
    public function test_admin_melihat_form_edit_kontak()
    {
        $this->buatKontak();

        $response = $this->actingAs($this->admin)->get('/admin/contact/edit');

        $response->assertStatus(200);
    }

    public function test_form_edit_kontak_saat_data_belum_ada_memakai_objek_kosong()
    {
        $response = $this->actingAs($this->admin)->get('/admin/contact/edit');

        $response->assertViewHas('contact', function ($c) {
            return $c instanceof Contact && !$c->exists;
        });
    }

    // ---- AdminContactController::update() ----
    // TC-CONTACT-001
    public function test_admin_menyimpan_kontak_untuk_pertama_kali()
    {
        $response = $this->actingAs($this->admin)->put('/admin/contact/update', $this->dataKontak());

        $response->assertRedirect(route('admin.contact.index'));
        $this->assertDatabaseHas('contact', ['email' => 'fpci.unej@gmail.com']);
    }

    // TC-CONTACT-003
    public function test_admin_mengubah_kontak_yang_sudah_ada_tanpa_menambah_baris()
    {
        $this->buatKontak();

        $this->actingAs($this->admin)->put('/admin/contact/update', $this->dataKontak(['email' => 'baru@fpci.test']));

        $this->assertEquals(1, Contact::count());
        $this->assertDatabaseHas('contact', ['email' => 'baru@fpci.test']);
    }

    // TC-CONTACT-002
    public function test_admin_menambah_tautan_media_sosial()
    {
        $this->actingAs($this->admin)->put('/admin/contact/update', $this->dataKontak([
            'instagram' => 'fpciunej', 'youtube' => 'fpciunejchannel', 'x' => 'fpciunej',
        ]));

        $this->assertDatabaseHas('contact', ['instagram' => 'fpciunej', 'youtube' => 'fpciunejchannel']);
    }

    public function test_simpan_kontak_tanpa_data_wajib()
    {
        $response = $this->actingAs($this->admin)->put('/admin/contact/update', []);

        $response->assertSessionHasErrors(['email', 'no_hp', 'alamat']);
    }

    public function test_simpan_kontak_dengan_email_tidak_valid()
    {
        $response = $this->actingAs($this->admin)->put('/admin/contact/update', $this->dataKontak(['email' => 'bukan-email']));

        $response->assertSessionHasErrors('email');
    }

    // TC-CONTACT-004, kode hanya membatasi panjang 100 karakter, format URL tidak diperiksa
    public function test_tautan_media_sosial_lebih_dari_100_karakter_ditolak()
    {
        $response = $this->actingAs($this->admin)->put('/admin/contact/update', $this->dataKontak(['instagram' => str_repeat('i', 101)]));

        $response->assertSessionHasErrors('instagram');
    }

    // ---- AdminLogoController::index() ----
    public function test_admin_melihat_halaman_logo()
    {
        $response = $this->actingAs($this->admin)->get('/admin/logo');

        $response->assertStatus(200);
    }

    public function test_halaman_logo_memakai_logo_default_kalau_belum_diganti()
    {
        $response = $this->actingAs($this->admin)->get('/admin/logo');

        $response->assertViewHas('logo', 'assets/img/logo.png');
    }

    // ---- AdminLogoController::updateLogo() ----
    // TC-UPLOAD-001
    public function test_admin_mengganti_logo()
    {
        $file = UploadedFile::fake()->create('logo.png', 100, 'image/png');

        $response = $this->actingAs($this->admin)->put('/admin/logo/update', ['logo' => $file]);

        $response->assertRedirect(route('admin.logo.index'));
        $this->assertStringStartsWith('storage/settings/logo_', Setting::get('site_logo'));
    }

    public function test_ganti_logo_tanpa_memilih_file()
    {
        $response = $this->actingAs($this->admin)->put('/admin/logo/update', []);

        $response->assertSessionHasErrors('logo');
    }

    // TC-UPLOAD-002
    public function test_ganti_logo_dengan_file_pdf_ditolak()
    {
        $pdf = UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->admin)->put('/admin/logo/update', ['logo' => $pdf]);

        $response->assertSessionHasErrors('logo');
    }

    // TC-UPLOAD-005
    public function test_ganti_logo_lebih_dari_batas_ukuran_ditolak()
    {
        $file = UploadedFile::fake()->create('logo.png', 6000, 'image/png');

        $response = $this->actingAs($this->admin)->put('/admin/logo/update', ['logo' => $file]);

        $response->assertSessionHasErrors('logo');
    }

    // ---- AdminLogoController::updateFavicon() ----
    public function test_admin_mengganti_favicon()
    {
        $file = UploadedFile::fake()->create('fav.png', 20, 'image/png');

        $response = $this->actingAs($this->admin)->put('/admin/logo/favicon', ['favicon' => $file]);

        $response->assertRedirect(route('admin.logo.index'));
        $this->assertStringStartsWith('storage/settings/favicon_', Setting::get('site_favicon'));
    }

    public function test_ganti_favicon_tanpa_file()
    {
        $response = $this->actingAs($this->admin)->put('/admin/logo/favicon', []);

        $response->assertSessionHasErrors('favicon');
    }

    public function test_ganti_favicon_dengan_file_bukan_gambar()
    {
        $pdf = UploadedFile::fake()->create('dokumen.pdf', 20, 'application/pdf');

        $response = $this->actingAs($this->admin)->put('/admin/logo/favicon', ['favicon' => $pdf]);

        $response->assertSessionHasErrors('favicon');
    }

    // ---- AdminLogoController::resetLogo() ----
    public function test_reset_logo_kembali_ke_default()
    {
        Setting::set('site_logo', 'storage/settings/logo_lama.png', 'image');

        $response = $this->actingAs($this->admin)->get('/admin/logo/reset');

        $response->assertRedirect(route('admin.logo.index'));
        $this->assertEquals('assets/img/logo.png', Setting::get('site_logo'));
    }

    public function test_reset_logo_saat_masih_memakai_default_tidak_error()
    {
        $response = $this->actingAs($this->admin)->get('/admin/logo/reset');

        $response->assertSessionHas('success', 'Logo berhasil direset ke default!');
    }

    // ---- AdminPageController::index() ----
    public function test_admin_melihat_daftar_halaman()
    {
        $response = $this->actingAs($this->admin)->get('/admin/pages');

        $response->assertStatus(200);
        $response->assertViewIs('admin.pages.index');
    }

    public function test_daftar_halaman_admin_hanya_berisi_post_bertipe_page()
    {
        $this->buatPost(['post_type' => 'page']);
        $this->buatPost(['post_type' => 'page', 'status' => 'draft']);
        $this->buatPost(['post_type' => 'post']);

        $response = $this->actingAs($this->admin)->get('/admin/pages');

        $response->assertViewHas('pages', function ($p) {
            return $p->total() == 2;
        });
    }

    public function test_daftar_halaman_dipaginasi_15_per_halaman()
    {
        for ($i = 0; $i < 16; $i++) {
            $this->buatPost(['post_type' => 'page']);
        }

        $response = $this->actingAs($this->admin)->get('/admin/pages');

        $response->assertViewHas('pages', function ($p) {
            return $p->count() == 15;
        });
    }
}
