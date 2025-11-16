<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtpsTable extends Migration
{
    public function up()
    {
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('code_hash'); // hashed OTP
            $table->timestamp('expires_at')->index();
            $table->timestamp('used_at')->nullable()->index();
            $table->integer('attempts')->default(0); // verification attempts for this OTP
            $table->string('request_ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });

        Schema::table('otps', function (Blueprint $table) {
            $table->index(['email', 'expires_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('otps');
    }
}
