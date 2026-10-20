<?php

namespace Tests\Feature;

use App\Models\FormField;
use App\Models\JenisBerkas;
use Tests\DatabaseTestCase;

// form field dan jenis berkas pendaftaran (AdminPendaftaranController)
class AdminFormBerkasControllerTest extends DatabaseTestCase
{
    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->buatAdmin();
    }

    private function dataField(array $ganti = [])
    {
        return array_merge([
            'field_name' => 'asal_kampus',
            'field_label' => 'Asal Kampus',
            'field_type' => 'text',
            'sort_order' => 5,
        ], $ganti);
    }

    private function dataBerkas(array $ganti = [])
    {
        return array_merge([
            'nama_jenis' => 'Transkrip Nilai',
            'file_type' => 'pdf',
            'max_size' => 2048,
        ], $ganti);
    }

    private function buatField($nama = 'hobi')
    {
        return FormField::create([
            'field_name' => $nama, 'field_label' => ucfirst($nama), 'field_type' => 'text',
            'is_required' => 0, 'sort_order' => 1, 'is_active' => 1,
        ]);
    }

    private function buatJenis($nama = 'KTM')
    {
        return JenisBerkas::create(['nama_jenis' => $nama, 'is_required' => 1, 'file_type' => 'jpg', 'max_size' => 1024]);
    }

    // ---- formFields() ----
    public function test_admin_melihat_daftar_form_field()
    {
        $this->buatField();

        $response = $this->actingAs($this->admin)->get('/admin/pendaftaran/form-fields');

        $response->assertStatus(200);
        $response->assertViewIs('admin.pendaftaran.form-fields');
    }

    public function test_daftar_form_field_diurutkan_berdasarkan_sort_order()
    {
        FormField::create(['field_name' => 'b', 'field_label' => 'B', 'field_type' => 'text', 'sort_order' => 2, 'is_active' => 1]);
        FormField::create(['field_name' => 'a', 'field_label' => 'A', 'field_type' => 'text', 'sort_order' => 1, 'is_active' => 1]);

        $response = $this->actingAs($this->admin)->get('/admin/pendaftaran/form-fields');

        $response->assertViewHas('formFields', function ($f) {
            return $f->first()->field_name == 'a';
        });
    }

    // ---- storeFormField() ----
    public function test_admin_menambah_form_field()
    {
        $response = $this->actingAs($this->admin)->post('/admin/pendaftaran/form-fields', $this->dataField());

        $response->assertRedirect(route('admin.pendaftaran.form-fields'));
        $this->assertDatabaseHas('form_fields', ['field_name' => 'asal_kampus', 'is_active' => 1]);
    }

    public function test_form_field_dengan_nama_yang_sudah_ada()
    {
        $this->buatField('asal_kampus');

        $response = $this->actingAs($this->admin)->post('/admin/pendaftaran/form-fields', $this->dataField());

        $response->assertSessionHasErrors('field_name');
    }

    public function test_form_field_dengan_tipe_yang_tidak_dikenal()
    {
        $response = $this->actingAs($this->admin)->post('/admin/pendaftaran/form-fields', $this->dataField(['field_type' => 'warna']));

        $response->assertSessionHasErrors('field_type');
    }

    public function test_form_field_tanpa_label()
    {
        $response = $this->actingAs($this->admin)->post('/admin/pendaftaran/form-fields', $this->dataField(['field_label' => '']));

        $response->assertSessionHasErrors('field_label');
    }

    public function test_form_field_dicentang_wajib_diisi()
    {
        $this->actingAs($this->admin)->post('/admin/pendaftaran/form-fields', $this->dataField(['is_required' => 1]));

        $this->assertDatabaseHas('form_fields', ['field_name' => 'asal_kampus', 'is_required' => 1]);
    }

    // ---- updateFormField() ----
    public function test_admin_mengubah_label_form_field()
    {
        $field = $this->buatField('hobi');

        $response = $this->actingAs($this->admin)->put('/admin/pendaftaran/form-fields/' . $field->id_field, $this->dataField(['field_name' => 'hobi', 'field_label' => 'Hobi Kamu', 'is_active' => 1]));

        $response->assertRedirect(route('admin.pendaftaran.form-fields'));
        $this->assertDatabaseHas('form_fields', ['id_field' => $field->id_field, 'field_label' => 'Hobi Kamu']);
    }

    public function test_form_field_tanpa_centang_aktif_menjadi_nonaktif()
    {
        $field = $this->buatField('hobi');

        $this->actingAs($this->admin)->put('/admin/pendaftaran/form-fields/' . $field->id_field, $this->dataField(['field_name' => 'hobi']));

        $this->assertDatabaseHas('form_fields', ['id_field' => $field->id_field, 'is_active' => 0]);
    }

    public function test_ubah_nama_form_field_menjadi_nama_milik_field_lain()
    {
        $this->buatField('hobi');
        $field = $this->buatField('minat');

        $response = $this->actingAs($this->admin)->put('/admin/pendaftaran/form-fields/' . $field->id_field, $this->dataField(['field_name' => 'hobi']));

        $response->assertSessionHasErrors('field_name');
    }

    public function test_update_form_field_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->put('/admin/pendaftaran/form-fields/999', $this->dataField());

        $response->assertStatus(404);
    }

    // ---- destroyFormField() ----
    public function test_admin_menghapus_form_field()
    {
        $field = $this->buatField('hobi');

        $response = $this->actingAs($this->admin)->delete('/admin/pendaftaran/form-fields/' . $field->id_field);

        $response->assertRedirect(route('admin.pendaftaran.form-fields'));
        $this->assertDatabaseMissing('form_fields', ['id_field' => $field->id_field]);
    }

    public function test_hapus_form_field_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->delete('/admin/pendaftaran/form-fields/999');

        $response->assertStatus(404);
    }

    // ---- jenisBerkas() ----
    public function test_admin_melihat_daftar_jenis_berkas()
    {
        $this->buatJenis();

        $response = $this->actingAs($this->admin)->get('/admin/pendaftaran/jenis-berkas');

        $response->assertStatus(200);
        $response->assertViewHas('jenisBerkas', function ($j) {
            return $j->count() == 1;
        });
    }

    public function test_daftar_jenis_berkas_kosong()
    {
        $response = $this->actingAs($this->admin)->get('/admin/pendaftaran/jenis-berkas');

        $response->assertStatus(200);
    }

    // ---- storeJenisBerkas() ----
    public function test_admin_menambah_jenis_berkas()
    {
        $response = $this->actingAs($this->admin)->post('/admin/pendaftaran/jenis-berkas', $this->dataBerkas());

        $response->assertRedirect(route('admin.pendaftaran.jenis-berkas'));
        $this->assertDatabaseHas('jenis_berkas', ['nama_jenis' => 'Transkrip Nilai', 'max_size' => 2048]);
    }

    public function test_jenis_berkas_dengan_nama_yang_sudah_ada()
    {
        $this->buatJenis('Transkrip Nilai');

        $response = $this->actingAs($this->admin)->post('/admin/pendaftaran/jenis-berkas', $this->dataBerkas());

        $response->assertSessionHasErrors('nama_jenis');
    }

    // TC-UPLOAD-003 sampai TC-UPLOAD-005, batas ukuran yang boleh diatur admin 100 KB sampai 10 MB
    public function test_ukuran_maksimal_berkas_kurang_dari_100_kb_ditolak()
    {
        $response = $this->actingAs($this->admin)->post('/admin/pendaftaran/jenis-berkas', $this->dataBerkas(['max_size' => 99]));

        $response->assertSessionHasErrors('max_size');
    }

    public function test_ukuran_maksimal_berkas_lebih_dari_10_mb_ditolak()
    {
        $response = $this->actingAs($this->admin)->post('/admin/pendaftaran/jenis-berkas', $this->dataBerkas(['max_size' => 10241]));

        $response->assertSessionHasErrors('max_size');
    }

    public function test_ukuran_maksimal_berkas_tepat_10_mb_diterima()
    {
        $response = $this->actingAs($this->admin)->post('/admin/pendaftaran/jenis-berkas', $this->dataBerkas(['max_size' => 10240]));

        $response->assertSessionHasNoErrors();
    }

    // ---- updateJenisBerkas() ----
    public function test_admin_mengubah_jenis_berkas()
    {
        $jenis = $this->buatJenis('KTM');

        $response = $this->actingAs($this->admin)->put('/admin/pendaftaran/jenis-berkas/' . $jenis->id_jenis, $this->dataBerkas(['nama_jenis' => 'KTM Asli']));

        $response->assertRedirect(route('admin.pendaftaran.jenis-berkas'));
        $this->assertDatabaseHas('jenis_berkas', ['id_jenis' => $jenis->id_jenis, 'nama_jenis' => 'KTM Asli']);
    }

    public function test_ubah_jenis_berkas_dengan_nama_milik_jenis_lain()
    {
        $this->buatJenis('CV');
        $jenis = $this->buatJenis('KTM');

        $response = $this->actingAs($this->admin)->put('/admin/pendaftaran/jenis-berkas/' . $jenis->id_jenis, $this->dataBerkas(['nama_jenis' => 'CV']));

        $response->assertSessionHasErrors('nama_jenis');
    }

    public function test_update_jenis_berkas_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->put('/admin/pendaftaran/jenis-berkas/999', $this->dataBerkas());

        $response->assertStatus(404);
    }

    // ---- destroyJenisBerkas() ----
    public function test_admin_menghapus_jenis_berkas()
    {
        $jenis = $this->buatJenis();

        $response = $this->actingAs($this->admin)->delete('/admin/pendaftaran/jenis-berkas/' . $jenis->id_jenis);

        $response->assertRedirect(route('admin.pendaftaran.jenis-berkas'));
        $this->assertDatabaseMissing('jenis_berkas', ['id_jenis' => $jenis->id_jenis]);
    }

    public function test_hapus_jenis_berkas_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->delete('/admin/pendaftaran/jenis-berkas/999');

        $response->assertStatus(404);
    }
}
