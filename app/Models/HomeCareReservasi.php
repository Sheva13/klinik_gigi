<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeCareReservasi extends Model
{
    use HasFactory;

    protected $table = 'homecare_reservasi';

    protected $fillable = [
        'no_pemeriksaan',
        'no_antrian',
        'pasien_id',
        'dokter_id',
        'jadwal_id',
        'tanggal_pesan',
        'waktu_pesan',
        'jam_mulai',
        'jam_selesai',
        'keluhan',
        'biaya_reservasi',
        'biaya_transport',
        'pembayaran_total',
        'metode_pembayaran',
        'status',
        'status_reservasi',
        'status_pembayaran',
        'jenis_pasien',
        'alamat_lengkap',
        'latitude',
        'longitude',
    ];

    public function rekamMedis()
    {
        return $this->belongsTo(RekamMedis::class, 'pasien_id', 'rekam_medis');
    }

    public function pasien()
    {
        return $this->belongsTo(RekamMedis::class, 'pasien_id', 'rekam_medis');
    }

    public function dokter()
    {
        return $this->belongsTo(MasterDokter::class, 'dokter_id', 'kode_dokter');
    }

    public function jadwalHarian()
    {
        return $this->belongsTo(JadwalHarian::class, 'jadwal_id', 'id');
    }

    public function tindakanPemeriksaan()
    {
        // After migration this will use 'homecare_reservasi_id'
        return $this->hasMany(TindakanPemeriksaan::class, 'homecare_reservasi_id', 'id');
    }

    public function biayaTambahan()
    {
        return $this->hasMany(BiayaTambahan::class, 'homecare_reservasi_id', 'id');
    }

    public function tracking()
    {
        return $this->hasMany(HomeCareTracking::class, 'id_periksa', 'id');
    }
}
