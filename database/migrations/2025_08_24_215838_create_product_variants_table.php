<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('variant_name');
            $table->enum('variant_type', ['flex', 'loft', 'length', 'color', 'hand', 'size']);
            $table->string('variant_value');
            $table->decimal('price', 10, 2)->nullable();
            $table->string('sku')->unique()->nullable();
            $table->boolean('in_stock')->default(true);
            $table->integer('stock_quantity')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['product_id', 'variant_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
