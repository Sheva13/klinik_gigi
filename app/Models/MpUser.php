<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\RekamMedis;

class MpUser extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'user_id';

    // ✅ PERBAIKAN 1: Set 'incrementing' ke false
    // Ini memberi tahu Laravel bahwa 'user_id' tidak auto-increment.
    public $incrementing = false;

    // ✅ PERBAIKAN 2: Set 'keyType' ke string
    // Ini memberi tahu Laravel bahwa 'user_id' adalah string (seperti 'PSN...').
    protected $keyType = 'string';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'nama_pengguna',
        'nik',
        'rekam_medis_id',
        'tanggal_lahir',
        'jenis_kelamin',
        'no_hp',
        'email',
        'password',
        'file_foto',
        'current_token',
    ];

    protected $hidden = [
        'password', 
        'remember_token',
        'current_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'tanggal_lahir' => 'date',
        ];
    }

    // Relasi ke rekam medis
    public function rekamMedis()
    {
        return $this->belongsTo(RekamMedis::class, 'rekam_medis_id', 'id');
    }

    public function reservasi()
    {
        return $this->hasManyThrough(
            Reservasi::class,
            RekamMedis::class,
            'id',          // Foreign key di RekamMedis (ke MpUser)
            'pasien_id',   // Foreign key di Reservasi (ke RekamMedis)
            'rekam_medis_id', // Lokal key di MpUser
            'rekam_medis'  // Lokal key di RekamMedis
        );
    }
}