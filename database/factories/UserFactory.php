<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    // tabel users di proyek ini pakai kolom nama, username, id_role (bukan name seperti bawaan laravel)
    public function definition()
    {
        return [
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password123'),
            'nama' => fake()->name(),
            'id_role' => 2,
            'tanggal_daftar' => now()->toDateString(),
        ];
    }

    public function admin()
    {
        return $this->state(fn (array $attributes) => ['id_role' => 1]);
    }

    public function anggota()
    {
        return $this->state(fn (array $attributes) => ['id_role' => 2]);
    }
}
