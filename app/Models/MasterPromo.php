<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPromo extends Model
{
    use HasFactory;

    protected $table = 'master_promo';

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
        // Tambahan baru
        'harga_poin',
        'nilai_potongan',
        'limit_per_user'
    ];
    public $timestamps = true;
}