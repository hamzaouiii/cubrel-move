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
    Schema::create('modules', function (Blueprint $table) {
      $table->char('id', 36)->primary();
      $table->string('name');
      $table->string('slug')->unique();
      $table->string('label')->nullable();
      $table->string('icon')->default('fa-bahai');
      $table->string('color')->default('#0d6efd');
      $table->string('path');
      $table->unsignedInteger('sort_order')->default(0);
      $table->boolean('is_active')->default(true);
      $table->boolean('show_in_sidebar')->default(true);
      $table->string('handler_class')->nullable();
      $table->text('description')->nullable();
      $table->boolean('can_view')->default(true);
      $table->boolean('can_create')->default(true);
      $table->boolean('can_edit')->default(true);
      $table->boolean('can_delete')->default(true);
      $table->string('model_class')->nullable();
      $table->string('table_name')->nullable();
      $table->timestamps();
      $table->boolean('is_custom')->default(false);
      $table->boolean('is_draft')->default(false);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('modules');
  }
};
