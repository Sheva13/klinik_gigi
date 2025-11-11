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
    ];

    /**
     * Kita nonaktifkan timestamps (created_at, updated_at) 
     * karena tidak ada di tabel SQL Anda.
     */
    public $timestamps = false;
}