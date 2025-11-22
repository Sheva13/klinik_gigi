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
        'tipe_layanan', 
        'alamat_lengkap', 
        'latitude', 
        'longitude', 
        'biaya_transport', 
        'metode_pembayaran'
    ];

    public function rekamMedis()
    {
        return $this->belongsTo(RekamMedis::class, 'pasien_id', 'rekam_medis');
    }

    /**
     * Alias relation to maintain compatibility with controllers using 'pasien'
     * Some code (controllers/routes) eager-load or access 'pasien' relation.
     * Provide a pasien() method that points to the same RekamMedis relation.
     */
    public function pasien()
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
