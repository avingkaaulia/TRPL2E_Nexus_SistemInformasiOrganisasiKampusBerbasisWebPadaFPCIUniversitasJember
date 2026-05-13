<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\WritingsController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PendaftaranController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\CarouselController;
use App\Http\Controllers\Admin\PostAdminController;
use App\Http\Controllers\Admin\AdminPendaftaranController;
use App\Http\Controllers\Admin\AdminAnggotaController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [HomeController::class,'index']);
// routes/web.php
Route::get('/post/{id_post}/comments', [CommentController::class, 'index'])->name('comments.index');
Route::post('/post/{id_post}/comments', [CommentController::class, 'store'])->name('comments.store');
// ROUTE UNTUK TESTING COMMENT (TANPA MENUNGGU TEMAN)
// Route::get('/post/{id}', [App\Http\Controllers\CommentController::class, 'testShow'])->name('test.post');
// Route::post('/post/{id_post}/comments', [App\Http\Controllers\CommentController::class, 'store'])->name('test.comments.store');
Route::post('/post/{id_post}/comments', [CommentController::class, 'store'])->name('comments.store');

// Pendaftaran Routes
Route::get('/pendaftaran', [PendaftaranController::class, 'index'])->name('pendaftaran');
Route::post('/pendaftaran', [PendaftaranController::class, 'store'])->name('pendaftaran.store');
Route::get('/cek-pendaftaran/{email}', [PendaftaranController::class, 'cekStatus']);
// About Routes (Dinamis)
Route::get('/about', [AboutController::class, 'index'])->name('about');
// Contact Routes
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
// 🔥 KEGIATAN ROUTES (Semua mengarah ke show.blade.php)
// KEGIATAN ROUTES
Route::get('/events', [KegiatanController::class, 'index'])->name('kegiatan.index');
Route::get('/events/event/reguler', [KegiatanController::class, 'allEventReguler'])->name('kegiatan.event.reguler');
Route::get('/events/event/unggulan', [KegiatanController::class, 'allEventUnggulan'])->name('kegiatan.event.unggulan');
Route::get('/events/programs/{status}', [KegiatanController::class, 'allPrograms'])->name('kegiatan.programs');
Route::get('/events/{id}', [KegiatanController::class, 'show'])->name('kegiatan.show');

// 🔥 WRITINGS ROUTES (Semua mengarah ke show.blade.php)
Route::get('/writings', [WritingsController::class, 'index'])->name('writings');
Route::get('/writings/all', [WritingsController::class, 'all'])->name('writings.all');
Route::get('/writings/category/{categoryId}', [WritingsController::class, 'category'])->name('writings.category');
Route::get('/writings/{id}', [WritingsController::class, 'show'])->name('writings.show');

// 🔥 DETAIL POST UNTUK SEMUA KATEGORI (menggunakan show.blade.php)
Route::get('/post/{id}', [PostController::class, 'show'])->name('post.show');

// Admin Routes
Route::prefix('admin')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    
    // Carousel Routes
    Route::get('/carousel', [CarouselController::class, 'index'])->name('admin.carousel');
    Route::get('/carousel/create/{categoryId}', [CarouselController::class, 'create'])->name('admin.carousel.create');
    Route::post('/carousel/store/{categoryId}', [CarouselController::class, 'store'])->name('admin.carousel.store');
    Route::get('/carousel/edit/{id}', [CarouselController::class, 'edit'])->name('admin.carousel.edit');
    Route::put('/carousel/update/{id}', [CarouselController::class, 'update'])->name('admin.carousel.update');
    Route::delete('/carousel/destroy/{id}', [CarouselController::class, 'destroy'])->name('admin.carousel.destroy');
});

// Admin Post Routes
Route::prefix('admin')->group(function () {
    Route::get('/posts', [PostAdminController::class, 'index'])->name('admin.posts.index');
    Route::get('/posts/create', [PostAdminController::class, 'create'])->name('admin.posts.create');
    Route::post('/posts/store', [PostAdminController::class, 'store'])->name('admin.posts.store');
    Route::get('/posts/edit/{id}', [PostAdminController::class, 'edit'])->name('admin.posts.edit');
    Route::put('/posts/update/{id}', [PostAdminController::class, 'update'])->name('admin.posts.update');
    Route::delete('/posts/destroy/{id}', [PostAdminController::class, 'destroy'])->name('admin.posts.destroy');
    Route::delete('/posts/gallery/{id}', [PostAdminController::class, 'deleteGallery'])->name('admin.posts.gallery.delete');
});

