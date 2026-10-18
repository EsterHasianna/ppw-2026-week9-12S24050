<?php
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\KategoriController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('buku.index'));

Route::resource('buku', BukuController::class);
Route::resource('kategori', KategoriController::class);
Route::get('/peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
Route::post('/buku/{id}/pinjam', [PeminjamanController::class, 'pinjam'])->name('buku.pinjam');
Route::patch('/peminjaman/{id}/kembali', [PeminjamanController::class, 'kembali'])->name('peminjaman.kembali');