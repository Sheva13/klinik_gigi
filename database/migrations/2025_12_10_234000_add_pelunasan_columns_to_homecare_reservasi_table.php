<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('homecare_reservasi', function (Blueprint $table) {
            $table->enum('status_pelunasan', ['belum_lunas', 'lunas', 'gagal'])->nullable()->default('belum_lunas');
            $table->string('snap_token_pelunasan')->nullable()->after('status_pelunasan');
            $table->decimal('total_biaya_tindakan', 12, 0)->nullable()->default(0)->after('snap_token_pelunasan');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('homecare_reservasi', function (Blueprint $table) {
            $table->dropColumn(['status_pelunasan', 'snap_token_pelunasan', 'total_biaya_tindakan']);
        });
    }
};
