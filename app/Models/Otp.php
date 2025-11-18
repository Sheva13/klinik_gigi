<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Otp extends Model
{
    protected $table = 'otps';

    protected $fillable = [
        'email',
        'code_hash',
        'expires_at',
        'used_at',
        'attempts',
        'request_ip',
        'user_agent',
    ];

    protected $dates = [
        'expires_at',
        'used_at',
        'created_at',
        'updated_at',
    ];

    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    public function isUsed()
    {
        return $this->used_at !== null;
    }

    public function markUsed()
    {
        $this->used_at = now(); 
        $this->save(['timestamps' => true]);
    }
}
