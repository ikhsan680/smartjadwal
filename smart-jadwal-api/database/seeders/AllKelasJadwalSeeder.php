<?php

namespace Database\Seeders;

use App\Models\Jadwal;
use App\Models\Kelas;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AllKelasJadwalSeeder extends Seeder
{
    /**
     * Run the database seeds untuk semua kelas dari X sampai XII
     * Dengan jadwal BERBEDA per jurusan
     */
    public function run(): void
    {
        // Ambil semua kelas
        $allKelas = Kelas::all();

        // Untuk setiap kelas, generate jadwal berdasarkan jurusan
        foreach ($allKelas as $kelas) {
            $programKey = $this->detectProgram($kelas->nama);
            $this->generateScheduleByProgram($kelas->id, $programKey);
        }

        $this->command->info('✅ Jadwal berhasil dibuat untuk semua ' . $allKelas->count() . ' kelas dengan program yang berbeda-beda!');
    }

    /**
     * Detect program/jurusan dari nama kelas
     */
    private function detectProgram($kelasNama): string
    {
        if (stripos($kelasNama, 'PPLG') !== false) {
            return 'PPLG';
        } elseif (stripos($kelasNama, 'TJKT') !== false || stripos($kelasNama, 'TKJ') !== false) {
            return 'TJKT';
        } elseif (stripos($kelasNama, 'MPLB') !== false) {
            return 'MPLB';
        } elseif (stripos($kelasNama, 'AKL') !== false || stripos($kelasNama, 'AK') !== false) {
            return 'AKL';
        } elseif (stripos($kelasNama, 'RPL') !== false) {
            return 'RPL';
        }
        return 'PPLG'; // Default ke PPLG
    }

    /**
     * Generate jadwal berdasarkan program
     */
    private function generateScheduleByProgram($kelasId, $program): void
    {
        switch ($program) {
            case 'PPLG':
                $this->scheduleForPPLG($kelasId);
                break;
            case 'TJKT':
                $this->scheduleForTJKT($kelasId);
                break;
            case 'MPLB':
                $this->scheduleForMPLB($kelasId);
                break;
            case 'AKL':
                $this->scheduleForAKL($kelasId);
                break;
            case 'RPL':
                $this->scheduleForRPL($kelasId);
                break;
            default:
                $this->scheduleForPPLG($kelasId);
        }
    }

    /**
     * JADWAL PPLG: Fokus pada Pemrograman & Software Development
     */
    private function scheduleForPPLG($kelasId): void
    {
        $template = [
            'Senin' => [
                ['kegiatan_id' => 1, 'jam_mulai' => '06:30', 'jam_selesai' => '07:30'], // Upacara
                ['mapel_id' => 16, 'jam_mulai' => '07:30', 'jam_selesai' => '08:50'], // Matematika
                ['kegiatan_id' => 8, 'jam_mulai' => '08:50', 'jam_selesai' => '09:35'], // Istirahat
                ['mapel_id' => 1, 'jam_mulai' => '09:35', 'jam_selesai' => '12:15'], // B. Inggris
                ['kegiatan_id' => 8, 'jam_mulai' => '12:15', 'jam_selesai' => '13:20'], // Istirahat
                ['mapel_id' => 30, 'jam_mulai' => '13:20', 'jam_selesai' => '15:20'], // Pemrograman Javascript
            ],
            'Selasa' => [
                ['kegiatan_id' => 2, 'jam_mulai' => '06:30', 'jam_selesai' => '07:30'], // Muhadoroh
                ['mapel_id' => 30, 'jam_mulai' => '07:30', 'jam_selesai' => '08:50'], // Pemrograman Javascript
                ['kegiatan_id' => 8, 'jam_mulai' => '08:50', 'jam_selesai' => '09:35'], // Istirahat
                ['mapel_id' => 41, 'jam_mulai' => '09:35', 'jam_selesai' => '12:15'], // PKn
                ['kegiatan_id' => 8, 'jam_mulai' => '12:15', 'jam_selesai' => '13:20'], // Istirahat
                ['mapel_id' => 33, 'jam_mulai' => '13:20', 'jam_selesai' => '15:20'], // B. Arab
            ],
            'Rabu' => [
                ['kegiatan_id' => 3, 'jam_mulai' => '06:30', 'jam_selesai' => '07:00'], // Pembacaan Almatsurat
                ['kegiatan_id' => 4, 'jam_mulai' => '07:00', 'jam_selesai' => '07:30'], // Mentoring
                ['mapel_id' => 30, 'jam_mulai' => '07:30', 'jam_selesai' => '08:50'], // Pemrograman Javascript
                ['kegiatan_id' => 8, 'jam_mulai' => '08:50', 'jam_selesai' => '09:35'], // Istirahat
                ['mapel_id' => 32, 'jam_mulai' => '09:35', 'jam_selesai' => '12:15'], // Basis Data
                ['kegiatan_id' => 8, 'jam_mulai' => '12:15', 'jam_selesai' => '13:20'], // Istirahat
                ['mapel_id' => 31, 'jam_mulai' => '13:20', 'jam_selesai' => '15:20'], // Pemrograman Web Dasar
            ],
            'Kamis' => [
                ['kegiatan_id' => 5, 'jam_mulai' => '06:30', 'jam_selesai' => '07:30'], // Pembacaan Almatsurat
                ['mapel_id' => 24, 'jam_mulai' => '07:30', 'jam_selesai' => '08:50'], // PAI
                ['kegiatan_id' => 8, 'jam_mulai' => '08:50', 'jam_selesai' => '09:35'], // Istirahat
                ['mapel_id' => 39, 'jam_mulai' => '09:35', 'jam_selesai' => '12:15'], // BK
                ['kegiatan_id' => 8, 'jam_mulai' => '12:15', 'jam_selesai' => '13:20'], // Istirahat
                ['mapel_id' => 30, 'jam_mulai' => '13:20', 'jam_selesai' => '15:20'], // Pemrograman Javascript
            ],
            'Jumat' => [
                ['kegiatan_id' => 6, 'jam_mulai' => '06:30', 'jam_selesai' => '07:15'], // Senam Bersama
                ['kegiatan_id' => 7, 'jam_mulai' => '07:15', 'jam_selesai' => '08:50'], // Tahfidz
                ['kegiatan_id' => 8, 'jam_mulai' => '08:50', 'jam_selesai' => '09:35'], // Istirahat
                ['mapel_id' => 34, 'jam_mulai' => '09:35', 'jam_selesai' => '12:15'], // B. Indonesia
                ['kegiatan_id' => 8, 'jam_mulai' => '12:15', 'jam_selesai' => '13:20'], // Istirahat
                ['mapel_id' => 36, 'jam_mulai' => '13:20', 'jam_selesai' => '15:20'], // PKK
            ],
        ];

        $this->createJadwalFromTemplate($kelasId, $template);
    }

    /**
     * JADWAL TJKT: Fokus pada Jaringan, Hardware, dan Infrastruktur
     */
    private function scheduleForTJKT($kelasId): void
    {
        $template = [
            'Senin' => [
                ['kegiatan_id' => 1, 'jam_mulai' => '06:30', 'jam_selesai' => '07:30'], // Upacara
                ['mapel_id' => 16, 'jam_mulai' => '07:30', 'jam_selesai' => '08:50'], // Matematika
                ['kegiatan_id' => 8, 'jam_mulai' => '08:50', 'jam_selesai' => '09:35'], // Istirahat
                ['mapel_id' => 1, 'jam_mulai' => '09:35', 'jam_selesai' => '12:15'], // B. Inggris
                ['kegiatan_id' => 8, 'jam_mulai' => '12:15', 'jam_selesai' => '13:20'], // Istirahat
                ['mapel_id' => 4, 'jam_mulai' => '13:20', 'jam_selesai' => '15:20'], // Dasar-dasar Jaringan Komputer
            ],
            'Selasa' => [
                ['kegiatan_id' => 2, 'jam_mulai' => '06:30', 'jam_selesai' => '07:30'], // Muhadoroh
                ['mapel_id' => 4, 'jam_mulai' => '07:30', 'jam_selesai' => '08:50'], // Dasar-dasar Jaringan
                ['kegiatan_id' => 8, 'jam_mulai' => '08:50', 'jam_selesai' => '09:35'], // Istirahat
                ['mapel_id' => 41, 'jam_mulai' => '09:35', 'jam_selesai' => '12:15'], // PKn
                ['kegiatan_id' => 8, 'jam_mulai' => '12:15', 'jam_selesai' => '13:20'], // Istirahat
                ['mapel_id' => 33, 'jam_mulai' => '13:20', 'jam_selesai' => '15:20'], // B. Arab
            ],
            'Rabu' => [
                ['kegiatan_id' => 3, 'jam_mulai' => '06:30', 'jam_selesai' => '07:00'], // Pembacaan Almatsurat
                ['kegiatan_id' => 4, 'jam_mulai' => '07:00', 'jam_selesai' => '07:30'], // Mentoring
                ['mapel_id' => 4, 'jam_mulai' => '07:30', 'jam_selesai' => '08:50'], // Dasar-dasar Jaringan
                ['kegiatan_id' => 8, 'jam_mulai' => '08:50', 'jam_selesai' => '09:35'], // Istirahat
                ['mapel_id' => 5, 'jam_mulai' => '09:35', 'jam_selesai' => '12:15'], // Sysadmin
                ['kegiatan_id' => 8, 'jam_mulai' => '12:15', 'jam_selesai' => '13:20'], // Istirahat
                ['mapel_id' => 6, 'jam_mulai' => '13:20', 'jam_selesai' => '15:20'], // Simulasi Jaringan
            ],
            'Kamis' => [
                ['kegiatan_id' => 5, 'jam_mulai' => '06:30', 'jam_selesai' => '07:30'], // Pembacaan Almatsurat
                ['mapel_id' => 24, 'jam_mulai' => '07:30', 'jam_selesai' => '08:50'], // PAI
                ['kegiatan_id' => 8, 'jam_mulai' => '08:50', 'jam_selesai' => '09:35'], // Istirahat
                ['mapel_id' => 39, 'jam_mulai' => '09:35', 'jam_selesai' => '12:15'], // BK
                ['kegiatan_id' => 8, 'jam_mulai' => '12:15', 'jam_selesai' => '13:20'], // Istirahat
                ['mapel_id' => 5, 'jam_mulai' => '13:20', 'jam_selesai' => '15:20'], // Sysadmin
            ],
            'Jumat' => [
                ['kegiatan_id' => 6, 'jam_mulai' => '06:30', 'jam_selesai' => '07:15'], // Senam Bersama
                ['kegiatan_id' => 7, 'jam_mulai' => '07:15', 'jam_selesai' => '08:50'], // Tahfidz
                ['kegiatan_id' => 8, 'jam_mulai' => '08:50', 'jam_selesai' => '09:35'], // Istirahat
                ['mapel_id' => 34, 'jam_mulai' => '09:35', 'jam_selesai' => '12:15'], // B. Indonesia
                ['kegiatan_id' => 8, 'jam_mulai' => '12:15', 'jam_selesai' => '13:20'], // Istirahat
                ['mapel_id' => 36, 'jam_mulai' => '13:20', 'jam_selesai' => '15:20'], // PKK
            ],
        ];

        $this->createJadwalFromTemplate($kelasId, $template);
    }

    /**
     * JADWAL MPLB: Fokus pada Manajemen & Bisnis
     */
    private function scheduleForMPLB($kelasId): void
    {
        $template = [
            'Senin' => [
                ['kegiatan_id' => 1, 'jam_mulai' => '06:30', 'jam_selesai' => '07:30'], // Upacara
                ['mapel_id' => 16, 'jam_mulai' => '07:30', 'jam_selesai' => '08:50'], // Matematika
                ['kegiatan_id' => 8, 'jam_mulai' => '08:50', 'jam_selesai' => '09:35'], // Istirahat
                ['mapel_id' => 1, 'jam_mulai' => '09:35', 'jam_selesai' => '12:15'], // B. Inggris
                ['kegiatan_id' => 8, 'jam_mulai' => '12:15', 'jam_selesai' => '13:20'], // Istirahat
                ['mapel_id' => 18, 'jam_mulai' => '13:20', 'jam_selesai' => '15:20'], // Ekonomi Bisnis
            ],
            'Selasa' => [
                ['kegiatan_id' => 2, 'jam_mulai' => '06:30', 'jam_selesai' => '07:30'], // Muhadoroh
                ['mapel_id' => 18, 'jam_mulai' => '07:30', 'jam_selesai' => '08:50'], // Ekonomi Bisnis
                ['kegiatan_id' => 8, 'jam_mulai' => '08:50', 'jam_selesai' => '09:35'], // Istirahat
                ['mapel_id' => 41, 'jam_mulai' => '09:35', 'jam_selesai' => '12:15'], // PKn
                ['kegiatan_id' => 8, 'jam_mulai' => '12:15', 'jam_selesai' => '13:20'], // Istirahat
                ['mapel_id' => 33, 'jam_mulai' => '13:20', 'jam_selesai' => '15:20'], // B. Arab
            ],
            'Rabu' => [
                ['kegiatan_id' => 3, 'jam_mulai' => '06:30', 'jam_selesai' => '07:00'], // Pembacaan Almatsurat
                ['kegiatan_id' => 4, 'jam_mulai' => '07:00', 'jam_selesai' => '07:30'], // Mentoring
                ['mapel_id' => 18, 'jam_mulai' => '07:30', 'jam_selesai' => '08:50'], // Ekonomi Bisnis
                ['kegiatan_id' => 8, 'jam_mulai' => '08:50', 'jam_selesai' => '09:35'], // Istirahat
                ['mapel_id' => 25, 'jam_mulai' => '09:35', 'jam_selesai' => '12:15'], // Akuntansi Dasar
                ['kegiatan_id' => 8, 'jam_mulai' => '12:15', 'jam_selesai' => '13:20'], // Istirahat
                ['mapel_id' => 20, 'jam_mulai' => '13:20', 'jam_selesai' => '15:20'], // Komunikasi
            ],
            'Kamis' => [
                ['kegiatan_id' => 5, 'jam_mulai' => '06:30', 'jam_selesai' => '07:30'], // Pembacaan Almatsurat
                ['mapel_id' => 24, 'jam_mulai' => '07:30', 'jam_selesai' => '08:50'], // PAI
                ['kegiatan_id' => 8, 'jam_mulai' => '08:50', 'jam_selesai' => '09:35'], // Istirahat
                ['mapel_id' => 39, 'jam_mulai' => '09:35', 'jam_selesai' => '12:15'], // BK
                ['kegiatan_id' => 8, 'jam_mulai' => '12:15', 'jam_selesai' => '13:20'], // Istirahat
                ['mapel_id' => 20, 'jam_mulai' => '13:20', 'jam_selesai' => '15:20'], // Komunikasi
            ],
            'Jumat' => [
                ['kegiatan_id' => 6, 'jam_mulai' => '06:30', 'jam_selesai' => '07:15'], // Senam Bersama
                ['kegiatan_id' => 7, 'jam_mulai' => '07:15', 'jam_selesai' => '08:50'], // Tahfidz
                ['kegiatan_id' => 8, 'jam_mulai' => '08:50', 'jam_selesai' => '09:35'], // Istirahat
                ['mapel_id' => 34, 'jam_mulai' => '09:35', 'jam_selesai' => '12:15'], // B. Indonesia
                ['kegiatan_id' => 8, 'jam_mulai' => '12:15', 'jam_selesai' => '13:20'], // Istirahat
                ['mapel_id' => 36, 'jam_mulai' => '13:20', 'jam_selesai' => '15:20'], // PKK
            ],
        ];

        $this->createJadwalFromTemplate($kelasId, $template);
    }

    /**
     * JADWAL AKL: Fokus pada Akuntansi & Keuangan
     */
    private function scheduleForAKL($kelasId): void
    {
        $template = [
            'Senin' => [
                ['kegiatan_id' => 1, 'jam_mulai' => '06:30', 'jam_selesai' => '07:30'], // Upacara
                ['mapel_id' => 16, 'jam_mulai' => '07:30', 'jam_selesai' => '08:50'], // Matematika
                ['kegiatan_id' => 8, 'jam_mulai' => '08:50', 'jam_selesai' => '09:35'], // Istirahat
                ['mapel_id' => 1, 'jam_mulai' => '09:35', 'jam_selesai' => '12:15'], // B. Inggris
                ['kegiatan_id' => 8, 'jam_mulai' => '12:15', 'jam_selesai' => '13:20'], // Istirahat
                ['mapel_id' => 25, 'jam_mulai' => '13:20', 'jam_selesai' => '15:20'], // Akuntansi Dasar
            ],
            'Selasa' => [
                ['kegiatan_id' => 2, 'jam_mulai' => '06:30', 'jam_selesai' => '07:30'], // Muhadoroh
                ['mapel_id' => 25, 'jam_mulai' => '07:30', 'jam_selesai' => '08:50'], // Akuntansi Dasar
                ['kegiatan_id' => 8, 'jam_mulai' => '08:50', 'jam_selesai' => '09:35'], // Istirahat
                ['mapel_id' => 41, 'jam_mulai' => '09:35', 'jam_selesai' => '12:15'], // PKn
                ['kegiatan_id' => 8, 'jam_mulai' => '12:15', 'jam_selesai' => '13:20'], // Istirahat
                ['mapel_id' => 33, 'jam_mulai' => '13:20', 'jam_selesai' => '15:20'], // B. Arab
            ],
            'Rabu' => [
                ['kegiatan_id' => 3, 'jam_mulai' => '06:30', 'jam_selesai' => '07:00'], // Pembacaan Almatsurat
                ['kegiatan_id' => 4, 'jam_mulai' => '07:00', 'jam_selesai' => '07:30'], // Mentoring
                ['mapel_id' => 25, 'jam_mulai' => '07:30', 'jam_selesai' => '08:50'], // Akuntansi Dasar
                ['kegiatan_id' => 8, 'jam_mulai' => '08:50', 'jam_selesai' => '09:35'], // Istirahat
                ['mapel_id' => 18, 'jam_mulai' => '09:35', 'jam_selesai' => '12:15'], // Ekonomi Bisnis
                ['kegiatan_id' => 8, 'jam_mulai' => '12:15', 'jam_selesai' => '13:20'], // Istirahat
                ['mapel_id' => 26, 'jam_mulai' => '13:20', 'jam_selesai' => '15:20'], // Komputer Akuntansi
            ],
            'Kamis' => [
                ['kegiatan_id' => 5, 'jam_mulai' => '06:30', 'jam_selesai' => '07:30'], // Pembacaan Almatsurat
                ['mapel_id' => 24, 'jam_mulai' => '07:30', 'jam_selesai' => '08:50'], // PAI
                ['kegiatan_id' => 8, 'jam_mulai' => '08:50', 'jam_selesai' => '09:35'], // Istirahat
                ['mapel_id' => 39, 'jam_mulai' => '09:35', 'jam_selesai' => '12:15'], // BK
                ['kegiatan_id' => 8, 'jam_mulai' => '12:15', 'jam_selesai' => '13:20'], // Istirahat
                ['mapel_id' => 26, 'jam_mulai' => '13:20', 'jam_selesai' => '15:20'], // Komputer Akuntansi
            ],
            'Jumat' => [
                ['kegiatan_id' => 6, 'jam_mulai' => '06:30', 'jam_selesai' => '07:15'], // Senam Bersama
                ['kegiatan_id' => 7, 'jam_mulai' => '07:15', 'jam_selesai' => '08:50'], // Tahfidz
                ['kegiatan_id' => 8, 'jam_mulai' => '08:50', 'jam_selesai' => '09:35'], // Istirahat
                ['mapel_id' => 34, 'jam_mulai' => '09:35', 'jam_selesai' => '12:15'], // B. Indonesia
                ['kegiatan_id' => 8, 'jam_mulai' => '12:15', 'jam_selesai' => '13:20'], // Istirahat
                ['mapel_id' => 36, 'jam_mulai' => '13:20', 'jam_selesai' => '15:20'], // PKK
            ],
        ];

        $this->createJadwalFromTemplate($kelasId, $template);
    }

    /**
     * JADWAL RPL (Kelas XII): Fokus pada Rekayasa Perangkat Lunak (Advanced)
     */
    private function scheduleForRPL($kelasId): void
    {
        $template = [
            'Senin' => [
                ['kegiatan_id' => 1, 'jam_mulai' => '06:30', 'jam_selesai' => '07:30'], // Upacara
                ['mapel_id' => 16, 'jam_mulai' => '07:30', 'jam_selesai' => '08:50'], // Matematika
                ['kegiatan_id' => 8, 'jam_mulai' => '08:50', 'jam_selesai' => '09:35'], // Istirahat
                ['mapel_id' => 1, 'jam_mulai' => '09:35', 'jam_selesai' => '12:15'], // B. Inggris
                ['kegiatan_id' => 8, 'jam_mulai' => '12:15', 'jam_selesai' => '13:20'], // Istirahat
                ['mapel_id' => 30, 'jam_mulai' => '13:20', 'jam_selesai' => '15:20'], // Pemrograman Javascript
            ],
            'Selasa' => [
                ['kegiatan_id' => 2, 'jam_mulai' => '06:30', 'jam_selesai' => '07:30'], // Muhadoroh
                ['mapel_id' => 32, 'jam_mulai' => '07:30', 'jam_selesai' => '08:50'], // Basis Data
                ['kegiatan_id' => 8, 'jam_mulai' => '08:50', 'jam_selesai' => '09:35'], // Istirahat
                ['mapel_id' => 41, 'jam_mulai' => '09:35', 'jam_selesai' => '12:15'], // PKn
                ['kegiatan_id' => 8, 'jam_mulai' => '12:15', 'jam_selesai' => '13:20'], // Istirahat
                ['mapel_id' => 33, 'jam_mulai' => '13:20', 'jam_selesai' => '15:20'], // B. Arab
            ],
            'Rabu' => [
                ['kegiatan_id' => 3, 'jam_mulai' => '06:30', 'jam_selesai' => '07:00'], // Pembacaan Almatsurat
                ['kegiatan_id' => 4, 'jam_mulai' => '07:00', 'jam_selesai' => '07:30'], // Mentoring
                ['mapel_id' => 31, 'jam_mulai' => '07:30', 'jam_selesai' => '08:50'], // Pemrograman Web Dasar
                ['kegiatan_id' => 8, 'jam_mulai' => '08:50', 'jam_selesai' => '09:35'], // Istirahat
                ['mapel_id' => 30, 'jam_mulai' => '09:35', 'jam_selesai' => '12:15'], // Pemrograman Javascript
                ['kegiatan_id' => 8, 'jam_mulai' => '12:15', 'jam_selesai' => '13:20'], // Istirahat
                ['mapel_id' => 32, 'jam_mulai' => '13:20', 'jam_selesai' => '15:20'], // Basis Data
            ],
            'Kamis' => [
                ['kegiatan_id' => 5, 'jam_mulai' => '06:30', 'jam_selesai' => '07:30'], // Pembacaan Almatsurat
                ['mapel_id' => 24, 'jam_mulai' => '07:30', 'jam_selesai' => '08:50'], // PAI
                ['kegiatan_id' => 8, 'jam_mulai' => '08:50', 'jam_selesai' => '09:35'], // Istirahat
                ['mapel_id' => 39, 'jam_mulai' => '09:35', 'jam_selesai' => '12:15'], // BK
                ['kegiatan_id' => 8, 'jam_mulai' => '12:15', 'jam_selesai' => '13:20'], // Istirahat
                ['mapel_id' => 30, 'jam_mulai' => '13:20', 'jam_selesai' => '15:20'], // Pemrograman Javascript
            ],
            'Jumat' => [
                ['kegiatan_id' => 6, 'jam_mulai' => '06:30', 'jam_selesai' => '07:15'], // Senam Bersama
                ['kegiatan_id' => 7, 'jam_mulai' => '07:15', 'jam_selesai' => '08:50'], // Tahfidz
                ['kegiatan_id' => 8, 'jam_mulai' => '08:50', 'jam_selesai' => '09:35'], // Istirahat
                ['mapel_id' => 34, 'jam_mulai' => '09:35', 'jam_selesai' => '12:15'], // B. Indonesia
                ['kegiatan_id' => 8, 'jam_mulai' => '12:15', 'jam_selesai' => '13:20'], // Istirahat
                ['mapel_id' => 36, 'jam_mulai' => '13:20', 'jam_selesai' => '15:20'], // PKK
            ],
        ];

        $this->createJadwalFromTemplate($kelasId, $template);
    }

    /**
     * Helper: Create jadwal dari template dengan auto-fetch guru dari mapel relationship
     */
    private function createJadwalFromTemplate($kelasId, $template): void
    {
        foreach ($template as $hari => $schedules) {
            foreach ($schedules as $schedule) {
                $jadwalData = [
                    'kelas_id' => $kelasId,
                    'hari' => $hari,
                    'jam_mulai' => $schedule['jam_mulai'],
                    'jam_selesai' => $schedule['jam_selesai'],
                ];

                if (isset($schedule['kegiatan_id'])) {
                    $jadwalData['kegiatan_id'] = $schedule['kegiatan_id'];
                }
                
                // Jika ada mapel_id
                if (isset($schedule['mapel_id'])) {
                    $mapelId = $schedule['mapel_id'];
                    $jadwalData['mapel_id'] = $mapelId;
                    
                    // Auto-fetch guru dari relationship mapel->guru
                    if (!isset($schedule['guru_id']) || is_null($schedule['guru_id'])) {
                        $mapel = \App\Models\Mapel::find($mapelId);
                        if ($mapel && $mapel->guru && count($mapel->guru) > 0) {
                            // Ambil guru pertama
                            $jadwalData['guru_id'] = $mapel->guru[0]->id;
                        }
                    } else {
                        $jadwalData['guru_id'] = $schedule['guru_id'];
                    }
                }

                Jadwal::create($jadwalData);
            }
        }
    }
}

