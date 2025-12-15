<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Reservasi;
use App\Models\HomeCareReservasi;

class BiayaTambahan extends Model
{
    use HasFactory;
    protected $table = 'biaya_tambahan';
    protected $fillable = ['id_periksa', 'komponen', 'biaya', 'reservasi_id', 'homecare_reservasi_id', 'qty', 'jumlah_kali'];

    public $timestamps = false;

    // Relasi kembali ke booking/kunjungan
    public function dataPasien()
    {
        return $this->belongsTo(DataPasien::class, 'id_periksa', 'id');
    }

    // Relasi ke reservasi (klinik biasa)
    public function reservasi()
    {
        // Prefer explicit FK column when present
        return $this->belongsTo(Reservasi::class, 'reservasi_id', 'id');
    }

    public function homeCareReservasi()
    {
        return $this->belongsTo(HomeCareReservasi::class, 'homecare_reservasi_id', 'id');
    }

    /**
     * Return whichever parent (reservasi or homecare) exists for this biaya
     */
    public function parentReservation()
    {
        return $this->reservasi ?? $this->homeCareReservasi ?? $this->dataPasien;
    }
}