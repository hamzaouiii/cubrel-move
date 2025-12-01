<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('setting_items', function (Blueprint $table) {
            $table->string('label')->nullable()->after('name');
        });

        // Optional: prefill labels as "settings.items.xxx"
        DB::statement("
            UPDATE setting_items
            SET label = CONCAT(
                'settings.items.',
                REPLACE(LOWER(name), ' ', '_')
            )
            WHERE label IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('setting_items', function (Blueprint $table) {
            $table->dropColumn('label');
        });
    }
};
