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
        Schema::create('layouts', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('module_id', 36)->nullable()->index('layouts_module_id_foreign');
            $table->enum('type', ['list', 'record', 'form'])->index();
            $table->string('module_name')->nullable();
            $table->string('name')->nullable();
            $table->json('definition');
            $table->boolean('is_record_default')->default(false);
            $table->boolean('is_list_default')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('layouts');
    }
};