// Admin Pendaftaran Routes
Route::prefix('admin')->group(function () {

    // ✅ ROUTE SPESIFIK DULU (sebelum {id})
    Route::get('/pendaftaran/config', [AdminPendaftaranController::class, 'config'])->name('admin.pendaftaran.config');
    Route::put('/pendaftaran/config', [AdminPendaftaranController::class, 'updateConfig'])->name('admin.pendaftaran.config.update');

    Route::get('/pendaftaran/periode', [AdminPendaftaranController::class, 'periode'])->name('admin.pendaftaran.periode');
    Route::post('/pendaftaran/periode', [AdminPendaftaranController::class, 'storePeriode'])->name('admin.pendaftaran.periode.store');
    Route::put('/pendaftaran/periode/{id}', [AdminPendaftaranController::class, 'updatePeriode'])->name('admin.pendaftaran.periode.update');
    Route::delete('/pendaftaran/periode/{id}', [AdminPendaftaranController::class, 'destroyPeriode'])->name('admin.pendaftaran.periode.destroy');

    Route::get('/pendaftaran/form-fields', [AdminPendaftaranController::class, 'formFields'])->name('admin.pendaftaran.form-fields');
    Route::post('/pendaftaran/form-fields', [AdminPendaftaranController::class, 'storeFormField'])->name('admin.pendaftaran.form-fields.store');
    Route::put('/pendaftaran/form-fields/{id}', [AdminPendaftaranController::class, 'updateFormField'])->name('admin.pendaftaran.form-fields.update');
    Route::delete('/pendaftaran/form-fields/{id}', [AdminPendaftaranController::class, 'destroyFormField'])->name('admin.pendaftaran.form-fields.destroy');

    Route::get('/pendaftaran/jenis-berkas', [AdminPendaftaranController::class, 'jenisBerkas'])->name('admin.pendaftaran.jenis-berkas');
    Route::post('/pendaftaran/jenis-berkas', [AdminPendaftaranController::class, 'storeJenisBerkas'])->name('admin.pendaftaran.jenis-berkas.store');
    Route::put('/pendaftaran/jenis-berkas/{id}', [AdminPendaftaranController::class, 'updateJenisBerkas'])->name('admin.pendaftaran.jenis-berkas.update');
    Route::delete('/pendaftaran/jenis-berkas/{id}', [AdminPendaftaranController::class, 'destroyJenisBerkas'])->name('admin.pendaftaran.jenis-berkas.destroy');

    Route::get('/pendaftaran/download-berkas/{id}', [AdminPendaftaranController::class, 'downloadBerkas'])->name('admin.pendaftaran.download-berkas');

    // ✅ ROUTE INDEX
    Route::get('/pendaftaran', [AdminPendaftaranController::class, 'index'])->name('admin.pendaftaran.index');

    // ⚠️ ROUTE {id} HARUS PALING BAWAH
    Route::get('/pendaftaran/{id}', [AdminPendaftaranController::class, 'show'])->name('admin.pendaftaran.show');
    Route::put('/pendaftaran/update-status/{id}', [AdminPendaftaranController::class, 'updateStatus'])->name('admin.pendaftaran.update-status');
    Route::delete('/pendaftaran/{id}', [AdminPendaftaranController::class, 'destroy'])->name('admin.pendaftaran.destroy');
    // 🔥 TAMBAH ROUTE UNTUK DITERIMA DAN DITOLAK
    Route::put('/pendaftaran/accept/{id}', [AdminPendaftaranController::class, 'accept'])->name('admin.pendaftaran.accept');
    Route::put('/pendaftaran/reject/{id}', [AdminPendaftaranController::class, 'reject'])->name('admin.pendaftaran.reject');
});

// Admin Anggota Routes
Route::prefix('admin')->group(function () {
    Route::get('/anggota', [AdminAnggotaController::class, 'index'])->name('admin.anggota.index');
    Route::get('/anggota/create', [AdminAnggotaController::class, 'create'])->name('admin.anggota.create');
    Route::post('/anggota', [AdminAnggotaController::class, 'store'])->name('admin.anggota.store');
    Route::get('/anggota/{id}', [AdminAnggotaController::class, 'show'])->name('admin.anggota.show');
    Route::get('/anggota/{id}/edit', [AdminAnggotaController::class, 'edit'])->name('admin.anggota.edit');
    Route::put('/anggota/{id}', [AdminAnggotaController::class, 'update'])->name('admin.anggota.update');
    Route::delete('/anggota/{id}', [AdminAnggotaController::class, 'destroy'])->name('admin.anggota.destroy');
    Route::post('/anggota/convert/{id}', [AdminAnggotaController::class, 'convertFromPendaftaran'])->name('admin.anggota.convert');
});