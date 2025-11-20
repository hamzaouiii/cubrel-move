<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('layouts', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->uuid('module_id')->unique();
            $table->foreign('module_id')
                ->references('id')
                ->on('modules')
                ->onDelete('cascade');

            // list / record / form / etc.
            $table->enum('type', ['list', 'record', 'form'])->index();

            // optional human readable name
            $table->string('name')->nullable();

            // the actual layout configuration (JSON)
            $table->json('definition');

            // in case you later want multiple layouts per type
            $table->boolean('is_default')->default(true);

            $table->timestamps();
            // one default layout per module + type
            $table->unique(['type', 'is_default'], 'layouts_module_type_default_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('layouts');
    }
};