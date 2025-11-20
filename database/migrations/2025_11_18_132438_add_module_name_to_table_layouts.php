<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('layouts', function (Blueprint $table) {
            $table->string('module_name')->unique()->after('module_id');
        });
    }

    public function down(): void
    {
        Schema::table('layouts', function (Blueprint $table) {
            $table->dropColumn(['module_name']);
        });
    }
};
