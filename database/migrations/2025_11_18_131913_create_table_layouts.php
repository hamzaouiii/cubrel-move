<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('layouts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('module_id')->nullable();
            $table->foreign('module_id')
                ->references('id')
                ->on('modules')
                ->onDelete('cascade');

            $table->enum('type', ['list', 'record', 'form'])->index();
            $table->string('module_name')->nullable();

            $table->string('name')->nullable();

            $table->json('definition');

            $table->boolean('is_record_default')->default(false);
            $table->boolean('is_list_default')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('layouts');
    }
};