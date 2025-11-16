<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtpAuditLogsTable extends Migration
{
    public function up()
    {
        Schema::create('otp_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('action'); // request_sent, verification_attempt, verification_success, verification_failed, rate_limited, blocked
            $table->text('meta')->nullable(); // JSON metadata: ip, user_agent, reason, otp_id, attempts, send_result
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('otp_audit_logs');
    }
}
