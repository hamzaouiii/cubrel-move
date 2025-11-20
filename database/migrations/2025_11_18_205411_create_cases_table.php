<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cases', function (Blueprint $table) {
            $table->uuid('id')->primary();   
            $table->string('name');
            $table->foreignUuid('account_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignUuid('contact_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string('subject');
            $table->text('description')->nullable();
            $table->string('status')->default('open'); // open, in_progress, closed
            $table->string('priority')->default('normal'); // low, normal, high, urgent
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cases');
    }
};
