<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Tests\DatabaseTestCase;

class WritingsSubmitControllerTest extends DatabaseTestCase
{
    private function dataKarya($kategori, array $ganti = [])
    {
        return array_merge([
            'title' => 'Peran Diplomasi Digital di Era Modern',
            'post_content' => str_repeat('Diplomasi digital semakin penting. ', 3),
            'id_post_category' => $kategori->id_category,
        ], $ganti);
    }

    private function kategoriKarya()
    {
        $writings = $this->buatKategori('writings');

        return $this->buatKategori('opini', $writings->id_category);
    }

    // ---- create() ----
    public function test_halaman_kirim_karya_butuh_login()
    {
        $response = $this->get('/writings/submit');

        $response->assertRedirect(route('login'));
    }

    public function test_anggota_login_melihat_form_kirim_karya()
    {
        $response = $this->actingAs($this->buatAnggotaUser())->get('/writings/submit');

        $response->assertStatus(200);
    }

    public function test_pilihan_kategori_hanya_sub_kategori_writings()
    {
        $writings = $this->buatKategori('writings');
        $this->buatKategori('opini', $writings->id_category);
        $this->buatKategori('esai', $writings->id_category);
        $this->buatKategori('event');

        $response = $this->actingAs($this->buatAnggotaUser())->get('/writings/submit');

        $response->assertViewHas('categories', function ($c) {
            return $c->count() == 2;
        });
    }

    public function test_pilihan_kategori_kosong_kalau_kategori_writings_belum_ada()
    {
        $response = $this->actingAs($this->buatAnggotaUser())->get('/writings/submit');

        $response->assertViewHas('categories', function ($c) {
            return $c->count() == 0;
        });
    }

    // ---- store() ----
    // TC-KARYA-001
    public function test_anggota_mengirim_karya_dengan_data_valid()
    {
        $anggota = $this->buatAnggotaUser();
        $kategori = $this->kategoriKarya();

        $response = $this->actingAs($anggota)->post('/writings/submit', $this->dataKarya($kategori));

        $response->assertRedirect(route('writings.submit'));
        $this->assertDatabaseHas('posts', [
            'title' => 'Peran Diplomasi Digital di Era Modern',
            'status' => 'pending',
            'id_user' => $anggota->id_user,
        ]);
    }

    public function test_kirim_karya_tanpa_login_diarahkan_ke_login()
    {
        $kategori = $this->kategoriKarya();

        $response = $this->post('/writings/submit', $this->dataKarya($kategori));

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('posts', 0);
    }

    // TC-KARYA-002
    public function test_kirim_karya_tanpa_judul_kategori_dan_konten()
    {
        $response = $this->actingAs($this->buatAnggotaUser())->post('/writings/submit', []);

        $response->assertSessionHasErrors(['title', 'post_content', 'id_post_category']);
    }

    public function test_konten_karya_kurang_dari_50_karakter()
    {
        $kategori = $this->kategoriKarya();

        $response = $this->actingAs($this->buatAnggotaUser())->post('/writings/submit', $this->dataKarya($kategori, ['post_content' => 'terlalu pendek']));

        $response->assertSessionHasErrors('post_content');
    }

    public function test_judul_karya_lebih_dari_150_karakter()
    {
        $kategori = $this->kategoriKarya();

        $response = $this->actingAs($this->buatAnggotaUser())->post('/writings/submit', $this->dataKarya($kategori, ['title' => str_repeat('a', 151)]));

        $response->assertSessionHasErrors('title');
    }

    public function test_kategori_karya_yang_tidak_ada()
    {
        $response = $this->actingAs($this->buatAnggotaUser())->post('/writings/submit', [
            'title' => 'Judul',
            'post_content' => str_repeat('isi ', 20),
            'id_post_category' => 999,
        ]);

        $response->assertSessionHasErrors('id_post_category');
    }


    public function test_featured_image_dibawah_batas_tersimpan()
    {
        $kategori = $this->kategoriKarya();

        $this->actingAs($this->buatAnggotaUser())->post('/writings/submit', $this->dataKarya($kategori, [
            'featured_image' => $this->gambarPalsu('cover.jpg', 4000),
        ]));

        $post = Post::first();

        $this->assertTrue(
            Storage::disk('public')->exists($post->featured_image_path)
        );
    }


    // TC-KARYA-003, batas di kode 4048 KB jadi file 4 MB (4096 KB) ikut ditolak
    public function test_featured_image_4096kb_ditolak_karena_batas_kode_4048kb()
    {
        $kategori = $this->kategoriKarya();

        $response = $this->actingAs($this->buatAnggotaUser())->post('/writings/submit', $this->dataKarya($kategori, [
            'featured_image' => $this->gambarPalsu('cover.jpg', 4096),
        ]));

        $response->assertSessionHasErrors('featured_image');
    }

    // TC-KARYA-004
    public function test_featured_image_lebih_dari_4mb_ditolak()
    {
        $kategori = $this->kategoriKarya();

        $response = $this->actingAs($this->buatAnggotaUser())->post('/writings/submit', $this->dataKarya($kategori, [
            'featured_image' => $this->gambarPalsu('cover.jpg', 5000),
        ]));

        $response->assertSessionHasErrors('featured_image');
    }

    public function test_gambar_gallery_ikut_tersimpan_beserta_deskripsinya()
    {
        $kategori = $this->kategoriKarya();

        $this->actingAs($this->buatAnggotaUser())->post('/writings/submit', $this->dataKarya($kategori, [
            'gallery_images' => [$this->gambarPalsu('g1.jpg', 100)],
            'gallery_descriptions' => ['Suasana diskusi'],
        ]));

        $this->assertDatabaseHas('post_gallery', ['description' => 'Suasana diskusi']);
    }
}
