<?php

namespace App\Models;

use Illuminate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiBayar extends Model
{
    use HasFactory;
    protected $table = 'transaksi_bayar';
    protected $fillable = [
        'id_periksa', 
        'total_tindakan', 
        'total_obat', 
        'total_tambahan', 
        'total_bayar', 
        'diskon'
    ];

    /**
     * Relasi ke data kunjungan/booking (One-to-One)
     */
    public function dataPasien()
    {
        return $this->belongsTo(DataPasien::class, 'id_periksa', 'id');
    }
}