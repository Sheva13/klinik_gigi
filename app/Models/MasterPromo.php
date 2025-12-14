<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPromo extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database.
     */
    protected $table = 'master_promo';

    /**
     * Kolom yang bisa diisi (mass assignable).
     */
    protected $fillable = [
        'judul_promo',
        'deskripsi',
        'gambar_banner',
        'tanggal_mulai',
        'tanggal_selesai',
        'tipe',
        'harga_poin',
        'nilai_potongan',
        'limit_per_user',
    ];

    public $timestamps = true;
}