<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pdf_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('module_slug');
            $table->string('name');
            $table->string('blade_view');
            $table->string('layout_id', 36)->nullable();
            $table->string('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->json('definition')->nullable();

            $table->timestamps();

            $table->index('module_slug', 'layout_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdf_templates');
    }
};
