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
      $table->char('id', 36)->primary();
      $table->string('name');
      $table->string('key')->unique();
      $table->string('type')->nullable();
      $table->string('label')->nullable();
      $table->char('module_id', 36);


      $table->boolean('is_custom')->default(false);
      $table->boolean('is_active')->default(false);
      $table->boolean('readonly')->default(false);
      $table->boolean('hidden')->default(false);
      $table->boolean('nullable')->default(true);
      $table->boolean('required')->default(false);
      $table->boolean('searchable')->default(false);
      $table->boolean('filterable')->default(false);
      $table->boolean('sortable')->default(false);

      $table->string('database_type')->nullable();
      $table->string('default_value')->nullable();
      $table->json('options')->nullable();
      $table->integer('min_length')->nullable();
      $table->integer('max_length')->nullable();
      $table->string('regex')->nullable();

      $table->foreign(['module_id'])->references(['id'])->on('modules')->onUpdate('no action')->onDelete('cascade');
      $table->softDeletes();
      $table->timestamps();


      $table->index(['module_id', 'is_custom', 'is_active']);
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
