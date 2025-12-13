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
        Schema::table('products', function (Blueprint $table) {
            $table->string('horizontal_repeat')->nullable()->after('weight');
            $table->string('vertical_repeat')->nullable()->after('horizontal_repeat');
            $table->string('direction')->nullable()->after('vertical_repeat');
            $table->text('extra_data')->nullable()->after('direction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['horizontal_repeat', 'vertical_repeat', 'direction', 'extra_data']);
        });
    }
};
