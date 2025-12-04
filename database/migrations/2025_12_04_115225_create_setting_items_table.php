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
        Schema::create('setting_items', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('setting_id', 36)->index('setting_items_setting_id_foreign');
            $table->string('name');
            $table->string('label')->nullable();
            $table->string('path')->nullable();
            $table->string('icon')->nullable();
            $table->string('category')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setting_items');
    }
};
