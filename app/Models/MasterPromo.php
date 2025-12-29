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
        'target_transaksi',
    ];
    public $timestamps = true;

    protected $appends = ['gambar_banner_url'];

    public function getGambarBannerUrlAttribute()
    {
        if ($this->gambar_banner) {
            // Menggunakan API Route khusus agar CORS Headers otomatis ditambahkan (fix untuk php artisan serve)
            return url('api/images/' . $this->gambar_banner);
        }
        return null;
    }
}