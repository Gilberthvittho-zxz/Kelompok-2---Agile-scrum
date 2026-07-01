<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->dateTime('opname_date');
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('opname_date');
        });

        Schema::create('stock_opname_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')
                ->constrained('stock_opnames')
                ->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->string('product_name_snapshot', 150);
            $table->string('product_code_snapshot', 50)->nullable();
            $table->integer('qty_system');   // stok menurut sistem saat opname
            $table->integer('qty_physical'); // stok fisik hasil hitung
            $table->integer('qty_diff');     // qty_physical - qty_system (bisa negatif)
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opname_details');
        Schema::dropIfExists('stock_opnames');
    }
};
