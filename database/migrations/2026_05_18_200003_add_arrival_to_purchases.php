<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->date('arrival_date')->nullable()->after('purchase_date');
            $table->dateTime('arrived_at')->nullable()->after('arrival_date');
            $table->foreignId('arrived_by')->nullable()->after('arrived_at')->constrained('users')->nullOnDelete();
        });

        // Extend status enum: pending / confirmed / voided
        DB::statement("ALTER TABLE purchases MODIFY status ENUM('pending','confirmed','voided') NOT NULL DEFAULT 'confirmed'");

        // Backfill: arrival_date untuk data lama = purchase_date
        DB::statement("UPDATE purchases SET arrival_date = purchase_date WHERE arrival_date IS NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE purchases MODIFY status ENUM('confirmed','voided') NOT NULL DEFAULT 'confirmed'");

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('arrived_by');
            $table->dropColumn(['arrival_date', 'arrived_at']);
        });
    }
};
