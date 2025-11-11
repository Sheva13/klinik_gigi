<?php

namespace App\Models;

use Illuminate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterSpesialis extends Model
{
    use HasFactory;
    protected $table = 'master_spesialis';
    protected $fillable = ['nama'];

    /**
     * Satu spesialis bisa dimiliki banyak dokter
     */
    public function dokter()
    {
        return $this->hasMany(MasterDokter::class, 'spesialisasi', 'id');
    }
}