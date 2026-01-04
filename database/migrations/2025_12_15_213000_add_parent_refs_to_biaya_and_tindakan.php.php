<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Biaya Tambahan - add columns only when they don't exist (idempotent)
        if (!Schema::hasColumn('biaya_tambahan', 'reservasi_id')) {
            Schema::table('biaya_tambahan', function (Blueprint $table) {
                $table->unsignedBigInteger('reservasi_id')->nullable()->after('id_periksa');
            });
            Schema::table('biaya_tambahan', function (Blueprint $table) {
                try { $table->index(['reservasi_id']); } catch (\Exception $e) { /* ignore */ }
            });
        }

        if (!Schema::hasColumn('biaya_tambahan', 'homecare_reservasi_id')) {
            Schema::table('biaya_tambahan', function (Blueprint $table) {
                $table->unsignedBigInteger('homecare_reservasi_id')->nullable()->after('reservasi_id');
            });
            Schema::table('biaya_tambahan', function (Blueprint $table) {
                try { $table->index(['homecare_reservasi_id']); } catch (\Exception $e) { /* ignore */ }
            });
        }

        // Tindakan Pemeriksaan
        // Tindakan Pemeriksaan - idempotent add for each column
        if (!Schema::hasColumn('tindakan_pemeriksaan', 'reservasi_id')) {
            Schema::table('tindakan_pemeriksaan', function (Blueprint $table) {
                $table->unsignedBigInteger('reservasi_id')->nullable()->after('id_periksa');
            });
            Schema::table('tindakan_pemeriksaan', function (Blueprint $table) {
                try { $table->index(['reservasi_id']); } catch (\Exception $e) { /* ignore */ }
            });
        }

        if (!Schema::hasColumn('tindakan_pemeriksaan', 'homecare_reservasi_id')) {
            Schema::table('tindakan_pemeriksaan', function (Blueprint $table) {
                $table->unsignedBigInteger('homecare_reservasi_id')->nullable()->after('reservasi_id');
            });
            Schema::table('tindakan_pemeriksaan', function (Blueprint $table) {
                try { $table->index(['homecare_reservasi_id']); } catch (\Exception $e) { /* ignore */ }
            });
        }

        // Populate the new columns based on id_periksa values
        // If id_periksa exists in reservasi -> set reservasi_id, else if exists in homecare_reservasi -> set homecare_reservasi_id
        $bRows = DB::table('biaya_tambahan')->get();
        foreach ($bRows as $b) {
            $existsReservasi = DB::table('reservasi')->where('id', $b->id_periksa)->exists();
            $existsHome = DB::table('homecare_reservasi')->where('id', $b->id_periksa)->exists();
            if ($existsReservasi) {
                DB::table('biaya_tambahan')->where('id', $b->id)->update(['reservasi_id' => $b->id_periksa]);
            } elseif ($existsHome) {
                DB::table('biaya_tambahan')->where('id', $b->id)->update(['homecare_reservasi_id' => $b->id_periksa]);
            }
        }

        $tRows = DB::table('tindakan_pemeriksaan')->get();
        foreach ($tRows as $t) {
            $existsReservasi = DB::table('reservasi')->where('id', $t->id_periksa)->exists();
            $existsHome = DB::table('homecare_reservasi')->where('id', $t->id_periksa)->exists();
            if ($existsReservasi) {
                DB::table('tindakan_pemeriksaan')->where('id', $t->id)->update(['reservasi_id' => $t->id_periksa]);
            } elseif ($existsHome) {
                DB::table('tindakan_pemeriksaan')->where('id', $t->id)->update(['homecare_reservasi_id' => $t->id_periksa]);
            }
        }

        // Add FK constraints (attempt, ignore if already present)
        try {
            Schema::table('biaya_tambahan', function (Blueprint $table) {
                if (Schema::hasColumn('biaya_tambahan', 'reservasi_id')) {
                    $table->foreign('reservasi_id')->references('id')->on('reservasi')->onDelete('set null');
                }
                if (Schema::hasColumn('biaya_tambahan', 'homecare_reservasi_id')) {
                    $table->foreign('homecare_reservasi_id')->references('id')->on('homecare_reservasi')->onDelete('set null');
                }
            });
        } catch (\Exception $e) {
            // ignore already-existing or engine-specific FK issues
        }

        try {
            Schema::table('tindakan_pemeriksaan', function (Blueprint $table) {
                if (Schema::hasColumn('tindakan_pemeriksaan', 'reservasi_id')) {
                    $table->foreign('reservasi_id')->references('id')->on('reservasi')->onDelete('set null');
                }
                if (Schema::hasColumn('tindakan_pemeriksaan', 'homecare_reservasi_id')) {
                    $table->foreign('homecare_reservasi_id')->references('id')->on('homecare_reservasi')->onDelete('set null');
                }
            });
        } catch (\Exception $e) {
            // ignore
        }
    }

    public function down(): void
    {
        Schema::table('biaya_tambahan', function (Blueprint $table) {
            $table->dropForeign(['reservasi_id']);
            $table->dropForeign(['homecare_reservasi_id']);
            $table->dropIndex(['reservasi_id']);
            $table->dropIndex(['homecare_reservasi_id']);
            $table->dropColumn(['reservasi_id', 'homecare_reservasi_id']);
        });

        Schema::table('tindakan_pemeriksaan', function (Blueprint $table) {
            $table->dropForeign(['reservasi_id']);
            $table->dropForeign(['homecare_reservasi_id']);
            $table->dropIndex(['reservasi_id']);
            $table->dropIndex(['homecare_reservasi_id']);
            $table->dropColumn(['reservasi_id', 'homecare_reservasi_id']);
        });
    }
};
