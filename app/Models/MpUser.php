<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\RekamMedis;

class MpUser extends Authenticatable
{
    use HasApiTokens; 

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
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
        'current_token',  
    ];

    protected $hidden = [
        'password',
        'remember_token', 
        'current_token',  
    ];

    public function getAuthPassword()
    {
        return $this->password;
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'tanggal_lahir' => 'date',
        ];
    }
    
    public function rekamMedis()
    {
        return $this->belongsTo(RekamMedis::class, 'rekam_medis_id');
    }
}
