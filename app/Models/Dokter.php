<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dokter extends Model
{
    use HasFactory;
    
    protected $table = 'dokter';
    protected $primaryKey = 'dokter_id';

    // Pastikan fillable mencakup field yang kita ambil
    protected $fillable = [
        'nama_dokter',
        'spesialisasi',
        'no_sip',
        'deskripsi',
        'foto_profil',
    ];

    // Jika Anda ingin hanya mengambil 3 field saja, Anda bisa menggunakan $visible atau di Controller
    // Tapi kita akan membatasi di Controller saja
}