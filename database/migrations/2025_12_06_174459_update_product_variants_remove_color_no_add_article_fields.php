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
        Schema::table('product_variants', function (Blueprint $table) {
            // Remove color_no column
            $table->dropColumn('color_no');

            // Add article and article_description columns (both required)
            $table->string('article')->after('color_id');
            $table->text('article_description')->after('article');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            // Remove new columns
            $table->dropColumn(['article', 'article_description']);

            // Restore color_no column
            $table->string('color_no')->nullable()->after('color_id');
        });
    }
};
