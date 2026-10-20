<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Setting;
use Tests\DatabaseTestCase;

class AdminCommentControllerTest extends DatabaseTestCase
{
    private $admin;
    private $post;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->buatAdmin(['nama' => 'Admin FPCI']);
        $this->post = $this->buatPost();
    }

    // ---- index() ----
    public function test_admin_melihat_daftar_komentar()
    {
        $this->buatKomentar($this->post);

        $response = $this->actingAs($this->admin)->get('/admin/comments');

        $response->assertStatus(200);
        $response->assertViewIs('admin.comments.index');
    }

    public function test_daftar_komentar_bisa_difilter_berdasarkan_status()
    {
        $this->buatKomentar($this->post, ['status' => 'approved']);
        $this->buatKomentar($this->post, ['status' => 'pending']);

        $response = $this->actingAs($this->admin)->get('/admin/comments?status=approved');

        $response->assertViewHas('comments', function ($c) {
            return $c->total() == 1;
        });
    }

    public function test_daftar_komentar_bisa_dicari_berdasarkan_nama()
    {
        $this->buatKomentar($this->post, ['nama_pengunjung' => 'Andi Pratama']);
        $this->buatKomentar($this->post, ['nama_pengunjung' => 'Dewi Lestari', 'email' => 'dewi@mail.com']);

        $response = $this->actingAs($this->admin)->get('/admin/comments?search=Andi');

        $response->assertViewHas('comments', function ($c) {
            return $c->total() == 1;
        });
    }

    public function test_statistik_komentar_dihitung_per_status()
    {
        $this->buatKomentar($this->post, ['status' => 'pending']);
        $this->buatKomentar($this->post, ['status' => 'approved']);
        $this->buatKomentar($this->post, ['status' => 'rejected']);

        $response = $this->actingAs($this->admin)->get('/admin/comments');

        $response->assertViewHas('totalComments', 3);
        $response->assertViewHas('pendingComments', 1);
        $response->assertViewHas('unrepliedComments', 1);
    }

    // ---- approve() ----
    // TC-COMMENT-002
    public function test_admin_menyetujui_komentar_pending()
    {
        $komentar = $this->buatKomentar($this->post);

        $response = $this->actingAs($this->admin)->post('/admin/comments/' . $komentar->id_comment . '/approve');

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('comments', ['id_comment' => $komentar->id_comment, 'status' => 'approved']);
    }

    public function test_menyetujui_komentar_yang_sudah_disetujui()
    {
        $komentar = $this->buatKomentar($this->post, ['status' => 'approved']);

        $response = $this->actingAs($this->admin)->post('/admin/comments/' . $komentar->id_comment . '/approve');

        $response->assertSessionHas('error', 'Komentar sudah disetujui sebelumnya!');
    }

    public function test_menyetujui_komentar_yang_sudah_ditolak()
    {
        $komentar = $this->buatKomentar($this->post, ['status' => 'rejected']);

        $response = $this->actingAs($this->admin)->post('/admin/comments/' . $komentar->id_comment . '/approve');

        $response->assertSessionHas('error', 'Komentar sudah ditolak sebelumnya!');
    }

    public function test_menyetujui_komentar_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->post('/admin/comments/999/approve');

        $response->assertSessionHas('error', 'Komentar tidak ditemukan!');
    }

    public function test_menyetujui_komentar_tercatat_di_log()
    {
        $komentar = $this->buatKomentar($this->post);

        $this->actingAs($this->admin)->post('/admin/comments/' . $komentar->id_comment . '/approve');

        $this->assertDatabaseHas('activity_log', ['action' => 'approve', 'model_type' => 'Comment']);
    }

    // ---- reject() ----
    public function test_admin_menolak_komentar_pending()
    {
        $komentar = $this->buatKomentar($this->post);

        $response = $this->actingAs($this->admin)->post('/admin/comments/' . $komentar->id_comment . '/reject');

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('comments', ['id_comment' => $komentar->id_comment, 'status' => 'rejected']);
    }

    public function test_menolak_komentar_yang_sudah_disetujui()
    {
        $komentar = $this->buatKomentar($this->post, ['status' => 'approved']);

        $response = $this->actingAs($this->admin)->post('/admin/comments/' . $komentar->id_comment . '/reject');

        $response->assertSessionHas('error', 'Komentar sudah disetujui sebelumnya!');
    }

    public function test_menolak_komentar_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->post('/admin/comments/999/reject');

        $response->assertSessionHas('error', 'Komentar tidak ditemukan!');
    }

    // ---- reply() ----
    public function test_admin_membalas_komentar()
    {
        $komentar = $this->buatKomentar($this->post, ['status' => 'approved']);

        $response = $this->actingAs($this->admin)->post('/admin/comments/' . $komentar->id_comment . '/reply', ['reply' => 'Terima kasih atas masukannya']);

        $response->assertSessionHas('success', 'Balasan berhasil ditambahkan!');
        $this->assertDatabaseHas('comments', [
            'id_comment' => $komentar->id_comment,
            'reply' => 'Terima kasih atas masukannya',
            'reply_by' => 'Admin FPCI',
            'is_replied' => 1,
        ]);
    }

    public function test_balasan_komentar_kosong_ditolak()
    {
        $komentar = $this->buatKomentar($this->post);

        $response = $this->actingAs($this->admin)->post('/admin/comments/' . $komentar->id_comment . '/reply', ['reply' => '']);

        $response->assertSessionHasErrors('reply');
    }

    public function test_balasan_kurang_dari_3_karakter_ditolak()
    {
        $komentar = $this->buatKomentar($this->post);

        $response = $this->actingAs($this->admin)->post('/admin/comments/' . $komentar->id_comment . '/reply', ['reply' => 'ok']);

        $response->assertSessionHasErrors('reply');
    }

    public function test_membalas_komentar_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->post('/admin/comments/999/reply', ['reply' => 'Halo semua']);

        $response->assertStatus(404);
    }

    // ---- destroy() ----
    public function test_admin_menghapus_komentar()
    {
        $komentar = $this->buatKomentar($this->post);

        $response = $this->actingAs($this->admin)->delete('/admin/comments/' . $komentar->id_comment);

        $response->assertRedirect(route('admin.comments.index'));
        $this->assertDatabaseMissing('comments', ['id_comment' => $komentar->id_comment]);
    }

    public function test_menghapus_komentar_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->delete('/admin/comments/999');

        $response->assertStatus(404);
    }

    public function test_menghapus_komentar_tercatat_di_log()
    {
        $komentar = $this->buatKomentar($this->post);

        $this->actingAs($this->admin)->delete('/admin/comments/' . $komentar->id_comment);

        $this->assertDatabaseHas('activity_log', ['action' => 'delete', 'model_type' => 'Comment']);
    }

    // ---- toggleComments() ----
    public function test_admin_menonaktifkan_fitur_komentar()
    {
        $response = $this->actingAs($this->admin)->post('/admin/comments/toggle');

        $response->assertSessionHas('success', 'Fitur komentar berhasil dinonaktifkan.');
        $this->assertFalse(Setting::isCommentsEnabled());
    }

    public function test_admin_mengaktifkan_kembali_fitur_komentar()
    {
        Setting::toggleComments(false);

        $response = $this->actingAs($this->admin)->post('/admin/comments/toggle');

        $response->assertSessionHas('success', 'Fitur komentar berhasil diaktifkan.');
        $this->assertTrue(Setting::isCommentsEnabled());
    }

    public function test_toggle_komentar_dua_kali_kembali_ke_keadaan_semula()
    {
        $this->actingAs($this->admin)->post('/admin/comments/toggle');
        $this->actingAs($this->admin)->post('/admin/comments/toggle');

        $this->assertTrue(Setting::isCommentsEnabled());
    }
}
