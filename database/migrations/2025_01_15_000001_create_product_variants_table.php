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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('color_id')->constrained('colors')->onDelete('cascade');

            // Variant-specific SKU (optional, if not provided, auto-generate)
            $table->string('sku')->nullable();

            // Variant-specific pricing and stock
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->integer('stock_quantity')->nullable();

            // Variant status and visibility
            $table->enum('status', ['active', 'inactive', 'draft'])->default('active');
            $table->boolean('in_stock')->default(true);
            $table->boolean('is_default')->default(false); // Default variant for the product

            // Sort order for variants
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            // Ensure unique color per product
            $table->unique(['product_id', 'color_id']);
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
