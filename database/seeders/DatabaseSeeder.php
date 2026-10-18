<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $kategoris = collect([
            ['TEK-01', 'Teknologi Informasi'],
            ['SAN-02', 'Sains dan Matematika'],
            ['SOS-03', 'Ilmu Sosial'],
            ['SAS-04', 'Sastra dan Fiksi'],
            ['BIS-05', 'Bisnis dan Manajemen'],
        ])->map(fn ($k) => Kategori::create([
            'kode_kategori' => $k[0],
            'nama_kategori' => $k[1],
        ]));

        Buku::factory()
            ->count(20)
            ->state(fn () => ['kategori_id' => $kategoris->random()->id])
            ->create();

        // ---- Minggu 6: akun pustakawan ----
        User::updateOrCreate(
            ['email' => 'pustakawan@del.ac.id'],
            ['name' => 'Pustakawan Del', 'password' => Hash::make('password123')]
        );

        // ---- Minggu 6: buku uji stok 0 dan stok 1 ----
        $bukuHabis = Buku::create([
            'isbn' => '9786020000001',
            'judul' => 'Algoritma dan Struktur Data (Stok Habis)',
            'penulis' => 'Tim Dosen Del',
            'penerbit' => 'Del Press',
            'tahun_terbit' => 2024,
            'kategori_id' => $kategoris[0]->id,
            'stok' => 0,
            'sinopsis' => 'Buku uji: stok 0 untuk membuktikan penolakan dan rollback transaksi.',
        ]);

        $bukuTerakhir = Buku::create([
            'isbn' => '9786020000002',
            'judul' => 'Basis Data Lanjutan (Stok Tinggal 1)',
            'penulis' => 'Tim Dosen Del',
            'penerbit' => 'Del Press',
            'tahun_terbit' => 2025,
            'kategori_id' => $kategoris[0]->id,
            'stok' => 1,
            'sinopsis' => 'Buku uji: stok 1 untuk membuktikan peminjaman terakhir dan race condition.',
        ]);

        // ---- Sampel transaksi (stok dijaga konsisten) ----
        // 1) Buku stok 0 sedang dipinjam (satu-satunya eksemplar)
        Peminjaman::create([
            'kode_pinjam' => 'PJM-20261010-001',
            'buku_id' => $bukuHabis->id,
            'nim_peminjam' => '12S24001',
            'nama_peminjam' => 'Budi Santoso',
            'tanggal_pinjam' => now()->subDays(8)->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(6)->toDateString(),
            'status' => 'dipinjam',
        ]);

        // 2) Riwayat sudah dikembalikan (stok tidak berubah)
        Peminjaman::create([
            'kode_pinjam' => 'PJM-20261001-002',
            'buku_id' => $bukuTerakhir->id,
            'nim_peminjam' => '12S24002',
            'nama_peminjam' => 'Sari Lestari',
            'tanggal_pinjam' => now()->subDays(17)->toDateString(),
            'tanggal_jatuh_tempo' => now()->subDays(3)->toDateString(),
            'tanggal_kembali' => now()->subDays(5)->toDateString(),
            'status' => 'dikembalikan',
        ]);

        // 3) Satu buku acak sedang dipinjam, stok dikurangi
        $bukuAcak = Buku::where('stok', '>', 0)
            ->where('id', '!=', $bukuTerakhir->id)
            ->first();

        Peminjaman::create([
            'kode_pinjam' => 'PJM-20261012-003',
            'buku_id' => $bukuAcak->id,
            'nim_peminjam' => '12S24003',
            'nama_peminjam' => 'Rudi Hartono',
            'tanggal_pinjam' => now()->subDays(6)->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(8)->toDateString(),
            'status' => 'dipinjam',
        ]);
        $bukuAcak->decrement('stok');
    }
}