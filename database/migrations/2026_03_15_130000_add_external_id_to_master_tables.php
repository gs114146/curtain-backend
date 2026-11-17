<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Internal CSV/client IDs used only for import mapping (not exposed in admin/API).
     */
    public function up(): void
    {
        $tables = ['colors', 'designs', 'materials', 'transparency', 'characteristics', 'usage'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedInteger('external_id')->nullable()->after('id');
                $table->unique('external_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['colors', 'designs', 'materials', 'transparency', 'characteristics', 'usage'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropUnique(['external_id']);
                $table->dropColumn('external_id');
            });
        }
    }
};
