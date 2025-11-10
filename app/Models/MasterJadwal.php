<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterJadwal extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'master_jadwal';

    // Kolom yang bisa diisi
    protected $fillable = [
        'id',
        'kode_dokter',
        'kode_poli',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'keterangan',
        'quota',
    ];

    /**
     * Relasi ke tabel MasterDokter
     * Setiap jadwal dimiliki oleh satu dokter
     */
    public function dokter()
    {
        return $this->belongsTo(MasterDokter::class, 'kode_dokter', 'kode_dokter');
    }

    /**
     * Relasi ke tabel MasterPoli
     * Setiap jadwal termasuk dalam satu poli
     */
    public function poli()
    {
        return $this->belongsTo(MasterPoli::class, 'kode_poli', 'kode_poli');
    }

    /**
     * Relasi ke tabel Reservasi
     * Satu jadwal bisa memiliki banyak reservasi
     */
    public function reservasi()
    {
        return $this->hasMany(Reservasi::class, 'jadwal_id', 'id');
    }
}
