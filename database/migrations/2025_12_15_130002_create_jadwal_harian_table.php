<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_harian', function (Blueprint $table) {
            $table->id();
            $table->string('kode_jadwal', 10);
            $table->date('tanggal');
            $table->integer('validasi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_harian');
    }
};