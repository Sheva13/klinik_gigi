<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens; 

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
    
    public function rekamMedis()
    {
        return $this->belongsTo(RekamMedis::class, 'rekam_medis_id');
    }
}
