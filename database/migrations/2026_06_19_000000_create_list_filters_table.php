<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('list_filters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('module_slug')->nullable();
            $table->string('slug')->nullable();
            $table->string('label')->nullable();
            $table->string('name');
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_shared')->default(false);
            $table->boolean('is_system')->default(false);
            $table->boolean('is_global')->default(false);
            $table->json('conditions');
            $table->string('match_type')->nullable()->default('all');
            $table->dateTime('last_used')->nullable();
            $table->timestamps();

            $table->index(['module_slug', 'slug']);
            $table->index(['module_slug', 'user_id']);
            $table->index('user_id');
            $table->index('is_global');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('list_filters');
    }
};
