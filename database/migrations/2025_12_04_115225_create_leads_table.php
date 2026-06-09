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
    Schema::create('leads', function (Blueprint $table) {
      $table->char('id', 36)->primary();
      $table->string('name');
      $table->string('first_name')->nullable();
      $table->string('last_name')->nullable();
      $table->string('email')->nullable();
      $table->string('phone')->nullable();
      $table->string('company')->nullable();
      $table->json('address')->nullable();
      $table->text('description')->nullable();
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
    Schema::dropIfExists('leads');
  }
};
