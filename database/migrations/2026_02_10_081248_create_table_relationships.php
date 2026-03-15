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
      $table->string("label");
      $table->string('left_module');
      $table->string('right_module');
      $table->string('type');
      $table->boolean('is_system')->default(true);
      // for future DB-level optimizations
      $table->string('join_table')->default("relationship_links");
      $table->timestamps();

      $table->index(['left_module']);
      $table->index(['right_module']);
      $table->index(['type']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('relationships');
  }
};
