<?php

namespace Database\Seeders;

use App\Models\Mapel;
use App\Models\Guru;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GuruMapelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Array of mapel and their guru(s)
        $data = [
            'Matematika' => ['Ibu Ratna'],
            'Bahasa Inggris' => ['Pak Doni'],
            'Fisika' => ['Pak Tono', 'Bu Lina'],
            'Kimia' => ['Bu Lina'],
            'Pemrograman' => ['Bu Siti', 'Pak Budi', 'Bu Hana'],
            'Database' => ['Pak Budi', 'Bu Siti'],
            'Desain Web' => ['Bu Hana'],
            'Jaringan Komputer' => ['Pak Ahmad'],
            'Manajemen Proyek' => ['Bu Maya'],
            'Bahasa Indonesia' => ['Pak Rudi'],
            'Sejarah' => ['Pak Edi'],
            'Pendidikan Jasmani' => ['Pak Bambang'],
        ];

        foreach ($data as $mapelName => $guruNames) {
            $mapel = Mapel::where('nama', $mapelName)->first();
            
            if ($mapel) {
                foreach ($guruNames as $guruName) {
                    $guru = Guru::where('nama', $guruName)->first();
                    if ($guru) {
                        $mapel->guru()->attach($guru->id);
                    }
                }
            }
        }
    }
}
