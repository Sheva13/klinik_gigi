<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterBiayaLayanan extends Model
{
    use HasFactory;

    protected $table = 'master_biaya_layanan';

    protected $fillable = [
        'tipe_layanan',
        'jenis_pasien',
        'biaya_reservasi',
    ];

    protected $casts = [
        'biaya_reservasi' => 'decimal:2',
    ];

    /**
     * Mengambil biaya berdasarkan tipe layanan dan jenis pasien
     */
    public static function getBiayaByLayananAndPasien($tipeLayanan, $jenisPasien)
    {
        return static::where('tipe_layanan', $tipeLayanan)
                    ->where('jenis_pasien', $jenisPasien)
                    ->first();
    }
}