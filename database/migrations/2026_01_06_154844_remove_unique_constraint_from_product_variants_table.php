<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop and recreate foreign keys to remove dependency on unique index
        Schema::table('product_variants', function (Blueprint $table) {
            // Drop foreign keys
            $table->dropForeign(['product_id']);
            $table->dropForeign(['color_id']);
        });

        // Drop the unique constraint
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropUnique(['product_id', 'color_id']);
        });

        // Recreate foreign keys
        Schema::table('product_variants', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('color_id')->references('id')->on('colors')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            // Restore the unique constraint
            $table->unique(['product_id', 'color_id']);
        });
    }
};
