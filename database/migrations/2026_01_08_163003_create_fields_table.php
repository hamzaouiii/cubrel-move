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
    Schema::create('fields', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->string('name');
      $table->string('key')->unique();
      $table->string('type')->nullable();
      $table->string('label')->nullable();
      $table->char('module_id', 36)->nullable();
      $table->boolean('is_global')->default(false);
      $table->boolean('is_default')->default(false);
      $table->boolean('is_default_for_line_items')->default(false);
      $table->boolean('is_draft')->default(false);
      $table->boolean('is_custom')->default(false);
      $table->boolean('is_active')->default(false);
      $table->boolean('is_calculated')->default(false);
      $table->boolean('readonly')->default(false);
      $table->boolean('hidden')->default(false);
      $table->boolean('required')->default(false);
      $table->boolean('searchable')->default(false);
      $table->boolean('filterable')->default(false);
      $table->boolean('sortable')->default(false);
      $table->boolean('enable_mass_update')->default(true);

      $table->string('related_module')->nullable();
      $table->string('default_value')->nullable();
      $table->json('options')->nullable();
      $table->integer('min_length')->nullable();
      $table->integer('max_length')->nullable();
      $table->string('regex')->nullable();
      $table->uuid('dropdown_list_id')->nullable();
      $table->foreign('dropdown_list_id')->references('id')->on('dropdown_lists')->cascadeOnDelete();
      $table->foreign(['module_id'])->references(['id'])->on('modules')->onDelete('cascade');
      $table->timestamps();


      $table->index(['module_id', 'is_custom', 'is_active', 'is_global']);
      $table->index(['key', 'type']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('fields');
  }
};
