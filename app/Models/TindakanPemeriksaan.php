<?php

namespace App\Models;

use Illuminate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TindakanPemeriksaan extends Model
{
    use HasFactory;
    protected $table = 'tindakan_pemeriksaan';
    protected $fillable = ['id_periksa', 'tindakan'];

    /**
     * Relasi ke data kunjungan/booking
     */
    public function dataPasien()
    {
        return $this->belongsTo(DataPasien::class, 'id_periksa', 'id');
    }

    /**
     * Relasi ke master data tindakan (untuk info nama & harga)
     */
    public function masterTindakan()
    {
        return $this->belongsTo(MasterTindakan::class, 'tindakan', 'id');
    }
}