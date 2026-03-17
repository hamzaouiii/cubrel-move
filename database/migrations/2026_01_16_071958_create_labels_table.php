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
    Schema::create('labels', function (Blueprint $table) {
      $table->char('id', 36)->primary();
      $table->string('key')->unique();
      $table->string('value');
      $table->char('module_id', 36)->nullable();
      $table->boolean('global')->default(false);
      $table->boolean('is_custom')->default(true);
      $table->boolean('is_draft')->default(false);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('labels');
  }
};
