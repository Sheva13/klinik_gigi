<?php

namespace App\Models;

use Illuminate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeCareTracking extends Model
{
    use HasFactory;
    protected $table = 'home_care_tracking';
    public $timestamps = false; 
    
    protected $fillable = [
        'id_periksa', 
        'status_tracking', 
        'keterangan', 
        'waktu'
    ];

    // Relasi kembali ke data kunjungan/booking
    public function dataPasien()
    {
        return $this->belongsTo(DataPasien::class, 'id_periksa', 'id');
    }

    public function reservasi()
    {
        return $this->belongsTo(Reservasi::class, 'id_periksa', 'id');
    }
}