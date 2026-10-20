<?php

namespace Tests\Feature;

use Tests\DatabaseTestCase;

class KegiatanControllerTest extends DatabaseTestCase
{
    // ---- index() ----
    // TC-EVENT-001
    public function test_melihat_halaman_kegiatan()
    {
        $response = $this->get('/events');

        $response->assertStatus(200);
        $response->assertViewIs('kegiatan.index');
    }

    // TC-EVENT-002
    public function test_kegiatan_dikelompokkan_berdasarkan_status_program()
    {
        $this->postDiKategori('Sedang Direncanakan');
        $this->postDiKategori('Sedang Berlangsung');
        $this->postDiKategori('Selesai');

        $response = $this->get('/events');

        $response->assertViewHas('programsPlanned', function ($p) { return $p->count() == 1; });
        $response->assertViewHas('programsOngoing', function ($p) { return $p->count() == 1; });
        $response->assertViewHas('programsCompleted', function ($p) { return $p->count() == 1; });
    }

    public function test_event_reguler_di_halaman_kegiatan_dibatasi_3_post()
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postDiKategori('Event Reguler');
        }

        $response = $this->get('/events');

        $response->assertViewHas('eventRegulerPosts', function ($p) {
            return $p->count() == 3;
        });
    }

    // TC-EVENT-003, kegiatan tanpa foto dokumentasi
    public function test_kegiatan_tanpa_foto_tetap_tampil()
    {
        $this->postDiKategori('Event Unggulan', ['featured_image_path' => null]);

        $response = $this->get('/events');

        $response->assertStatus(200);
        $response->assertViewHas('eventUnggulanPosts', function ($p) {
            return $p->count() == 1;
        });
    }

    // ---- allEventReguler() ----
    public function test_melihat_semua_event_reguler()
    {
        $this->postDiKategori('Event Reguler');

        $response = $this->get('/events/event/reguler');

        $response->assertStatus(200);
        $response->assertViewIs('kegiatan.all');
    }

    public function test_event_reguler_diurutkan_judul_a_ke_z()
    {
        $this->postDiKategori('Event Reguler', ['title' => 'Zebra Talk']);
        $this->postDiKategori('Event Reguler', ['title' => 'Alpha Forum']);

        $response = $this->get('/events/event/reguler?sort=az');

        $response->assertViewHas('posts', function ($p) {
            return $p->first()->title == 'Alpha Forum';
        });
    }

    public function test_event_reguler_dipaginasi_9_per_halaman()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->postDiKategori('Event Reguler');
        }

        $response = $this->get('/events/event/reguler');

        $response->assertViewHas('posts', function ($p) {
            return $p->count() == 9 && $p->total() == 10;
        });
    }

    // ---- allEventUnggulan() ----
    public function test_melihat_semua_event_unggulan()
    {
        $this->postDiKategori('Event Unggulan');

        $response = $this->get('/events/event/unggulan');

        $response->assertStatus(200);
    }

    public function test_event_unggulan_diurutkan_terlama()
    {
        $this->postDiKategori('Event Unggulan', ['title' => 'Baru', 'date_published' => now()->toDateString()]);
        $this->postDiKategori('Event Unggulan', ['title' => 'Lama', 'date_published' => now()->subYear()->toDateString()]);

        $response = $this->get('/events/event/unggulan?sort=terlama');

        $response->assertViewHas('posts', function ($p) {
            return $p->first()->title == 'Lama';
        });
    }

    public function test_event_unggulan_kosong_tetap_tampil_tanpa_error()
    {
        $response = $this->get('/events/event/unggulan');

        $response->assertStatus(200);
        $response->assertViewHas('posts', function ($p) {
            return $p->count() == 0;
        });
    }

    // ---- allPrograms() ----
    public function test_melihat_semua_program_dalam_satu_kategori()
    {
        $kategori = $this->buatKategori('Selesai');
        $this->buatPost(['id_post_category' => $kategori->id_category]);

        $response = $this->get('/events/programs/' . $kategori->id_category);

        $response->assertStatus(200);
        $response->assertViewHas('category');
    }

    public function test_program_kategori_tidak_ada_menghasilkan_404()
    {
        $response = $this->get('/events/programs/999');

        $response->assertStatus(404);
    }

    public function test_program_diurutkan_judul_z_ke_a()
    {
        $kategori = $this->buatKategori('Selesai');
        $this->buatPost(['id_post_category' => $kategori->id_category, 'title' => 'Alpha']);
        $this->buatPost(['id_post_category' => $kategori->id_category, 'title' => 'Zulu']);

        $response = $this->get('/events/programs/' . $kategori->id_category . '?sort=za');

        $response->assertViewHas('posts', function ($p) {
            return $p->first()->title == 'Zulu';
        });
    }

    // ---- show() ----
    public function test_melihat_detail_kegiatan()
    {
        $post = $this->postDiKategori('Event Reguler');

        $response = $this->get('/events/' . $post->id_post);

        $response->assertStatus(200);
        $response->assertViewIs('show');
    }

    // TC-EVENT-004
    public function test_detail_kegiatan_dengan_id_yang_tidak_ada()
    {
        $response = $this->get('/events/9999');

        $response->assertStatus(404);
    }

    public function test_detail_kegiatan_draft_tidak_bisa_dibuka_pengunjung()
    {
        $post = $this->postDiKategori('Event Reguler', ['status' => 'draft']);

        $response = $this->get('/events/' . $post->id_post);

        $response->assertStatus(404);
    }

    public function test_detail_kegiatan_membawa_komentarnya()
    {
        $post = $this->postDiKategori('Event Reguler');
        $this->buatKomentar($post);

        $response = $this->get('/events/' . $post->id_post);

        $response->assertViewHas('comments', function ($c) {
            return $c->count() == 1;
        });
    }
}
