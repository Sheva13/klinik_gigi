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
     * 🔹 Relasi ke tabel RekamMedis (pasien)
     * Setiap reservasi dimiliki oleh satu pasien
     */
    public function pasien()
    {
        // 👉 ubah ke kolom yang benar sesuai struktur tabel kamu
        // kalau kolom di tabel rekam_medis adalah `rekam_medis`, biarkan begini:
        return $this->belongsTo(RekamMedis::class, 'pasien_id', 'rekam_medis');

        // kalau ternyata kolomnya `id`, ubah jadi:
        // return $this->belongsTo(RekamMedis::class, 'pasien_id', 'id');
    }

    /**
     * 🔹 Relasi ke tabel MasterDokter
     * Satu reservasi dilakukan dengan satu dokter
     */
    public function dokter()
    {
        return $this->belongsTo(MasterJadwal::class, 'dokter_id', 'kode_dokter');
    }

    /**
     * 🔹 Relasi ke tabel MasterJadwal
     * Satu reservasi mengambil jadwal tertentu
     */
    public function jadwal()
    {
        return $this->belongsTo(MasterDokter::class, 'jadwal_id', 'id');
    }
}
