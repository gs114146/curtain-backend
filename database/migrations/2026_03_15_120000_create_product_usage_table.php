<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('usage_id')->constrained('usage')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['product_id', 'usage_id']);
        });

        // Migrate existing single usage_id values into the pivot table
        $products = DB::table('products')
            ->whereNotNull('usage_id')
            ->select('id', 'usage_id', 'created_at', 'updated_at')
            ->get();

        $now = now();
        $rows = [];
        foreach ($products as $product) {
            $rows[] = [
                'product_id' => $product->id,
                'usage_id' => $product->usage_id,
                'created_at' => $product->created_at ?? $now,
                'updated_at' => $product->updated_at ?? $now,
            ];
        }

        if (!empty($rows)) {
            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('product_usage')->insert($chunk);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_usage');
    }
};
