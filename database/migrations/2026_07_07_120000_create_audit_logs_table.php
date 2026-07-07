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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('module_slug')->nullable()->index();
            $table->string('record_id')->nullable()->index();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('impersonator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->json('diff')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['module_slug', 'record_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
