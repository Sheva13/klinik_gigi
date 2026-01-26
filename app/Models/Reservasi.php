<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservasi extends Model
{
    use HasFactory;

    protected $table = 'reservasi';

    // Tidak perlu "public $timestamps = false;" 
    // karena di gambar tabel RESERVASI ada created_at & updated_at. Aman!

    protected $fillable = [
        'no_pemeriksaan',
        'no_antrian',
        'pasien_id',      // Relasi string ke rekam_medis
        'dokter_id',      // Relasi string ke kode_dokter
        'jadwal_id',      // Relasi integer ke id jadwal
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
        'link_pembayaran',
        'snap_token',
        'redirect_url',
        'snap_token_pelunasan',
        // --- ⚠️ AREA BERBAHAYA (HOME CARE) ⚠️ ---
        // Kolom di bawah ini SEMENTARA dimatikan karena BELUM ADA di Database (Gambar 4)
        // Jika dipaksa, akan error "Column not found".
        // Uncomment (hapus //) jika sudah update database.
        
        // 'tipe_layanan', 
        // 'alamat_lengkap', 
        // 'latitude', 
        // 'longitude', 
        // 'biaya_transport', 
    ];

    public function rekamMedis()
    {
        return $this->belongsTo(RekamMedis::class, 'pasien_id', 'rekam_medis');
    }

    // Alias untuk memudahkan panggil $reservasi->pasien
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

    public function tindakanPemeriksaan()
    {
        return $this->hasMany(TindakanPemeriksaan::class, 'reservasi_id', 'id');
    }

    public function biayaTambahan()
    {
        return $this->hasMany(BiayaTambahan::class, 'reservasi_id', 'id');
    }

    public function tracking()
    {
        return $this->hasMany(HomeCareTracking::class, 'id_periksa', 'id');
    }
}