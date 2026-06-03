<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom reason + note di detail
        Schema::table('stock_adjustment_details', function (Blueprint $table) {
            $table->string('reason', 20)->after('qty_diff')->default('koreksi');
            $table->text('note')->nullable()->after('reason');
        });

        // Backfill: copy reason header ke semua detail-nya
        DB::statement("
            UPDATE stock_adjustment_details d
            JOIN stock_adjustments h ON d.stock_adjustment_id = h.id
            SET d.reason = h.reason
        ");

        // Drop kolom reason dari header (tidak dipakai lagi)
        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->dropColumn('reason');
        });
    }

    public function down(): void
    {
        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->enum('reason', ['rusak', 'expired', 'hilang', 'koreksi', 'opname', 'lain'])
                ->default('koreksi')->after('adjustment_date');
        });

        // Backfill balik: ambil reason pertama dari detail
        DB::statement("
            UPDATE stock_adjustments h
            JOIN (SELECT stock_adjustment_id, MIN(reason) AS r FROM stock_adjustment_details GROUP BY stock_adjustment_id) x
              ON x.stock_adjustment_id = h.id
            SET h.reason = x.r
        ");

        Schema::table('stock_adjustment_details', function (Blueprint $table) {
            $table->dropColumn(['reason', 'note']);
        });
    }
};
