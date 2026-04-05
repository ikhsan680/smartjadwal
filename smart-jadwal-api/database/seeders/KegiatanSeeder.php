<?php

namespace Database\Seeders;

use App\Models\Kegiatan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KegiatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kegiatans = [
            ['nama' => 'Upacara'],
            ['nama' => 'Muhadoroh/Literasi & Sholat Duha'],
            ['nama' => 'Pembacaan Almatsurat & Sholat Duha'],
            ['nama' => 'Mentoring'],
            ['nama' => 'Pembacaan Almatsurat'],
            ['nama' => 'Senam Bersama'],
            ['nama' => 'Tahfidz'],
            ['nama' => 'Istirahat'],
        ];

        foreach ($kegiatans as $kegiatan) {
            Kegiatan::create($kegiatan);
        }
    }
}
