<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\PostGallery;
use Illuminate\Support\Facades\Storage;
use Tests\DatabaseTestCase;

class AdminWritingsControllerTest extends DatabaseTestCase
{
    private $admin;
    private $writings;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->buatAdmin();
        $this->writings = $this->buatKategori('writings');
    }

    private function karya(array $attr = [])
    {
        return $this->buatPost(array_merge(['id_post_category' => $this->writings->id_category], $attr));
    }

    // ---- pending() ----
    public function test_admin_melihat_halaman_validasi_karya()
    {
        $response = $this->actingAs($this->admin)->get('/admin/writings/pending');

        $response->assertStatus(200);
        $response->assertViewIs('admin.writings.pending');
    }

    public function test_jumlah_karya_per_status_dihitung()
    {
        $this->karya(['status' => 'pending']);
        $this->karya(['status' => 'pending']);
        $this->karya(['status' => 'publish']);
        $this->karya(['status' => 'draft']);

        $response = $this->actingAs($this->admin)->get('/admin/writings/pending');

        $response->assertViewHas('totalPending', 2);
        $response->assertViewHas('totalPublished', 1);
        $response->assertViewHas('totalDraft', 1);
    }

    // TC-VALID-004
    public function test_halaman_validasi_saat_tidak_ada_karya_menunggu()
    {
        $response = $this->actingAs($this->admin)->get('/admin/writings/pending');

        $response->assertStatus(200);
        $response->assertViewHas('totalPending', 0);
    }

    public function test_karya_bisa_difilter_berdasarkan_status()
    {
        $this->karya(['status' => 'pending']);
        $this->karya(['status' => 'publish']);

        $response = $this->actingAs($this->admin)->get('/admin/writings/pending?status=pending');

        $response->assertViewHas('pendingPosts', function ($p) {
            return $p->total() == 1;
        });
    }

    public function test_karya_bisa_dicari_berdasarkan_judul()
    {
        $this->karya(['title' => 'Geopolitik Asia']);
        $this->karya(['title' => 'Ekonomi Hijau']);

        $response = $this->actingAs($this->admin)->get('/admin/writings/pending?search=Geopolitik');

        $response->assertViewHas('pendingPosts', function ($p) {
            return $p->total() == 1;
        });
    }

    // ---- approve() ----
    // TC-VALID-001
    public function test_admin_menyetujui_karya()
    {
        $post = $this->karya(['status' => 'pending']);

        $response = $this->actingAs($this->admin)->put('/admin/writings/approve/' . $post->id_post);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('posts', ['id_post' => $post->id_post, 'status' => 'publish']);
    }

    public function test_menyetujui_karya_tercatat_di_log()
    {
        $post = $this->karya(['status' => 'pending']);

        $this->actingAs($this->admin)->put('/admin/writings/approve/' . $post->id_post);

        $this->assertDatabaseHas('activity_log', ['action' => 'approve', 'model_type' => 'Writings']);
    }

    public function test_menyetujui_karya_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->put('/admin/writings/approve/999');

        $response->assertStatus(404);
    }

    // TC-VALID-003, karya yang sudah ditolak bisa disetujui lagi
    public function test_karya_yang_ditolak_bisa_disetujui_kembali()
    {
        $post = $this->karya(['status' => 'pending']);
        $this->actingAs($this->admin)->put('/admin/writings/reject/' . $post->id_post);

        $this->actingAs($this->admin)->put('/admin/writings/approve/' . $post->id_post);

        $this->assertDatabaseHas('posts', ['id_post' => $post->id_post, 'status' => 'publish']);
    }

    // ---- reject() ----
    // TC-VALID-002, ditolak = dikembalikan ke draft
    public function test_admin_menolak_karya()
    {
        $post = $this->karya(['status' => 'pending']);

        $response = $this->actingAs($this->admin)->put('/admin/writings/reject/' . $post->id_post);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('posts', ['id_post' => $post->id_post, 'status' => 'draft']);
    }

    // TC-PUB-003
    public function test_karya_yang_sudah_tampil_bisa_ditolak()
    {
        $post = $this->karya(['status' => 'publish']);

        $this->actingAs($this->admin)->put('/admin/writings/reject/' . $post->id_post);

        $this->assertDatabaseHas('posts', ['id_post' => $post->id_post, 'status' => 'draft']);
    }

    public function test_menolak_karya_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->put('/admin/writings/reject/999');

        $response->assertStatus(404);
    }

    // ---- forceDelete() ----
    // pesannya "dihapus permanen" tapi kode memakai delete() biasa, jadi datanya masih ada (soft delete)
    public function test_hapus_karya_memakai_soft_delete()
    {
        $post = $this->karya();

        $response = $this->actingAs($this->admin)->delete('/admin/writings/force-delete/' . $post->id_post);

        $response->assertSessionHas('success');
        $this->assertSoftDeleted('posts', ['id_post' => $post->id_post]);
    }

    public function test_hapus_karya_juga_menghapus_file_gambar_dan_gallery()
    {
        Storage::disk('public')->put('img/cover.jpg', 'x');
        Storage::disk('public')->put('gallery/g1.jpg', 'x');
        $post = $this->karya(['featured_image_path' => 'img/cover.jpg']);
        PostGallery::create(['id_post' => $post->id_post, 'image_path' => 'gallery/g1.jpg']);

        $this->actingAs($this->admin)->delete('/admin/writings/force-delete/' . $post->id_post);

        Storage::disk('public')->assertMissing('img/cover.jpg');
        Storage::disk('public')->assertMissing('gallery/g1.jpg');
        $this->assertDatabaseMissing('post_gallery', ['id_post' => $post->id_post]);
    }

    public function test_hapus_karya_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->delete('/admin/writings/force-delete/999');

        $response->assertStatus(404);
    }

    // ---- show() ----
    public function test_admin_melihat_detail_karya_yang_masih_pending()
    {
        $post = $this->karya(['status' => 'pending']);

        $response = $this->actingAs($this->admin)->get('/admin/writings/show/' . $post->id_post);

        $response->assertStatus(200);
        $response->assertViewIs('show');
    }

    public function test_detail_karya_admin_membawa_komentar()
    {
        $post = $this->karya();
        $this->buatKomentar($post);

        $response = $this->actingAs($this->admin)->get('/admin/writings/show/' . $post->id_post);

        $response->assertViewHas('comments', function ($c) {
            return $c->count() == 1;
        });
    }

    public function test_detail_karya_admin_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->get('/admin/writings/show/999');

        $response->assertStatus(404);
    }
}
