<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transformation_steps', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('transformation_id');
            $table->unsignedSmallInteger('order')->default(0);
            $table->string('type');
            $table->json('configuration')->nullable();
            $table->timestamps();

            $table->index(['transformation_id', 'order']);

            $table->foreign('transformation_id')
                ->references('id')
                ->on('transformations')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transformation_steps');
    }
};
