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
      $table->boolean('is_global')->default(false);
      $table->boolean('is_draft')->default(false);
      $table->json('values')->nullable();
      $table->timestamps();
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
