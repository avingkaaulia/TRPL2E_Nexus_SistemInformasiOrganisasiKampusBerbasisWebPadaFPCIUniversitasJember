<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\DatabaseTestCase;

class CarouselControllerTest extends DatabaseTestCase
{
    private $admin;
    private $kategori;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->admin = $this->buatAdmin();
        $this->kategori = $this->buatKategori('carousel_home');
    }

    private function dataSlide(array $ganti = [])
    {
        return array_merge([
            'title' => 'Selamat Datang di FPCI UNEJ',
            'description' => 'Wadah diskusi kebijakan luar negeri mahasiswa Universitas Jember',
            'featured_image' => $this->gambarPalsu('slide.jpg', 200),
            'status' => 'publish',
        ], $ganti);
    }

    private function slide(array $attr = [])
    {
        return $this->buatPost(array_merge(['id_post_category' => $this->kategori->id_category], $attr));
    }

    // ---- index() ----
    public function test_admin_melihat_daftar_carousel()
    {
        $response = $this->actingAs($this->admin)->get('/admin/carousel');

        $response->assertStatus(200);
        $response->assertViewIs('admin.carousel.index');
    }

    public function test_daftar_carousel_dikelompokkan_per_kategori()
    {
        $this->slide();
        $this->slide();

        $response = $this->actingAs($this->admin)->get('/admin/carousel');

        $response->assertViewHas('carouselsByCategory', function ($data) {
            return count($data[$this->kategori->id_category]) == 2;
        });
    }

    public function test_kategori_selain_carousel_tidak_ikut_ditampilkan()
    {
        $this->buatKategori('event');

        $response = $this->actingAs($this->admin)->get('/admin/carousel');

        $response->assertViewHas('carouselCategories', function ($c) {
            return $c->count() == 1;
        });
    }

    // ---- create() ----
    public function test_admin_melihat_form_tambah_slide()
    {
        $response = $this->actingAs($this->admin)->get('/admin/carousel/create/' . $this->kategori->id_category);

        $response->assertStatus(200);
    }

    public function test_form_tambah_slide_untuk_kategori_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->get('/admin/carousel/create/999');

        $response->assertStatus(404);
    }

    // ---- store() ----
    // TC-CAROUSEL-001
    public function test_admin_menambah_slide_carousel()
    {
        $response = $this->actingAs($this->admin)->post('/admin/carousel/store/' . $this->kategori->id_category, $this->dataSlide());

        $response->assertRedirect(route('admin.carousel'));
        $this->assertDatabaseHas('posts', [
            'title' => 'Selamat Datang di FPCI UNEJ',
            'id_post_category' => $this->kategori->id_category,
            'status' => 'publish',
        ]);
    }

    public function test_gambar_slide_tersimpan_di_storage()
    {
        $this->actingAs($this->admin)->post('/admin/carousel/store/' . $this->kategori->id_category, $this->dataSlide());

        Storage::disk('public')->assertExists(Post::first()->featured_image_path);
    }

    public function test_tambah_slide_tanpa_data()
    {
        $response = $this->actingAs($this->admin)->post('/admin/carousel/store/' . $this->kategori->id_category, []);

        $response->assertSessionHasErrors(['title', 'description', 'featured_image', 'status']);
    }

    public function test_deskripsi_slide_kurang_dari_10_karakter()
    {
        $response = $this->actingAs($this->admin)->post('/admin/carousel/store/' . $this->kategori->id_category, $this->dataSlide(['description' => 'pendek']));

        $response->assertSessionHasErrors('description');
    }

    // TC-CAROUSEL-004
    public function test_tambah_slide_dengan_file_bukan_gambar()
    {
        $pdf = UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->admin)->post('/admin/carousel/store/' . $this->kategori->id_category, $this->dataSlide(['featured_image' => $pdf]));

        $response->assertSessionHasErrors('featured_image');
    }

    // TC-CAROUSEL-006
    public function test_gambar_slide_lebih_dari_4mb_ditolak()
    {
        $response = $this->actingAs($this->admin)->post('/admin/carousel/store/' . $this->kategori->id_category, $this->dataSlide([
            'featured_image' => $this->gambarPalsu('besar.jpg', 4500),
        ]));

        $response->assertSessionHasErrors('featured_image');
    }

    public function test_status_slide_selain_publish_atau_draft_ditolak()
    {
        $response = $this->actingAs($this->admin)->post('/admin/carousel/store/' . $this->kategori->id_category, $this->dataSlide(['status' => 'pending']));

        $response->assertSessionHasErrors('status');
    }

    // ---- edit() ----
    public function test_admin_melihat_form_edit_slide()
    {
        $slide = $this->slide();

        $response = $this->actingAs($this->admin)->get('/admin/carousel/edit/' . $slide->id_post);

        $response->assertStatus(200);
    }

    public function test_edit_slide_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->get('/admin/carousel/edit/999');

        $response->assertStatus(404);
    }

    // ---- update() ----
    // TC-CAROUSEL-002
    public function test_admin_mengubah_slide_tanpa_ganti_gambar()
    {
        $slide = $this->slide();
        $data = $this->dataSlide(['title' => 'Judul Slide Baru']);
        unset($data['featured_image']);

        $response = $this->actingAs($this->admin)->put('/admin/carousel/update/' . $slide->id_post, $data);

        $response->assertRedirect(route('admin.carousel'));
        $this->assertDatabaseHas('posts', ['id_post' => $slide->id_post, 'title' => 'Judul Slide Baru']);
    }

    public function test_ganti_gambar_slide_menghapus_gambar_lama()
    {
        Storage::disk('public')->put('img/lama.jpg', 'x');
        $slide = $this->slide(['featured_image_path' => 'img/lama.jpg']);

        $this->actingAs($this->admin)->put('/admin/carousel/update/' . $slide->id_post, $this->dataSlide());

        Storage::disk('public')->assertMissing('img/lama.jpg');
        Storage::disk('public')->assertExists(Post::find($slide->id_post)->featured_image_path);
    }

    public function test_update_slide_dengan_judul_kosong()
    {
        $slide = $this->slide();
        $data = $this->dataSlide(['title' => '']);
        unset($data['featured_image']);

        $response = $this->actingAs($this->admin)->put('/admin/carousel/update/' . $slide->id_post, $data);

        $response->assertSessionHasErrors('title');
    }

    public function test_update_slide_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->put('/admin/carousel/update/999', []);

        $response->assertStatus(404);
    }

    // ---- destroy() ----
    // TC-CAROUSEL-003
    public function test_admin_menghapus_slide_beserta_file_gambarnya()
    {
        Storage::disk('public')->put('img/slide.jpg', 'x');
        $slide = $this->slide(['featured_image_path' => 'img/slide.jpg']);

        $response = $this->actingAs($this->admin)->delete('/admin/carousel/destroy/' . $slide->id_post);

        $response->assertRedirect(route('admin.carousel'));
        $this->assertSoftDeleted('posts', ['id_post' => $slide->id_post]);
        Storage::disk('public')->assertMissing('img/slide.jpg');
    }

    public function test_hapus_slide_yang_tidak_ada()
    {
        $response = $this->actingAs($this->admin)->delete('/admin/carousel/destroy/999');

        $response->assertStatus(404);
    }

    public function test_hapus_slide_tercatat_di_log()
    {
        $slide = $this->slide();

        $this->actingAs($this->admin)->delete('/admin/carousel/destroy/' . $slide->id_post);

        $this->assertDatabaseHas('activity_log', ['action' => 'delete', 'model_type' => 'Carousel']);
    }
}
