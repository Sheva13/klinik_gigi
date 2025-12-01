<?php

namespace App\Models;

use Illuminate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Reservasi;
use App\Models\HomeCareReservasi;

class TindakanPemeriksaan extends Model
{
    use HasFactory;
    protected $table = 'tindakan_pemeriksaan';
    protected $fillable = ['id_periksa', 'tindakan', 'reservasi_id', 'homecare_reservasi_id'];

    /**
     * Relasi ke data kunjungan/booking
     */
    public function dataPasien()
    {
        return $this->belongsTo(DataPasien::class, 'id_periksa', 'id');
    }

    // Relation back to clinic reservation
    public function reservasi()
    {
        return $this->belongsTo(Reservasi::class, 'reservasi_id', 'id');
    }

    // Relation back to homecare reservation
    public function homeCareReservasi()
    {
        return $this->belongsTo(HomeCareReservasi::class, 'homecare_reservasi_id', 'id');
    }

    public function parentReservation()
    {
        return $this->reservasi ?? $this->homeCareReservasi ?? $this->dataPasien;
    }

    /**
     * Relasi ke master data tindakan (untuk info nama & harga)
     */
    public function masterTindakan()
    {
        return $this->belongsTo(MasterTindakan::class, 'tindakan', 'id');
    }
}