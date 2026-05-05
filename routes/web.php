<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PendaftaranController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\WritingController;
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

use App\Http\Controllers\HomeController;
use App\Http\Controllers\CommentController;

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
// Routes untuk Writings
Route::get('/writings', [WritingController::class, 'index'])->name('writings');
Route::get('/writings/category/{categoryId}', [WritingController::class, 'category'])->name('writings.category');
Route::get('/writings/{id}', [WritingController::class, 'show'])->name('writings.show');
// 🔥 DETAIL POSTINGAN UNTUK SEMUA KATEGORI (DINAMIS)
Route::get('/post/{id}', [PostController::class, 'show'])->name('post.show');
