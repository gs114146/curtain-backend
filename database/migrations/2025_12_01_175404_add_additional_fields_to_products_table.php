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
            $table->string('color_no')->nullable()->after('color_id');
            $table->text('designs')->nullable()->after('design_id');
            $table->text('compositions')->nullable()->after('material_id');
            $table->string('hsn_code')->nullable()->after('usage_id');
            $table->decimal('gst', 5, 2)->nullable()->after('hsn_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['color_no', 'designs', 'compositions', 'hsn_code', 'gst']);
        });
    }
};
