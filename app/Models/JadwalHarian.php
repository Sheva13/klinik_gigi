<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalHarian extends Model
{
    use HasFactory;
    protected $table = 'jadwal_harian';
    protected $fillable = ['master_jadwal_id', 'tanggal'];

    // Relasi ke master jadwal (template-nya)
    public function masterJadwal()
    {
        return $this->belongsTo(MasterJadwal::class, 'master_jadwal_id', 'id');
    }
}