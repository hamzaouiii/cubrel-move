<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('label')->nullable()->after('name');
        });

        // Optional: prefill labels as "settings.groups.xxx"
        // Example: "System Settings" -> "settings.groups.system_settings"
        DB::statement("
            UPDATE settings
            SET label = CONCAT(
                'settings.groups.',
                REPLACE(LOWER(name), ' ', '_')
            )
            WHERE label IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('label');
        });
    }
};
