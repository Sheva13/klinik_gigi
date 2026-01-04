<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('point_histories', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel users (gunakan tipe data yang sama dengan user_id di tabel users)
            // Di User.php: protected $primaryKey = 'user_id'; protected $keyType = 'string';
            $table->string('user_id'); 
            
            $table->integer('amount')->comment('Positif = nambah, Negatif = kurang');
            $table->string('type', 50)->comment('earn, redeem, adjustment, expired');
            $table->string('description')->nullable();
            
            // Opsional: Referensi ke ID transaksi (jika ada)
            $table->string('reference_id')->nullable()->comment('Misal: no_pemeriksaan');
            
            $table->timestamps();

            // Foreign key (opsional, tergantung engine DB dan konsistensi data legacy)
            // $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_histories');
    }
};
