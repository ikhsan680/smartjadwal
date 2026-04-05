<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

       $this->call([
        Userseeder::class,
        KelasSeeder::class,
        GuruSeeder::class,
        MapelSeeder::class,
        GuruMapelSeeder::class,
        KegiatanSeeder::class,
        AllKelasJadwalSeeder::class,
        ]);
    }
}
