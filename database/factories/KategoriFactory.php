<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class KategoriFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kode_kategori' => strtoupper($this->faker->unique()->bothify('???-##')),
            'nama_kategori' => $this->faker->unique()->words(2, true),
        ];
    }
}