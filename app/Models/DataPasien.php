<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataPasien extends Model
{
    use HasFactory;
    
    protected $table = 'data_pasien';
    
    protected $fillable = [
        'id_jadwal', 
        'rekam_medis_id', // Sudah disesuaikan dengan nama kolom di database (varchar)
        'no_antri',
        'status',
        'pasien_baru',
        'rujukan',
        'id_rujukan',
        'id_calon',
        'tindak_lanjut',
        'followup',
        'no_sjp',
        'biaya_admin',
        'biaya_admin_managecare',
        'keluhan', 
        'latitude_pasien', 
        'longitude_pasien', 
    ];

    public function jadwalHarian()
    {
        return $this->belongsTo(JadwalHarian::class, 'id_jadwal', 'id');
    }

    public function biayaTambahan()
    {
        return $this->hasMany(BiayaTambahan::class, 'id_periksa', 'id');
    }

    public function pasien()
    {
        // Menghubungkan kolom 'rekam_medis' (varchar) di tabel ini 
        // dengan kolom 'rekam_medis' (varchar) di tabel RekamMedis
        return $this->belongsTo(RekamMedis::class, 'rekam_medis', 'rekam_medis');
    }

    public function user()
    {
        // Relasi ke user via nomor rekam medis
        return $this->belongsTo(MpUser::class, 'rekam_medis', 'rekam_medis');
    }

    public function tracking()
    {
        return $this->hasMany(HomeCareTracking::class, 'id_periksa', 'id');
    }

    public function transaksiBayar()
    {
        return $this->hasOne(TransaksiBayar::class, 'id_periksa', 'id');
    }

    public function tindakanPemeriksaan()
    {
        return $this->hasMany(TindakanPemeriksaan::class, 'id_periksa', 'id');
    }
}