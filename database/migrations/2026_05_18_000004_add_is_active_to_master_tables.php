<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('description');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('address');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('categories', fn (Blueprint $t) => $t->dropColumn('is_active'));
        Schema::table('suppliers', fn (Blueprint $t) => $t->dropColumn('is_active'));
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn('is_active'));
    }
};
