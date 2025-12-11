<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalHarian extends Model
{
    use HasFactory;
    
    protected $table = 'jadwal_harian';
    
    // ⚠️ PENTING: Tambahkan ini karena di gambar tabel tidak ada created_at & updated_at
    public $timestamps = false; 

    protected $fillable = [
        'kode_jadwal', 
        'tanggal', 
        'validasi'
    ];

    /**
     * Relasi ke Master Jadwal
     * Note: Di database, 'kode_jadwal' tipenya VARCHAR(10)
     * sedangkan 'id' di master_jadwal tipenya INT.
     * Laravel cukup pintar untuk menghandle ini, tapi pastikan isinya memang ID ya.
     */
    public function masterJadwal()
    {
        return $this->belongsTo(MasterJadwal::class, 'kode_jadwal', 'id'); 
    }
}