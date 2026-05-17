<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\WritingsController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PendaftaranController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\CarouselController;
use App\Http\Controllers\Admin\PostAdminController;
use App\Http\Controllers\Admin\AdminPendaftaranController;
use App\Http\Controllers\Admin\AdminAnggotaController;
use App\Http\Controllers\Admin\AdminPageController;
use App\Http\Controllers\Admin\AdminMenuController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminCommentController;
use App\Http\Controllers\WritingsSubmitController;
use App\Http\Controllers\Admin\AdminWritingsController;
use App\Http\Controllers\Admin\AdminContactController;
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

Route::get('/', [HomeController::class, 'index'])->name('home');
// ========== ROUTE AUTHENTICATION ==========
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Profile routes (untuk user yang sudah login)
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});
// routes/web.php
Route::get('/post/{id_post}/comments', [CommentController::class, 'index'])->name('comments.index');
Route::post('/post/{id_post}/comments', [CommentController::class, 'store'])->name('comments.store');
Route::post('/post/{id_post}/comments', [CommentController::class, 'store'])->name('comments.store');
// ========== ROUTE PENCARIAN ==========
Route::get('/search', [SearchController::class, 'search'])->name('search');
Route::get('/search/autocomplete', [SearchController::class, 'autocomplete'])->name('search.autocomplete');
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
// Writings Submit Routes (User)
Route::get('/writings/submit', [WritingsSubmitController::class, 'create'])->name('writings.submit');
Route::post('/writings/submit', [WritingsSubmitController::class, 'store'])->name('writings.submit.store');
// 🔥 WRITINGS ROUTES (Semua mengarah ke show.blade.php)
Route::get('/writings', [WritingsController::class, 'index'])->name('writings');
Route::get('/writings/all', [WritingsController::class, 'all'])->name('writings.all');
Route::get('/writings/category/{categoryId}', [WritingsController::class, 'category'])->name('writings.category');
Route::get('/writings/{id}', [WritingsController::class, 'show'])->name('writings.show');

// 🔥 DETAIL POST UNTUK SEMUA KATEGORI (menggunakan show.blade.php)
Route::get('/post/{id}', [PostController::class, 'show'])->name('post.show');
// ========== PAGE ROUTES ==========
// Halaman Page
Route::get('/page/{id}', [PageController::class, 'show'])->name('page.show');
Route::get('/pages', [PageController::class, 'all'])->name('pages.index');

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
    Route::get('/anggota/{id}', [AdminAnggotaController::class, 'show'])->name('admin.anggota.show');
    Route::get('/anggota/{id}/edit', [AdminAnggotaController::class, 'edit'])->name('admin.anggota.edit');
    Route::put('/anggota/{id}', [AdminAnggotaController::class, 'update'])->name('admin.anggota.update');
    Route::delete('/anggota/{id}', [AdminAnggotaController::class, 'destroy'])->name('admin.anggota.destroy');
    Route::post('/anggota/convert/{id}', [AdminAnggotaController::class, 'convertFromPendaftaran'])->name('admin.anggota.convert');
});

// Admin Pages Routes
Route::prefix('admin')->group(function () {
    Route::get('/pages', [AdminPageController::class, 'index'])->name('admin.pages.list');
});
// Admin Menu Routes
Route::prefix('admin')->group(function () {
    Route::get('/menu', [AdminMenuController::class, 'index'])->name('admin.menu.index');
    Route::get('/menu/create', [AdminMenuController::class, 'create'])->name('admin.menu.create');
    Route::post('/menu', [AdminMenuController::class, 'store'])->name('admin.menu.store');
    Route::get('/menu/{id}/edit', [AdminMenuController::class, 'edit'])->name('admin.menu.edit');
    Route::put('/menu/{id}', [AdminMenuController::class, 'update'])->name('admin.menu.update');
    Route::delete('/menu/{id}', [AdminMenuController::class, 'destroy'])->name('admin.menu.destroy');
});
// Admin Category Routes
Route::prefix('admin')->group(function () {
    Route::get('/categories', [AdminCategoryController::class, 'index'])->name('admin.categories.index');
    Route::get('/categories/create', [AdminCategoryController::class, 'create'])->name('admin.categories.create');
    Route::post('/categories', [AdminCategoryController::class, 'store'])->name('admin.categories.store');
    Route::get('/categories/{id}/edit', [AdminCategoryController::class, 'edit'])->name('admin.categories.edit');
    Route::put('/categories/{id}', [AdminCategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('/categories/{id}', [AdminCategoryController::class, 'destroy'])->name('admin.categories.destroy');
});

// Admin Comment Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/comments', [AdminCommentController::class, 'index'])->name('comments.index');
    Route::post('/comments/{id}/reply', [AdminCommentController::class, 'reply'])->name('comments.reply');
    Route::delete('/comments/{id}', [AdminCommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('/comments/bulk', [AdminCommentController::class, 'bulkAction'])->name('comments.bulk');
});
// Admin Writings Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/writings/pending', [AdminWritingsController::class, 'pending'])->name('writings.pending');
    Route::get('/writings/show/{id}', [AdminWritingsController::class, 'show'])->name('writings.show');
    Route::put('/writings/approve/{id}', [AdminWritingsController::class, 'approve'])->name('writings.approve');
    Route::put('/writings/reject/{id}', [AdminWritingsController::class, 'reject'])->name('writings.reject');
    Route::delete('/writings/force-delete/{id}', [AdminWritingsController::class, 'forceDelete'])->name('writings.force-delete');
});

// Admin Contact Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/contact', [AdminContactController::class, 'index'])->name('contact.index');
    Route::get('/contact/edit', [AdminContactController::class, 'edit'])->name('contact.edit');
    Route::put('/contact/update', [AdminContactController::class, 'update'])->name('contact.update');
});