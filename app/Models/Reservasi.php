<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RekamMedis;
use App\Models\MasterDokter;
use App\Models\MasterJadwal;

class Reservasi extends Model
{
    use HasFactory;

    protected $table = 'reservasi';

    protected $fillable = [
        'no_pemeriksaan',
        'pasien_id',
        'dokter_id',
        'jadwal_id',
        'tanggal_pesan',
        'waktu_pesan',
        'jam_mulai',
        'jam_selesai',
        'keluhan',
        'biaya_reservasi',
        'status',
        'status_reservasi',
        'metode_pembayaran',
        'status_pembayaran',
        'bank_transaksi_id',
        'pembayaran_total',
        'jenis_pasien',
    ];

    public function rekamMedis()
    {
        return $this->belongsTo(RekamMedis::class, 'pasien_id', 'rekam_medis');
    }

    public function dokter()
    {
        return $this->belongsTo(MasterDokter::class, 'dokter_id', 'kode_dokter');
    }


    public function jadwal()
    {
        return $this->belongsTo(MasterJadwal::class, 'jadwal_id', 'id');
    }

}
