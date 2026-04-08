<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Guru extends Model
{
    use HasFactory;

    protected $table = 'guru';

    protected $fillable = [
        'nama',
    ];

    /**
     * Get the mapels for this guru.
     */
    public function mapel()
    {
        return $this->belongsToMany(Mapel::class, 'guru_mapel', 'guru_id', 'mapel_id')
            ->orderByPivot('mapel_id');
    }

    /**
     * Get kelas where this guru is assigned as homeroom teacher.
     */
    public function kelasWali()
    {
        return $this->hasMany(Kelas::class, 'wali_guru_id');
    }
}
