<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPoli extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'master_poli';

    // ⚠️ PENTING: Tambahkan ini karena tabel master_poli tidak punya created_at & updated_at
    public $timestamps = false; 

    // Kolom yang bisa diisi (mass assignable)
    protected $fillable = [
        'kode_poli',
        'nama_poli',
        'keterangan',
    ];

    /**
     * Relasi ke tabel lain
     */
    public function jadwal()
    {
        return $this->hasMany(MasterJadwal::class, 'kode_poli', 'kode_poli');
    }
    
    public function dokter()
    {
        return $this->hasMany(MasterDokter::class, 'kode_poli', 'kode_poli');
    }
}