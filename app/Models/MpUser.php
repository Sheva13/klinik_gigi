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
    public $incrementing = true;      // ubah ke true jika user_id berupa angka (INT)
    protected $keyType = 'int';       // ubah ke 'string' kalau user_id misalnya 'USR001'
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
}
