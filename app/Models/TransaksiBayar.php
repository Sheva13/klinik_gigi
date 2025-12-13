<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiBayar extends Model
{
    use HasFactory;
    protected $table = 'transaksi_bayar';
    
    // 🔥 PERBAIKAN LIXA: Menambahkan semua kolom yang dipakai di Controller
    protected $fillable = [
        'id_periksa', 
        'total_tindakan', 
        'total_obat', 
        'total_penunjang',  // ✅ Tambahan
        'total_tambahan', 
        'total_bayar', 
        'diskon',
        'ambil_obat',       // ✅ Tambahan (Penting!)
        'waktu',            // ✅ Tambahan
        'biaya_admin',      // ✅ Tambahan
        'pasien_baru'       // ✅ Tambahan
    ];

    public function dataPasien()
    {
        return $this->belongsTo(DataPasien::class, 'id_periksa', 'id');
    }
}