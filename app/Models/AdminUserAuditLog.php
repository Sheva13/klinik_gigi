<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminUserAuditLog extends Model
{
    protected $fillable = [
        'admin_id',
        'user_id',
        'old_data',
        'new_data',
        'alasan',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function user()
    {
        return $this->belongsTo(MpUser::class, 'user_id', 'user_id');
    }
}
