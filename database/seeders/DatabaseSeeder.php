<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Database\Seeder;

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
    }
}