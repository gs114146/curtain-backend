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
        Schema::create('product_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('file_path'); // Path to the uploaded file
            $table->string('file_name'); // Original file name
            $table->string('file_type'); // image or video
            $table->string('mime_type'); // e.g., image/jpeg, video/mp4
            $table->string('file_size')->nullable(); // File size in bytes
            $table->integer('sort_order')->default(0); // For ordering images/videos
            $table->boolean('is_primary')->default(false); // Primary image/video
            $table->string('alt_text')->nullable(); // Alt text for accessibility
            $table->text('caption')->nullable(); // Optional caption
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_media');
    }
};
