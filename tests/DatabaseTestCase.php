<?php

namespace Tests;

use App\Models\Anggota;
use App\Models\Comment;
use App\Models\Contact;
use App\Models\Divisi;
use App\Models\Menu;
use App\Models\Pendaftaran;
use App\Models\PeriodePendaftaran;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Schema\Blueprint;

/**
 * Base class untuk semua test yang butuh database.
 * Migration di proyek ini baru mencakup 5 tabel, jadi tabel lainnya
 * dibuat manual di sini (sqlite in-memory) supaya database asli tidak kena.
 */
abstract class DatabaseTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->buatSemuaTabel();
        Storage::fake('public');

        DB::table('roles')->insert([
            ['id_role' => 1, 'nama_role' => 'admin'],
            ['id_role' => 2, 'nama_role' => 'anggota'],
        ]);
    }

    protected function buatSemuaTabel()
    {
        Schema::create('roles', function (Blueprint $t) {
            $t->id('id_role');
            $t->string('nama_role', 20);
        });

        Schema::create('users', function (Blueprint $t) {
            $t->id('id_user');
            $t->string('username', 50);
            $t->string('email', 100);
            $t->string('password', 255);
            $t->string('nama', 100);
            $t->unsignedBigInteger('id_role');
            $t->date('tanggal_daftar')->nullable();
            $t->string('reset_token')->nullable();
            $t->dateTime('reset_token_expires')->nullable();
            $t->string('remember_token', 100)->nullable();
        });

        Schema::create('post_category', function (Blueprint $t) {
            $t->id('id_category');
            $t->string('category_name', 50);
            $t->unsignedBigInteger('parent_id')->nullable();
        });

        Schema::create('posts', function (Blueprint $t) {
            $t->id('id_post');
            $t->string('title', 150);
            $t->string('meta_title')->nullable();
            $t->text('meta_description')->nullable();
            $t->text('content');
            $t->unsignedBigInteger('id_post_category');
            $t->string('post_type', 10)->default('post');
            $t->unsignedBigInteger('id_user');
            $t->dateTime('date_published')->nullable();
            $t->string('status', 10)->default('draft');
            $t->string('featured_image_path', 255)->nullable();
            $t->softDeletes();
        });

        Schema::create('post_gallery', function (Blueprint $t) {
            $t->id('id_gallery');
            $t->unsignedBigInteger('id_post');
            $t->string('image_path', 255);
            $t->string('description', 255)->nullable();
        });

        Schema::create('comments', function (Blueprint $t) {
            $t->id('id_comment');
            $t->unsignedBigInteger('id_post');
            $t->string('nama_pengunjung', 100);
            $t->string('email', 100);
            $t->text('isi_komentar');
            $t->dateTime('tanggal')->nullable();
            $t->text('reply')->nullable();
            $t->string('reply_by', 100)->nullable();
            $t->dateTime('reply_date')->nullable();
            $t->boolean('is_replied')->default(0);
            $t->string('status', 10)->default('pending');
        });

        Schema::create('settings', function (Blueprint $t) {
            $t->id('id_setting');
            $t->string('setting_key', 100);
            $t->text('setting_value')->nullable();
            $t->string('setting_type', 20)->nullable();
            $t->timestamps();
        });

        Schema::create('activity_log', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('user_name', 100)->nullable();
            $t->string('action', 50);
            $t->text('description')->nullable();
            $t->string('model_type', 100)->nullable();
            $t->unsignedBigInteger('model_id')->nullable();
            $t->string('ip_address', 50)->nullable();
            $t->text('user_agent')->nullable();
            $t->dateTime('created_at')->nullable();
        });

        Schema::create('menu', function (Blueprint $t) {
            $t->id('id_menu');
            $t->string('menu_label', 255);
            $t->unsignedBigInteger('id_menu_parent')->default(0);
            $t->string('link', 255)->nullable();
        });

        Schema::create('contact', function (Blueprint $t) {
            $t->id('id_contact');
            $t->string('email', 100)->nullable();
            $t->string('no_hp', 20)->nullable();
            $t->text('alamat')->nullable();
            $t->string('instagram', 100)->nullable();
            $t->string('linkedin', 100)->nullable();
            $t->string('tiktok', 100)->nullable();
            $t->string('youtube', 100)->nullable();
            $t->string('x', 100)->nullable();
        });

        Schema::create('divisi', function (Blueprint $t) {
            $t->id('id_divisi');
            $t->string('nama_divisi', 100);
        });

        Schema::create('anggota', function (Blueprint $t) {
            $t->id('id_anggota');
            $t->unsignedBigInteger('id_user');
            $t->unsignedBigInteger('id_divisi');
            $t->string('jabatan', 100);
            $t->string('periode', 20);
            $t->string('foto', 255)->nullable();
            $t->integer('no_urut')->nullable();
            $t->string('link', 255)->nullable();
        });

        Schema::create('periode_pendaftaran', function (Blueprint $t) {
            $t->id('id_periode');
            $t->string('tahun_ajaran', 20);
            $t->string('nama_periode', 50);
            $t->date('tanggal_mulai');
            $t->date('tanggal_selesai');
            $t->boolean('is_active')->default(0);
            $t->integer('kuota')->default(0);
            $t->text('deskripsi')->nullable();
        });

        Schema::create('pendaftaran', function (Blueprint $t) {
            $t->id('id_pendaftaran');
            $t->unsignedBigInteger('id_periode')->nullable();
            $t->string('status', 20)->default('menunggu');
            $t->dateTime('tanggal_daftar')->nullable();
            $t->string('nama', 100)->nullable();
            $t->string('email', 100)->nullable();
            $t->string('nim', 20)->nullable();
            $t->string('no_hp', 20)->nullable();
            $t->string('divisi', 100)->nullable();
        });

        Schema::create('jenis_berkas', function (Blueprint $t) {
            $t->id('id_jenis');
            $t->string('nama_jenis', 100);
            $t->boolean('is_required')->default(0);
            $t->string('file_type', 20);
            $t->integer('max_size');
        });

        Schema::create('berkas_pendaftaran', function (Blueprint $t) {
            $t->id('id_berkas');
            $t->unsignedBigInteger('id_pendaftaran');
            $t->unsignedBigInteger('id_jenis');
            $t->string('file_path', 255);
        });

        Schema::create('form_fields', function (Blueprint $t) {
            $t->id('id_field');
            $t->string('field_name', 50);
            $t->string('field_label', 100);
            $t->string('field_type', 20);
            $t->text('field_options')->nullable();
            $t->boolean('is_required')->default(0);
            $t->string('placeholder', 255)->nullable();
            $t->integer('sort_order')->default(0);
            $t->boolean('is_active')->default(1);
        });

        Schema::create('pendaftaran_config', function (Blueprint $t) {
            $t->integer('id_config')->primary();
            $t->boolean('is_open')->default(0);
            $t->text('welcome_text')->nullable();
            $t->text('closing_text')->nullable();
            $t->dateTime('updated_at')->nullable();
        });
    }

    // ---------- helper pembuat data ----------

    protected function buatAdmin(array $attr = [])
    {
        return User::factory()->admin()->create($attr);
    }

    protected function buatAnggotaUser(array $attr = [])
    {
        return User::factory()->anggota()->create($attr);
    }

    protected function buatKategori($nama, $parentId = null)
    {
        return PostCategory::create(['category_name' => $nama, 'parent_id' => $parentId]);
    }

    protected function buatPost(array $attr = [])
    {
        return Post::factory()->create($attr);
    }

    protected function buatKomentar($post, array $attr = [])
    {
        return Comment::create(array_merge([
            'id_post' => $post->id_post,
            'nama_pengunjung' => 'Budi Santoso',
            'email' => 'budi@mail.com',
            'isi_komentar' => 'Tulisannya bagus sekali',
            'tanggal' => now()->subHour(),
            'is_replied' => 0,
            'status' => 'pending',
        ], $attr));
    }

    protected function buatMenu($label, $parent = 0, $link = '/about')
    {
        return Menu::create(['menu_label' => $label, 'id_menu_parent' => $parent, 'link' => $link]);
    }

    protected function buatKontak(array $attr = [])
    {
        return Contact::create(array_merge([
            'email' => 'fpci.unej@gmail.com',
            'no_hp' => '081200000000',
            'alamat' => 'Jl. Kalimantan No.37, Sumbersari, Jember',
            'instagram' => 'fpciunej',
        ], $attr));
    }

    protected function buatDivisi($nama = 'Hubungan Masyarakat')
    {
        return Divisi::create(['nama_divisi' => $nama]);
    }

    protected function buatAnggotaData($user, $divisi, array $attr = [])
    {
        return Anggota::create(array_merge([
            'id_user' => $user->id_user,
            'id_divisi' => $divisi->id_divisi,
            'jabatan' => 'Staff',
            'periode' => '2025/2026',
            'foto' => 'assets/img/avatars/default-avatar.png',
            'no_urut' => 1,
            'link' => '',
        ], $attr));
    }

    protected function buatPeriode(array $attr = [])
    {
        return PeriodePendaftaran::create(array_merge([
            'tahun_ajaran' => '2026/2027',
            'nama_periode' => 'Open Recruitment Gelombang 1',
            'tanggal_mulai' => now()->subDays(3)->toDateString(),
            'tanggal_selesai' => now()->addDays(10)->toDateString(),
            'is_active' => 1,
            'kuota' => 20,
            'deskripsi' => 'Pendaftaran anggota baru FPCI UNEJ',
        ], $attr));
    }

    protected function buatPendaftar(array $attr = [])
    {
        static $urut = 0;
        $urut++;

        return Pendaftaran::create(array_merge([
            'id_periode' => null,
            'status' => 'menunggu',
            'tanggal_daftar' => now(),
            'nama' => 'Calon Anggota ' . $urut,
            'email' => 'calon' . $urut . '@mail.com',
            'nim' => '24000' . $urut,
            'no_hp' => '0812000000' . $urut,
            'divisi' => null,
        ], $attr));
    }

    // field form pendaftaran yang aktif: nama, email, nim, no_hp
    protected function isiFormFieldDefault()
    {
        DB::table('form_fields')->insert([
            ['field_name' => 'nama', 'field_label' => 'Nama Lengkap', 'field_type' => 'text', 'is_required' => 1, 'sort_order' => 1, 'is_active' => 1],
            ['field_name' => 'email', 'field_label' => 'Email', 'field_type' => 'email', 'is_required' => 1, 'sort_order' => 2, 'is_active' => 1],
            ['field_name' => 'nim', 'field_label' => 'NIM', 'field_type' => 'text', 'is_required' => 1, 'sort_order' => 3, 'is_active' => 1],
            ['field_name' => 'no_hp', 'field_label' => 'No HP', 'field_type' => 'tel', 'is_required' => 0, 'sort_order' => 4, 'is_active' => 1],
        ]);
    }

    protected function bukaPendaftaran($buka = 1)
    {
        DB::table('pendaftaran_config')->insert([
            'id_config' => 1,
            'is_open' => $buka,
            'welcome_text' => 'Selamat datang calon anggota',
            'closing_text' => 'Pendaftaran Ditutup',
        ]);
    }

    // file gambar palsu untuk tes upload (ukuran dalam KB, tidak perlu ekstensi GD)
    protected function gambarPalsu($nama = 'foto.jpg', $ukuranKb = 100)
    {
        return UploadedFile::fake()->create($nama, $ukuranKb, 'image/jpeg');
    }

    protected function postDiKategori($namaKategori, array $attr = [])
    {
        $kategori = PostCategory::where('category_name', $namaKategori)->first() ?? $this->buatKategori($namaKategori);

        return $this->buatPost(array_merge(['id_post_category' => $kategori->id_category], $attr));
    }
}
