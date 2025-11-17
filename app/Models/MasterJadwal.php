<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterJadwal extends Model
{
    use HasFactory;

    protected $table = 'master_jadwal';

    protected $fillable = [
        'kode_dokter',
        'kode_poli',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'keterangan',
        'quota',
    ];

    public function dokter()
    {
        return $this->belongsTo(MasterDokter::class, 'kode_dokter', 'kode_dokter');
    }

    public function poli()
    {
        return $this->belongsTo(MasterPoli::class, 'kode_poli', 'kode_poli');
    }

    public function reservasi()
    {
        return $this->hasMany(Reservasi::class, 'jadwal_id', 'id');
    }
}
