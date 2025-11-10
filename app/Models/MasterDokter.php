<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterDokter extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'master_dokter';

    // Kolom yang bisa diisi
    protected $fillable = [
        'id',
        'kode_dokter',
        'nama',
        'gelar',
        'spesialisasi',
        'alamat',
        'hp',
        'tipe',
        'dokter_str',
        'dokter_str_mulai',
        'dokter_str_expire',
        'dokter_sip',
        'dokter_sip_berlaku',
        'dokter_sip_expired',
        'inisial',
    ];

    /**
     * Relasi ke tabel master_jadwal
     * Satu dokter bisa memiliki banyak jadwal praktik
     */
    public function jadwal()
    {
        return $this->hasMany(MasterJadwal::class, 'kode_dokter', 'kode_dokter');
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
