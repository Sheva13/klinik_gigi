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
        'kode_dokter',
        'kode_poli',
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
     * Relasi ke tabel reservasi
     * Satu dokter bisa memiliki banyak reservasi
     */
    public function reservasi()
    {
        return $this->hasMany(Reservasi::class, 'dokter_id', 'kode_dokter');
    }
}
