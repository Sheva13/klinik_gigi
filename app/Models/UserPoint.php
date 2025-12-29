<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPoint extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * Kolom yang diizinkan untuk diisi secara massal (mass-assignment).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'current_points',
    ];

    // Hubungan (relationship) ke model User
    public function user()
    {
        // Ganti 'User::class' jika nama Model pengguna utama Anda berbeda (misalnya MpUser::class)
        return $this->belongsTo(User::class); 
    }
}
