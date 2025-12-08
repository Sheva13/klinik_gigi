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
        
        // 🔥 PERBAIKAN LIXA:
        // Controller kirim 'rekam_medis', jadi di sini harus 'rekam_medis' juga.
        // Kalau di DB kolomnya 'rekam_medis', pakai ini:
        'rekam_medis', 
        
        // Kalau ternyata di DB kolomnya memang 'rekam_medis_id',
        // biarkan yang bawah ini aktif, TAPI di Controller baris 266 harus diganti jadi 'rekam_medis_id' => ...
        // 'rekam_medis_id', 

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

    // ... (method relasi di bawahnya aman, tidak perlu diubah) ...
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
        return $this->belongsTo(RekamMedis::class, 'rekam_medis', 'rekam_medis');
    }

    public function user()
    {
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