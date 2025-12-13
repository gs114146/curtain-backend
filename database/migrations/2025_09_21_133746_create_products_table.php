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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('sku')->unique(); // Stock Keeping Unit
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->string('dimensions')->nullable(); // e.g., "120cm x 150cm"
            $table->string('weight')->nullable();

            // Foreign keys to related tables
            $table->foreignId('color_id')->constrained('colors')->onDelete('cascade');
            $table->foreignId('design_id')->constrained('designs')->onDelete('cascade');
            $table->foreignId('material_id')->constrained('materials')->onDelete('cascade');
            $table->foreignId('transparency_id')->constrained('transparency')->onDelete('cascade');
            $table->foreignId('characteristic_id')->constrained('characteristics')->onDelete('cascade');

            // Product status and visibility
            $table->enum('status', ['active', 'inactive', 'draft'])->default('active');
            $table->boolean('featured')->default(false);
            $table->boolean('in_stock')->default(true);

            // SEO fields
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('slug')->unique();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
