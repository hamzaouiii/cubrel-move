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
    Schema::create('cases', function (Blueprint $table) {
      $table->char('id', 36)->primary();
      $table->string('name');
      $table->string('subject');
      $table->text('description')->nullable();
      $table->string('status')->default('open');
      $table->string('priority')->default('normal');
      $table->timestamp('opened_at')->nullable();
      $table->timestamp('closed_at')->nullable();
      $table->foreignUuid('owner_id')->nullable()->constrained('users')->nullOnDelete();
      $table->index('owner_id');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('cases');
  }
};
