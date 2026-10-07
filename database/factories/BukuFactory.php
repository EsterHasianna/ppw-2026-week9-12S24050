<?php

namespace Database\Factories;

use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

class BukuFactory extends Factory
{
    public function definition(): array
    {
        return [
            'isbn' => $this->faker->unique()->isbn13(),
            'judul' => ucwords($this->faker->unique()->words(rand(3, 6), true)),
            'penulis' => $this->faker->name(),
            'penerbit' => $this->faker->randomElement([
                'Gramedia Pustaka Utama', 'Erlangga', 'Mizan', 'Andi Publisher',
                'Informatika Bandung', 'Elex Media Komputindo', 'Deepublish',
            ]),
            'tahun_terbit' => $this->faker->numberBetween(2005, 2025),
            'kategori_id' => Kategori::factory(),
            'stok' => $this->faker->numberBetween(0, 50),
            'sinopsis' => $this->faker->paragraph(3),
        ];
    }
}