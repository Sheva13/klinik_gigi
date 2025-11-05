<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPoli extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'master_poli';

    // Kolom yang bisa diisi (mass assignable)
    protected $fillable = [
        'kode_poli',
        'nama_poli',
        'keterangan',
    ];

    /**
     * Relasi ke tabel lain
     * Misalnya: satu poli bisa memiliki banyak jadwal dokter
     */
    public function jadwal()
    {
        return $this->hasMany(MasterJadwal::class, 'kode_poli', 'kode_poli');
    }
}