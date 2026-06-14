<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seeders de datos demo (tracks, niveles). Ejecutar tras migrate.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
        ]);
    }
}
