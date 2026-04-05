<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateKegiatanColorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Color palette (pastel colors like the example image)
        $colors = [
            ['warna' => '#FFE8E8', 'warna_teks' => '#C1272D'], // Red
            ['warna' => '#FFF3E0', 'warna_teks' => '#E65100'], // Orange
            ['warna' => '#FFF9C4', 'warna_teks' => '#F57F17'], // Yellow
            ['warna' => '#E8F5E9', 'warna_teks' => '#2E7D32'], // Green
            ['warna' => '#E3F2FD', 'warna_teks' => '#1565C0'], // Blue
            ['warna' => '#F3E5F5', 'warna_teks' => '#6A1B9A'], // Purple
            ['warna' => '#FCE4EC', 'warna_teks' => '#C2185B']  // Pink
        ];

        // Get all kegiatan ordered by ID
        $kegiatans = DB::table('kegiatan')->orderBy('id')->get();
        
        foreach ($kegiatans as $index => $kegiatan) {
            $colorIndex = $index % count($colors);
            DB::table('kegiatan')
                ->where('id', $kegiatan->id)
                ->update([
                    'warna' => $colors[$colorIndex]['warna'],
                    'warna_teks' => $colors[$colorIndex]['warna_teks']
                ]);
        }

        echo "✅ Kegiatan colors updated\n";
    }
}
