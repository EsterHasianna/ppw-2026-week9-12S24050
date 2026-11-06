<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\Soap\LibrarySoapController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('buku.index'));

// Publik: login & logout
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Publik: hanya melihat katalog
Route::get('/buku', [BukuController::class, 'index'])->name('buku.index');

// Semua rute mutasi diproteksi auth
Route::middleware('auth')->group(function () {
    Route::resource('buku', BukuController::class)->except(['index', 'show']);
    Route::resource('kategori', KategoriController::class);

    Route::get('/peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
    Route::post('/buku/{id}/pinjam', [PeminjamanController::class, 'pinjam'])->name('buku.pinjam');
    Route::patch('/peminjaman/{id}/kembali', [PeminjamanController::class, 'kembali'])->name('peminjaman.kembali');
});

// show buku dipasang setelah grup agar /buku/create tidak tertimpa {buku}
Route::get('/buku/{buku}', [BukuController::class, 'show'])->name('buku.show');

// SOAP: layanan legacy untuk cek bebas pustaka (CSRF dikecualikan di bootstrap/app.php)
Route::get('/soap/sipus.wsdl', [LibrarySoapController::class, 'wsdl']);
Route::post('/soap/sipus', [LibrarySoapController::class, 'handle']);