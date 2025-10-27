<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class MpUser extends Authenticatable
{
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
    ];

    protected $hidden = [
        'password',
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
