<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pdf_templates', function (Blueprint $table) {
            $table->string('layout_id', 36)->nullable()->after('blade_view')->index();
        });
    }

    public function down(): void
    {
        Schema::table('pdf_templates', function (Blueprint $table) {
            $table->dropColumn('layout_id');
        });
    }
};
