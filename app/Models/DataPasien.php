<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataPasien extends Model
{
    use HasFactory;
    
    // Nama tabel di database
    protected $table = 'data_pasien';
    
    // PK default adalah 'id' (integer), sesuai DBML

    /**
     * Kolom yang bisa diisi (Mass Assignable)
     * Kita gunakan rekam_medis_id (Integer) sesuai Pilihan A
     */
    protected $fillable = [
        'id_jadwal', 
        'rekam_medis_id', // <-- PERUBAHAN DARI DBML (sebelumnya 'rekam_medis')
        'status',
        // tambahkan kolom lain dari Modul 1 jika ada
        'keluhan', 
        'latitude_pasien', // <-- Untuk Opsi 2 (Rekomendasi)
        'longitude_pasien', // <-- Untuk Opsi 2 (Rekomendasi)
    ];

    /**
     * Relasi ke jadwal harian (Jadwal spesifik yg dipilih)
     */
    public function jadwalHarian()
    {
        return $this->belongsTo(JadwalHarian::class, 'id_jadwal', 'id');
    }

    /**
     * Relasi ke biaya tambahan
     * Satu kunjungan bisa punya banyak biaya (DP, Jarak, dll)
     */
    public function biayaTambahan()
    {
        return $this->hasMany(BiayaTambahan::class, 'id_periksa', 'id');
    }

    /**
     * Relasi ke data profil pasien (tabel rekam_medis)
     * Kita hubungkan via rekam_medis_id
     */
    public function pasien()
    {
        // Relasi ini sudah B BENAR berdasarkan Pilihan A
        // data_pasien.rekam_medis_id -> rekam_medis.id
        return $this->belongsTo(RekamMedis::class, 'rekam_medis_id', 'id');
    }

    /**
     * Relasi ke data login user (tabel users)
     * Ini relasi "shortcut" yang berguna
     */
    public function user()
    {
        // data_pasien.rekam_medis_id -> users.rekam_medis_id
        // Asumsi: rekam_medis_id di tabel users adalah FK ke rekam_medis.id
        // Model MpUser Anda sudah benar (rekam_medis_id)
        return $this->belongsTo(MpUser::class, 'rekam_medis_id', 'rekam_medis_id');
    }

    /**
     * Relasi ke tabel tracking (Satu booking punya banyak history tracking)
     */
    public function tracking()
    {
        return $this->hasMany(HomeCareTracking::class, 'id_periksa', 'id');
    }

    /**
     * Relasi ke transaksi bayar (Satu kunjungan -> Satu nota)
     */
    public function transaksiBayar()
    {
        return $this->hasOne(TransaksiBayar::class, 'id_periksa', 'id');
    }

    /**
     * Relasi ke tindakan yang dilakukan
     */
    public function tindakanPemeriksaan()
    {
        return $this->hasMany(TindakanPemeriksaan::class, 'id_periksa', 'id');
    }
}