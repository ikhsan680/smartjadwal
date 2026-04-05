<?php

namespace Database\Seeders;

use App\Models\Mapel;
use App\Models\Guru;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MapelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat semua mapel
        $mapels = [
            'B. Inggris',
            'Pemrograman Perangkat Bergerak',
            'Sejarah Indonesia',
            'Dasar-dasar Jaringan Komputer',
            'Sysadmin',
            'Simulasi Jaringan & Administrasi Jaringan (CPT+Mikrotik)',
            'Sertifikasi MTCNA dan Teknologi Jaringan',
            'Dasar-dasar MPLB',
            'Ms. Office',
            'Kearsipan',
            'Kas Kecil',
            'Humas',
            'Dasar-dasar Komputer',
            'Cloud Computing berbasis AWS',
            'CISCO CCNP',
            'Matematika',
            'Seni',
            'Ekonomi Bisnis',
            'Administrasi Umum',
            'Komunikasi',
            'Sarpras',
            'SDM',
            'Kepegawaian',
            'PAI',
            'Dasar-dasar AKL',
            'Akuntansi perusahaan jasa, dagang dan manufaktur',
            'Komputer Akuntansi',
            'Perpajakan',
            'Akuntansi Keuangan',
            'Pemrograman Javascript',
            'Pemrograman Web dasar',
            'Basis Data',
            'B. Arab',
            'B. Indonesia',
            'Kreatifitas, Inovasi, dan Kewirausahaan',
            'PKK',
            'Informatika',
            'Pemrograman WEB',
            'BK',
            'Penjaskes',
            'PKn',
            'B. Jepang',
            'Produktif tambahan untuk X PPLG'
        ];

        foreach ($mapels as $nama) {
            Mapel::create(['nama' => $nama]);
        }

        // Hubungkan guru dengan mapel sesuai data
        $guruMapelData = [
            'Isti Qomah, S.Pd.' => [
                'B. Inggris'
            ],
            'Asep Cahya Nugraha, S.T.' => [
                'Pemrograman Perangkat Bergerak'
            ],
            'Reni Iriani, S.Sos.' => [
                'Sejarah Indonesia'
            ],
            'Zainal Musthofa, A.Md.' => [
                'Dasar-dasar Jaringan Komputer',
                'Sysadmin',
                'Simulasi Jaringan & Administrasi Jaringan (CPT+Mikrotik)',
                'Sertifikasi MTCNA dan Teknologi Jaringan'
            ],
            'Heri Triono Saputro, S.Pd.' => [
                'Dasar-dasar MPLB',
                'Ms. Office',
                'Kearsipan',
                'Kas Kecil',
                'Humas'
            ],
            'IDN' => [
                'Dasar-dasar Komputer',
                'Cloud Computing berbasis AWS',
                'CISCO CCNP'
            ],
            'Cahyono, S.Pd' => [
                'Matematika',
                'Seni'
            ],
            'Eliyusnayeti, S.Pd.' => [
                'Ekonomi Bisnis',
                'Administrasi Umum',
                'Komunikasi',
                'Sarpras',
                'SDM',
                'Kepegawaian'
            ],
            'Siti Nurjanah, S.Pd.' => [
                'PAI'
            ],
            'Ai Nurhayati, S.Pd.I' => [
                'PAI'
            ],
            'Yulianti, S.E' => [
                'Dasar-dasar AKL',
                'Ms. Office',
                'Akuntansi perusahaan jasa, dagang dan manufaktur',
                'Komputer Akuntansi',
                'Perpajakan',
                'Akuntansi Keuangan'
            ],
            'Ade Adjie Ferijal, S.T.' => [
                'Pemrograman Javascript',
                'Pemrograman Web dasar',
                'Basis Data'
            ],
            'Linda Marantika W, S.Pd.' => [
                'Matematika'
            ],
            'Ayi Solihudin, S.Pd.' => [
                'B. Arab'
            ],
            'Aah Robiah, S.Pd' => [
                'B. Inggris'
            ],
            'Arika Dhurianita, S.Pd' => [
                'B. Indonesia',
                'Kreatifitas, Inovasi, dan Kewirausahaan'
            ],
            'Arryan Bimantari, M.Si.' => [
                'PKK'
            ],
            'Liana Safitri, S.Pd' => [
                'B. Indonesia',
                'PKK'
            ],
            'Argi Zaelani, S.Kom' => [
                'Informatika',
                'Pemrograman WEB'
            ],
            'Dede Kusmiati, S.Pd.' => [
                'BK'
            ],
            'Rizki Purnomo' => [
                'Penjaskes'
            ],
            'Alisa Nur Fazria, S.Pd' => [
                'PKn'
            ],
            'Abdullah Azzam' => [
                'BK'
            ],
            'Guru Tamu ANABUKI' => [
                'B. Jepang'
            ],
            'Dede Suhardi' => [
                'Ms. Office'
            ],
            'Nazmi (dari Poltek IDN)' => [
                'Produktif tambahan untuk X PPLG'
            ]
        ];

        foreach ($guruMapelData as $guruNama => $mapelNames) {
            $guru = Guru::where('nama', $guruNama)->first();
            if ($guru) {
                foreach ($mapelNames as $mapelNama) {
                    $mapel = Mapel::where('nama', $mapelNama)->first();
                    if ($mapel) {
                        $guru->mapel()->attach($mapel->id);
                    }
                }
            }
        }
    }
}
