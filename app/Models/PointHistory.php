<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointHistory extends Model
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
        'transaction_type', // 'credit' atau 'debit'
        'amount',           // Jumlah poin
        'description',      // Keterangan transaksi
    ];
    
    // Hubungan (relationship) ke model User
    public function user()
    {
        // Ganti 'User::class' jika nama Model pengguna utama Anda berbeda
        return $this->belongsTo(User::class); 
    }
}
