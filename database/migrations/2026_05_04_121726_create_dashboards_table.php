<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
      Schema::create('dashboards', function (Blueprint $table) {
    $table->id();

    $table->uuid('user_id')->nullable();

    $table->string('name')->nullable();
    $table->string('slug')->unique();
    $table->boolean('is_default')->default(false);
    $table->json('layout')->nullable();
    $table->integer('sort_order')->default(0);
    $table->timestamps();

    $table->foreign('user_id')
        ->references('id')
        ->on('users')
        ->nullOnDelete();

    $table->index(['user_id', 'is_default']);
});
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboards');
    }
};