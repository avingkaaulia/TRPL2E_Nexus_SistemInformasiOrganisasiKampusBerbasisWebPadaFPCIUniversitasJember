<?php

namespace Tests\Unit;

use App\Models\Comment;
use Tests\DatabaseTestCase;

class CommentModelTest extends DatabaseTestCase
{
    // ---- post() ----
    public function test_komentar_terhubung_ke_post()
    {
        $post = $this->buatPost(['title' => 'Artikel Satu']);
        $komentar = $this->buatKomentar($post);

        $this->assertEquals('Artikel Satu', $komentar->post->title);
    }

    public function test_post_komentar_kosong_kalau_post_tidak_ada()
    {
        $post = $this->buatPost();
        $komentar = $this->buatKomentar($post, ['id_post' => 999]);

        $this->assertNull($komentar->post);
    }

    public function test_dua_komentar_satu_post()
    {
        $post = $this->buatPost();
        $a = $this->buatKomentar($post);
        $b = $this->buatKomentar($post, ['email' => 'lain@mail.com']);

        $this->assertEquals($a->post->id_post, $b->post->id_post);
    }

    // ---- scopeApproved() ----
    public function test_scope_approved_hanya_komentar_disetujui()
    {
        $post = $this->buatPost();
        $this->buatKomentar($post, ['status' => 'approved']);
        $this->buatKomentar($post, ['status' => 'pending']);
        $this->buatKomentar($post, ['status' => 'rejected']);

        $this->assertEquals(1, Comment::approved()->count());
    }

    public function test_scope_approved_kosong_kalau_belum_ada_yang_disetujui()
    {
        $post = $this->buatPost();
        $this->buatKomentar($post, ['status' => 'pending']);

        $this->assertEquals(0, Comment::approved()->count());
    }

    public function test_scope_approved_bisa_dihitung_lebih_dari_satu()
    {
        $post = $this->buatPost();
        $this->buatKomentar($post, ['status' => 'approved']);
        $this->buatKomentar($post, ['status' => 'approved', 'email' => 'x@mail.com']);

        $this->assertEquals(2, Comment::approved()->count());
    }

    // ---- scopePending() ----
    public function test_scope_pending_hanya_komentar_menunggu()
    {
        $post = $this->buatPost();
        $this->buatKomentar($post, ['status' => 'pending']);
        $this->buatKomentar($post, ['status' => 'approved']);

        $this->assertEquals(1, Comment::pending()->count());
    }

    public function test_scope_pending_kosong_kalau_semua_sudah_diproses()
    {
        $post = $this->buatPost();
        $this->buatKomentar($post, ['status' => 'approved']);
        $this->buatKomentar($post, ['status' => 'rejected']);

        $this->assertEquals(0, Comment::pending()->count());
    }

    public function test_scope_pending_bisa_dirangkai_dengan_where_lain()
    {
        $post = $this->buatPost();
        $this->buatKomentar($post, ['nama_pengunjung' => 'Andi']);
        $this->buatKomentar($post, ['nama_pengunjung' => 'Dewi', 'email' => 'dewi@mail.com']);

        $this->assertEquals(1, Comment::pending()->where('nama_pengunjung', 'Andi')->count());
    }

    // ---- scopeRejected() ----
    public function test_scope_rejected_hanya_komentar_ditolak()
    {
        $post = $this->buatPost();
        $this->buatKomentar($post, ['status' => 'rejected']);
        $this->buatKomentar($post, ['status' => 'pending']);

        $this->assertEquals(1, Comment::rejected()->count());
    }

    public function test_scope_rejected_kosong_kalau_tidak_ada_yang_ditolak()
    {
        $post = $this->buatPost();
        $this->buatKomentar($post, ['status' => 'approved']);

        $this->assertEquals(0, Comment::rejected()->count());
    }

    public function test_scope_rejected_tidak_mengembalikan_komentar_pending()
    {
        $post = $this->buatPost();
        $this->buatKomentar($post, ['status' => 'pending']);

        $this->assertCount(0, Comment::rejected()->get());
    }
}
