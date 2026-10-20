<?php

namespace Tests\Unit;

use App\Models\FormField;
use Tests\DatabaseTestCase;

class FormFieldTest extends DatabaseTestCase
{
    private function buatField($nama, $aktif = 1, $urutan = 0, $opsi = null)
    {
        return FormField::create([
            'field_name' => $nama,
            'field_label' => ucfirst($nama),
            'field_type' => 'text',
            'field_options' => $opsi,
            'is_required' => 1,
            'sort_order' => $urutan,
            'is_active' => $aktif,
        ]);
    }

    // ---- getActiveFields() ----
    public function test_active_fields_hanya_yang_aktif()
    {
        $this->buatField('nama', 1);
        $this->buatField('nim', 1);
        $this->buatField('alamat', 0);

        $this->assertCount(2, FormField::getActiveFields());
    }

    public function test_active_fields_diurutkan_berdasarkan_sort_order()
    {
        $this->buatField('nim', 1, 3);
        $this->buatField('nama', 1, 1);
        $this->buatField('email', 1, 2);

        $nama = FormField::getActiveFields()->pluck('field_name')->toArray();

        $this->assertEquals(['nama', 'email', 'nim'], $nama);
    }

    public function test_active_fields_kosong_kalau_semua_nonaktif()
    {
        $this->buatField('nama', 0);

        $this->assertCount(0, FormField::getActiveFields());
    }

    // ---- getOptionsArrayAttribute() ----
    public function test_options_array_dari_opsi_yang_tersimpan()
    {
        $field = $this->buatField('jenis_kelamin', 1, 0, ['Laki-laki', 'Perempuan']);

        $this->assertEquals(['Laki-laki', 'Perempuan'], $field->options_array);
    }

    public function test_options_array_kosong_kalau_tidak_ada_opsi()
    {
        $field = $this->buatField('nama');

        $this->assertEquals([], $field->options_array);
    }

    public function test_options_array_kosong_kalau_array_opsi_kosong()
    {
        $field = $this->buatField('nama', 1, 0, []);

        $this->assertSame([], $field->options_array);
    }
}
