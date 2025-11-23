<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setting_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Relationship to settings table
            $table->uuid('setting_id');
            $table->foreign('setting_id')
                  ->references('id')
                  ->on('settings')
                  ->onDelete('cascade');

            // Columns
            $table->string('name');
            $table->string('path')->nullable();
            $table->string('icon')->nullable();
            $table->string('category')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_items');
    }
};
