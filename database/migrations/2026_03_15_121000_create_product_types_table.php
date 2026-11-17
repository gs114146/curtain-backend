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
        Schema::create('product_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('type');
            $table->timestamps();

            $table->unique(['product_id', 'type']);
        });

        // Migrate existing single type values into the pivot table
        $products = DB::table('products')
            ->whereNotNull('type')
            ->select('id', 'type', 'created_at', 'updated_at')
            ->get();

        $now = now();
        $rows = [];
        foreach ($products as $product) {
            $rows[] = [
                'product_id' => $product->id,
                'type' => $product->type,
                'created_at' => $product->created_at ?? $now,
                'updated_at' => $product->updated_at ?? $now,
            ];
        }

        if (!empty($rows)) {
            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('product_types')->insert($chunk);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_types');
    }
};
