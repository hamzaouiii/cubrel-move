<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('relationships', function (Blueprint $table) {
      $table->uuid('id')->primary();

      $table->string('name')->unique();

      $table->string('left_module');
      $table->string('right_module');

      $table->string('relationship_type');

      // for future DB-level optimizations
      $table->string('join_table')->nullable();
      $table->string('left_module_key')->nullable();
      $table->string('right_module_key')->nullable();

      $table->timestamps();

      $table->index(['left_module']);
      $table->index(['right_module']);
      $table->index(['relationship_type']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('relationships');
  }
};
