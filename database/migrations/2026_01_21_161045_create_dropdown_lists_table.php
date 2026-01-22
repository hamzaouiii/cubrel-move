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
    Schema::create('dropdown_lists', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->string('key')->unique();
      $table->string('field_key')->nullable();
      $table->boolean('is_global')->default(false);
      $table->json('values')->nullable();
      $table->timestamps();
      $table->foreign('field_key')
        ->references('key')
        ->on('fields')
        ->cascadeOnDelete();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('dropdown_lists');
  }
};
