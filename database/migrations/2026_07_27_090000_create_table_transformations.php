<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transformations', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name');
            $table->string('source_module');
            $table->string('target_module');
            $table->text('description')->nullable();
            $table->boolean('enabled')->default(true);
            $table->boolean('automation_enabled')->default(false);
            $table->json('conditions')->nullable();
            $table->string('conditions_match')->default('all');
            $table->boolean('link_records_enabled')->default(true);
            $table->uuid('relationship_id')->nullable();
            $table->timestamps();

            $table->index(['source_module', 'target_module']);

            $table->foreign('relationship_id')
                ->references('id')
                ->on('relationships')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transformations');
    }
};
