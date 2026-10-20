<?php

namespace Tests\Feature;

use App\Models\Setting;
use Tests\DatabaseTestCase;

class CommentControllerTest extends DatabaseTestCase
{
    private function dataKomentar(array $ganti = [])
    {
        return array_merge([
            'nama' => 'Budi Santoso',
            'email' => 'budi@mail.com',
            'isi_komentar' => 'Tulisan yang sangat bermanfaat',
        ], $ganti);
    }

    // TC-COMMENT-001
    public function test_pengunjung_mengirim_komentar_dengan_status_pending()
    {
        $post = $this->buatPost();

        $response = $this->post('/post/' . $post->id_post . '/comments', $this->dataKomentar());

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('comments', [
            'id_post' => $post->id_post,
            'nama_pengunjung' => 'Budi Santoso',
            'status' => 'pending',
        ]);
    }

    public function test_komentar_ditolak_kalau_fitur_komentar_dinonaktifkan()
    {
        Setting::toggleComments(false);
        $post = $this->buatPost();

        $response = $this->post('/post/' . $post->id_post . '/comments', $this->dataKomentar());

        $response->assertSessionHas('error', 'Fitur komentar sedang dinonaktifkan oleh admin.');
        $this->assertDatabaseCount('comments', 0);
    }

    // TC-COMMENT-004
    public function test_komentar_kosong_ditolak()
    {
        $post = $this->buatPost();

        $response = $this->post('/post/' . $post->id_post . '/comments', []);

        $response->assertSessionHasErrors(['nama', 'email', 'isi_komentar']);
    }

    public function test_komentar_kurang_dari_3_karakter_ditolak()
    {
        $post = $this->buatPost();

        $response = $this->post('/post/' . $post->id_post . '/comments', $this->dataKomentar(['isi_komentar' => 'ok']));

        $response->assertSessionHasErrors('isi_komentar');
    }

    // TC-COMMENT-005
    public function test_komentar_tepat_1000_karakter_diterima()
    {
        $post = $this->buatPost();

        $response = $this->post('/post/' . $post->id_post . '/comments', $this->dataKomentar(['isi_komentar' => str_repeat('a', 1000)]));

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('comments', 1);
    }

    public function test_komentar_1001_karakter_ditolak()
    {
        $post = $this->buatPost();

        $response = $this->post('/post/' . $post->id_post . '/comments', $this->dataKomentar(['isi_komentar' => str_repeat('a', 1001)]));

        $response->assertSessionHasErrors('isi_komentar');
    }

    public function test_format_email_komentar_salah_ditolak()
    {
        $post = $this->buatPost();

        $response = $this->post('/post/' . $post->id_post . '/comments', $this->dataKomentar(['email' => 'budi-at-mail']));

        $response->assertSessionHasErrors('email');
    }

    public function test_email_sama_tidak_boleh_komentar_lagi_dalam_1_menit()
    {
        $post = $this->buatPost();
        $this->buatKomentar($post, ['email' => 'budi@mail.com', 'tanggal' => now()->subSeconds(20)]);

        $response = $this->post('/post/' . $post->id_post . '/comments', $this->dataKomentar());

        $response->assertSessionHas('error', 'Tunggu 1 menit sebelum mengirim komentar lagi.');
        $this->assertDatabaseCount('comments', 1);
    }

    public function test_email_sama_boleh_komentar_lagi_setelah_lewat_1_menit()
    {
        $post = $this->buatPost();
        $this->buatKomentar($post, ['email' => 'budi@mail.com', 'tanggal' => now()->subMinutes(5)]);

        $this->post('/post/' . $post->id_post . '/comments', $this->dataKomentar());

        $this->assertDatabaseCount('comments', 2);
    }

    // TC-COMMENT-006, tag script tersimpan apa adanya dan menunggu moderasi
    public function test_komentar_berisi_tag_script_tetap_pending()
    {
        $post = $this->buatPost();

        $this->post('/post/' . $post->id_post . '/comments', $this->dataKomentar([
            'isi_komentar' => '<script>alert("x")</script>Halo',
        ]));

        $this->assertDatabaseHas('comments', [
            'isi_komentar' => '<script>alert("x")</script>Halo',
            'status' => 'pending',
        ]);
    }

    public function test_spasi_di_awal_dan_akhir_komentar_dibuang()
    {
        $post = $this->buatPost();

        $this->post('/post/' . $post->id_post . '/comments', $this->dataKomentar(['isi_komentar' => '   komentar rapi   ']));

        $this->assertDatabaseHas('comments', ['isi_komentar' => 'komentar rapi']);
    }
}
