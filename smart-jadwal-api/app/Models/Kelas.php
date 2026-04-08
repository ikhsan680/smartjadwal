<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'nama',
        'jurusan',
        'wali_kelas',
        'wali_guru_id',
    ];

    /**
     * Get the jadwal records associated with the kelas.
     */
    public function jadwal()
    {
        return $this->hasMany(Jadwal::class, 'kelas_id');
    }

    /**
     * Get the guru assigned as homeroom teacher.
     */
    public function waliGuru()
    {
        return $this->belongsTo(Guru::class, 'wali_guru_id');
    }
}
