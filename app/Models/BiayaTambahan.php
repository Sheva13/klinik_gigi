<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiayaTambahan extends Model
{
    use HasFactory;
    protected $table = 'biaya_tambahan';
    protected $fillable = ['id_periksa', 'komponen', 'biaya'];

    // Relasi kembali ke booking/kunjungan
    public function dataPasien()
    {
        return $this->belongsTo(DataPasien::class, 'id_periksa', 'id');
    }
}