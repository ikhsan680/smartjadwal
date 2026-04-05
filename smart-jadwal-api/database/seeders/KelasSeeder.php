<?php

namespace Database\Seeders;

use App\Models\Kelas;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kelas X
        Kelas::create([
            'nama' => 'X PPLG 1',
            'jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
            'wali_kelas' => 'Ade Adje Ferijal, S.T.',
        ]);

        Kelas::create([
            'nama' => 'X PPLG 2',
            'jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
            'wali_kelas' => 'Reni Iriani, S.Sos.',
        ]);

        Kelas::create([
            'nama' => 'X TJKT',
            'jurusan' => 'Teknik Jaringan Komputer dan Telekomunikasi',
            'wali_kelas' => 'Arika Dhurianita, S.Pd',
        ]);

        Kelas::create([
            'nama' => 'X MPLB',
            'jurusan' => 'Manajemen Perkantoran dan Layanan Bisnis',
            'wali_kelas' => 'Yulianti, S.E',
        ]);

        Kelas::create([
            'nama' => 'X AKL',
            'jurusan' => 'Akuntansi Keuangan Dan Lembaga',
            'wali_kelas' => 'Yulianti, S.E',
        ]);

        // Kelas XI
        Kelas::create([
            'nama' => 'XI PPLG 1',
            'jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
            'wali_kelas' => 'Ayi Solihudin, S.Pd.',
        ]);

        Kelas::create([
            'nama' => 'XI PPLG 2',
            'jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
            'wali_kelas' => 'Dede Kusmiati, S.Pd.',
        ]);

        Kelas::create([
            'nama' => 'XI TKJ',
            'jurusan' => 'Teknik Komputer dan Jaringan',
            'wali_kelas' => 'Rizki Purnomo',
        ]);

        Kelas::create([
            'nama' => 'XI MPLB',
            'jurusan' => 'Manajemen Perkantoran dan Layanan Bisnis',
            'wali_kelas' => 'Ai Nurhayati, S.Pd.',
        ]);

        Kelas::create([
            'nama' => 'XI AKL',
            'jurusan' => 'Akuntansi Keuangan Dan Lembaga',
            'wali_kelas' => 'Ai Nurhayati, S.Pd',
        ]);

        // Kelas XII
        Kelas::create([
            'nama' => 'XII RPL 1',
            'jurusan' => 'Rekayasa Perangkat Lunak',
            'wali_kelas' => 'Liana Safitri, S.Pd',
        ]);

        Kelas::create([
            'nama' => 'XII RPL 2',
            'jurusan' => 'Rekayasa Perangkat Lunak',
            'wali_kelas' => 'Alisa Nur Fazria, S.Pd',
        ]);

        Kelas::create([
            'nama' => 'XII TKJ',
            'jurusan' => 'Teknik Komputer dan Jaringan',
            'wali_kelas' => 'Cahyono, S.Pd',
        ]);

        Kelas::create([
            'nama' => 'XII MPLB',
            'jurusan' => 'Manajemen Perkantoran dan Layanan Bisnis',
            'wali_kelas' => 'Arryan Bimantari, M.Si.',
        ]);

        Kelas::create([
            'nama' => 'XII AK',
            'jurusan' => 'Akuntansi Keuangan',
            'wali_kelas' => 'Arryan Bimantari, M.Si.',
        ]);
    }
}
