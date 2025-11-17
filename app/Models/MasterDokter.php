<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterDokter extends Model
{
    use HasFactory;
    protected $table = 'master_dokter';
    protected $fillable = [
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
        'kode_poli',
    ];

    public function masterPoli()
    {
        return $this->belongsTo(MasterPoli::class, 'kode_poli', 'kode_poli');
    }

    public function masterJadwal()
    {
        return $this->hasMany(MasterJadwal::class, 'kode_dokter', 'kode_dokter');
    }


    public function reservasi()
    {
        return $this->hasMany(Reservasi::class, 'dokter_id', 'kode_dokter');
    }

    public function spesialis()
    {
        
        return $this->belongsTo(MasterSpesialis::class, 'spesialisasi', 'id');
    }

    

}
