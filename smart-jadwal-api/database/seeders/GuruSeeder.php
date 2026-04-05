<?php

namespace Database\Seeders;

use App\Models\Guru;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GuruSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Guru::create(['nama' => 'Isti Qomah, S.Pd.']);
        Guru::create(['nama' => 'Asep Cahya Nugraha, S.T.']);
        Guru::create(['nama' => 'Reni Iriani, S.Sos.']);
        Guru::create(['nama' => 'Zainal Musthofa, A.Md.']);
        Guru::create(['nama' => 'Heri Triono Saputro, S.Pd.']);
        Guru::create(['nama' => 'IDN']);
        Guru::create(['nama' => 'Cahyono, S.Pd']);
        Guru::create(['nama' => 'Eliyusnayeti, S.Pd.']);
        Guru::create(['nama' => 'Siti Nurjanah, S.Pd.']);
        Guru::create(['nama' => 'Ai Nurhayati, S.Pd.I']);
        Guru::create(['nama' => 'Yulianti, S.E']);
        Guru::create(['nama' => 'Ade Adjie Ferijal, S.T.']);
        Guru::create(['nama' => 'Linda Marantika W, S.Pd.']);
        Guru::create(['nama' => 'Ayi Solihudin, S.Pd.']);
        Guru::create(['nama' => 'Aah Robiah, S.Pd']);
        Guru::create(['nama' => 'Arika Dhurianita, S.Pd']);
        Guru::create(['nama' => 'Arryan Bimantari, M.Si.']);
        Guru::create(['nama' => 'Liana Safitri, S.Pd']);
        Guru::create(['nama' => 'Argi Zaelani, S.Kom']);
        Guru::create(['nama' => 'Dede Kusmiati, S.Pd.']);
        Guru::create(['nama' => 'Rizki Purnomo']);
        Guru::create(['nama' => 'Alisa Nur Fazria, S.Pd']);
        Guru::create(['nama' => 'Abdullah Azzam']);
        Guru::create(['nama' => 'Guru Tamu ANABUKI']);
        Guru::create(['nama' => 'Dede Suhardi']);
        Guru::create(['nama' => 'Nazmi (dari Poltek IDN)']);
    }
}
