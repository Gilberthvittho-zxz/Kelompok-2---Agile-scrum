<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_transaction_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_transaction_id')
                ->constrained('sales_transactions')
                ->cascadeOnDelete();
            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();
            $table->string('product_name_snapshot', 150); // snapshot nama saat transaksi
            $table->string('product_code_snapshot', 50)->nullable();
            $table->integer('qty');
            $table->decimal('price', 14, 2);    // snapshot harga jual saat transaksi
            $table->decimal('subtotal', 14, 2);
            $table->timestamps();

            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_transaction_details');
    }
};
