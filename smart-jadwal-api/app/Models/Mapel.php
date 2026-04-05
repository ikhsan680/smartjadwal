<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Mapel extends Model
{
    use HasFactory;

    protected $table = 'mapel';

    protected $fillable = [
        'nama',
    ];

    /**
     * Get the jadwal records associated with the mapel.
     */
    public function jadwal()
    {
        return $this->hasMany(Jadwal::class, 'mapel_id');
    }

    /**
     * Get the gurus for this mapel.
     */
    public function guru()
    {
        return $this->belongsToMany(Guru::class, 'guru_mapel', 'mapel_id', 'guru_id')
            ->orderByPivot('guru_id');
    }
}
