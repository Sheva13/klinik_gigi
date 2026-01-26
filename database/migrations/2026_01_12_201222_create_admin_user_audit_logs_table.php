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
        Schema::create('admin_user_audit_logs', function (Blueprint $table) {
            $table->id();
    
            $table->foreignId('admin_id')
                  ->constrained('admins')
                  ->cascadeOnDelete();
    
            $table->string('user_id');
            $table->json('old_data');
            $table->json('new_data');
            $table->text('alasan');
    
            $table->timestamps();
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_user_audit_logs');
    }
};
