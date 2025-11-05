<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservasi extends Model
{
    use HasFactory;

    // Nama tabel
    protected $table = 'reservasi';

    // Kolom yang bisa diisi
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

    /**
     * Relasi ke tabel RekamMedis (pasien)
     * Satu reservasi dimiliki oleh satu pasien
     */
    public function pasien()
    {
        return $this->belongsTo(RekamMedis::class, 'pasien_id', 'rekam_medis');
    }

    /**
     * Relasi ke tabel MasterDokter
     * Satu reservasi dilakukan dengan satu dokter
     */
    public function dokter()
    {
        return $this->belongsTo(MasterDokter::class, 'dokter_id', 'kode_dokter');
    }

    /**
     * Relasi ke tabel MasterJadwal
     * Satu reservasi mengambil jadwal tertentu
     */
    public function jadwal()
    {
        return $this->belongsTo(MasterJadwal::class, 'jadwal_id', 'id');
    }
}
