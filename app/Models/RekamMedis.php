<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekamMedis extends Model
{
    use HasFactory;

    // Nama tabel yang digunakan
    protected $table = 'rekam_medis';

    // disesuaikan dari db
    protected $fillable = [
        'rekam_medis',
        'nama',
        'tempat_lahir',
        'tanggal_lahir',
        'no_identitas',
        'tipe_identitas',
        'status_nikah',
        'pekerjaan',
        'alamat',
        'hp',
        'golongan_darah',
        'file_foto',
        'nama_wali',
        'hubungan_wali',
        'hp_wali',
        'jenis_kelamin',
        'jenis_pasien',
        'no_peserta',
        'nama_asuransi',
    ];

    // relasi ke tabel mp_users
    public function user()
    {
        return $this->hasOne(MpUser::class, 'rekam_medis_id');
    }

    
    public function reservasi()
    {
        return $this->hasMany(Reservasi::class, 'pasien_id', 'rekam_medis');
    }

}

