<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpAuditLog extends Model
{
    protected $table = 'otp_audit_logs';

    protected $fillable = [
        'email',
        'action',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
