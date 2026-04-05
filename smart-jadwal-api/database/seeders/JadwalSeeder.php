<?php

namespace Database\Seeders;

use App\Models\Jadwal;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JadwalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // JADWAL X PPLG - Kelas ID 1
        // Jam: 07:00 - 15:00 (Pulang jam 15:00 / 3 sore)
        
        $kelas = 1; // X PPLG
        
        // ==========================
        // SENIN
        // ==========================
        // 07:00-08:00: Upacara
        Jadwal::create([
            'kelas_id' => $kelas,
            'kegiatan_id' => 1, // Upacara
            'hari' => 'Senin',
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:00',
        ]);
        
        // 08:00-10:00: Matematika
        Jadwal::create([
            'kelas_id' => $kelas,
            'mapel_id' => 16, // Matematika
            'guru_id' => 7, // Cahyono, S.Pd
            'hari' => 'Senin',
            'jam_mulai' => '08:00',
            'jam_selesai' => '09:00',
        ]);
        
        // 10:00-12:00: Bahasa Inggris
        Jadwal::create([
            'kelas_id' => $kelas,
            'mapel_id' => 1, // B. Inggris
            'guru_id' => 1, // Isti Qomah, S.Pd.
            'hari' => 'Senin',
            'jam_mulai' => '09:00',
            'jam_selesai' => '12:00',
        ]);
        
        Jadwal::create([
            'kelas_id' => $kelas,
            'kegiatan_id' => 8, // Istirahat
            'hari' => 'Senin',
            'jam_mulai' => '12:00',
            'jam_selesai' => '13:00',
        ]);
        
        // 13:00-15:00: Pemrograman Javascript
        Jadwal::create([
            'kelas_id' => $kelas,
            'mapel_id' => 30, // Pemrograman Javascript
            'guru_id' => 12, // Ade Adjie Ferijal, S.T.
            'hari' => 'Senin',
            'jam_mulai' => '13:00',
            'jam_selesai' => '15:00',
        ]);
        
        // ==========================
        // SELASA
        // ==========================
        // 07:00-08:00: Muhadoroh & Sholat Duha
        Jadwal::create([
            'kelas_id' => $kelas,
            'kegiatan_id' => 2, // Muhadoroh/Literasi & Sholat Duha
            'hari' => 'Selasa',
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:00',
        ]);
        
        // 08:00-10:00: Pemrograman Javascript
        Jadwal::create([
            'kelas_id' => $kelas,
            'mapel_id' => 30, // Pemrograman Javascript
            'guru_id' => 12, // Ade Adjie Ferijal, S.T.
            'hari' => 'Selasa',
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
        ]);
        
        // 10:00-12:00: PKn
        Jadwal::create([
            'kelas_id' => $kelas,
            'mapel_id' => 41, // PKn
            'guru_id' => 22, // Alisa Nur Fazria, S.Pd
            'hari' => 'Selasa',
            'jam_mulai' => '10:00',
            'jam_selesai' => '12:00',
        ]);
        
        // 12:00-13:00: Istirahat
         Jadwal::create([
            'kelas_id' => $kelas,
            'kegiatan_id' => 8, // Istirahat
            'hari' => 'Selasa',
            'jam_mulai' => '12:00',
            'jam_selesai' => '13:00',
        ]);
        
        // 13:00-15:00: Bahasa Arab
        Jadwal::create([
            'kelas_id' => $kelas,
            'mapel_id' => 33, // B. Arab
            'guru_id' => 14, // Ayi Solihudin, S.Pd.
            'hari' => 'Selasa',
            'jam_mulai' => '13:00',
            'jam_selesai' => '15:00',
        ]);
        
        // ==========================
        // RABU
        // ==========================
        // 07:00-08:00: Pembacaan Almatsurat & Sholat Duha + Mentoring
        Jadwal::create([
            'kelas_id' => $kelas,
            'kegiatan_id' => 3, // Pembacaan Almatsurat & Sholat Duha
            'hari' => 'Rabu',
            'jam_mulai' => '07:00',
            'jam_selesai' => '07:30',
        ]);
        
        Jadwal::create([
            'kelas_id' => $kelas,
            'kegiatan_id' => 4, // Mentoring
            'hari' => 'Rabu',
            'jam_mulai' => '07:30',
            'jam_selesai' => '08:00',
        ]);
        
        // 08:00-10:00: Pemrograman Javascript
        Jadwal::create([
            'kelas_id' => $kelas,
            'mapel_id' => 30, // Pemrograman Javascript
            'guru_id' => 12, // Ade Adjie Ferijal, S.T.
            'hari' => 'Rabu',
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
        ]);
        
        // 10:00-12:00: Basis Data
        Jadwal::create([
            'kelas_id' => $kelas,
            'mapel_id' => 32, // Basis Data
            'guru_id' => 12, // Ade Adjie Ferijal, S.T.
            'hari' => 'Rabu',
            'jam_mulai' => '10:00',
            'jam_selesai' => '12:00',
        ]);
        
         Jadwal::create([
            'kelas_id' => $kelas,
            'kegiatan_id' => 8, // Istirahat
            'hari' => 'Rabu',
            'jam_mulai' => '12:00',
            'jam_selesai' => '13:00',
        ]);
        
        // 13:00-15:00: Pemrograman Web Dasar
        Jadwal::create([
            'kelas_id' => $kelas,
            'mapel_id' => 31, // Pemrograman Web dasar
            'guru_id' => 12, // Ade Adjie Ferijal, S.T.
            'hari' => 'Rabu',
            'jam_mulai' => '13:00',
            'jam_selesai' => '15:00',
        ]);
        
        // ==========================
        // KAMIS
        // ==========================
        // 07:00-08:00: Pembacaan Almatsurat
        Jadwal::create([
            'kelas_id' => $kelas,
            'kegiatan_id' => 5, // Pembacaan Almatsurat di kelas
            'hari' => 'Kamis',
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:00',
        ]);
        
        // 08:00-10:00: PAI
        Jadwal::create([
            'kelas_id' => $kelas,
            'mapel_id' => 24, // PAI
            'guru_id' => 9, // Siti Nurjanah, S.Pd.
            'hari' => 'Kamis',
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
        ]);
        
        // 10:00-12:00: BK
        Jadwal::create([
            'kelas_id' => $kelas,
            'mapel_id' => 39, // BK
            'guru_id' => 23, // Abdullah Azzam
            'hari' => 'Kamis',
            'jam_mulai' => '10:00',
            'jam_selesai' => '12:00',
        ]);
        
        // 12:00-13:00: Istirahat
         Jadwal::create([
            'kelas_id' => $kelas,
            'kegiatan_id' => 8, // Istirahat
            'hari' => 'Kamis',
            'jam_mulai' => '12:00',
            'jam_selesai' => '13:00',
        ]);
        
        // 13:00-15:00: Pemrograman Javascript
        Jadwal::create([
            'kelas_id' => $kelas,
            'mapel_id' => 30, // Pemrograman Javascript
            'guru_id' => 12, // Ade Adjie Ferijal, S.T.
            'hari' => 'Kamis',
            'jam_mulai' => '13:00',
            'jam_selesai' => '15:00',
        ]);
        
        // ==========================
        // JUMAT
        // ==========================
        // 07:00-08:00: Senam Bersama
        Jadwal::create([
            'kelas_id' => $kelas,
            'kegiatan_id' => 6, // Senam Bersama
            'hari' => 'Jumat',
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:00',
        ]);
        
        // 08:00-10:00: Tahfidz
        Jadwal::create([
            'kelas_id' => $kelas,
            'kegiatan_id' => 7, // Tahfidz
            'hari' => 'Jumat',
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
        ]);
        
        // 10:00-12:00: Bahasa Indonesia
        Jadwal::create([
            'kelas_id' => $kelas,
            'mapel_id' => 34, // B. Indonesia
            'guru_id' => 18, // Liana Safitri, S.Pd
            'hari' => 'Jumat',
            'jam_mulai' => '10:00',
            'jam_selesai' => '12:00',
        ]);
        
        // 12:00-13:00: Istirahat
         Jadwal::create([
            'kelas_id' => $kelas,
            'kegiatan_id' => 8, // Istirahat
            'hari' => 'Jumat',
            'jam_mulai' => '12:00',
            'jam_selesai' => '13:00',
        ]);
        
        // 13:00-15:00: PKK
        Jadwal::create([
            'kelas_id' => $kelas,
            'mapel_id' => 36, // PKK
            'guru_id' => 17, // Arryan Bimantari, M.Si.
            'hari' => 'Jumat',
            'jam_mulai' => '13:00',
            'jam_selesai' => '15:00',
        ]);
    }
}
