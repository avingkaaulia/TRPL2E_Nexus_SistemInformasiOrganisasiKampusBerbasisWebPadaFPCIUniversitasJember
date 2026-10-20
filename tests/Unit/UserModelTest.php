<?php

namespace Tests\Unit;

use Tests\DatabaseTestCase;

class UserModelTest extends DatabaseTestCase
{
    // ---- role() ----
    public function test_role_user_admin_terhubung_ke_tabel_roles()
    {
        $admin = $this->buatAdmin();

        $this->assertEquals('admin', $admin->role->nama_role);
    }

    public function test_role_user_anggota_terhubung_ke_tabel_roles()
    {
        $anggota = $this->buatAnggotaUser();

        $this->assertEquals('anggota', $anggota->role->nama_role);
    }

    public function test_role_kosong_kalau_id_role_tidak_ada()
    {
        $user = $this->buatAnggotaUser(['id_role' => 99]);

        $this->assertNull($user->role);
    }

    // ---- isAdmin() ----
    public function test_isAdmin_true_untuk_role_1()
    {
        $admin = $this->buatAdmin();

        $this->assertTrue($admin->isAdmin());
    }

    public function test_isAdmin_false_untuk_anggota()
    {
        $anggota = $this->buatAnggotaUser();

        $this->assertFalse($anggota->isAdmin());
    }

    public function test_isAdmin_false_untuk_role_yang_tidak_dikenal()
    {
        $user = $this->buatAnggotaUser(['id_role' => 7]);

        $this->assertFalse($user->isAdmin());
    }

    // ---- isAnggota() ----
    public function test_isAnggota_true_untuk_role_2()
    {
        $anggota = $this->buatAnggotaUser();

        $this->assertTrue($anggota->isAnggota());
    }

    public function test_isAnggota_false_untuk_admin()
    {
        $admin = $this->buatAdmin();

        $this->assertFalse($admin->isAnggota());
    }

    public function test_isAnggota_false_untuk_role_yang_tidak_dikenal()
    {
        $user = $this->buatAdmin(['id_role' => 5]);

        $this->assertFalse($user->isAnggota());
    }
}
