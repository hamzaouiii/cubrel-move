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

      $table->string('lhs_module');
      $table->string('rhs_module');

      $table->string('relationship_type'); // one-to-one | one-to-many | many-to-many

      // for future DB-level optimizations
      $table->string('join_table')->nullable();
      $table->string('lhs_key')->nullable();
      $table->string('rhs_key')->nullable();

      $table->timestamps();

      $table->index(['lhs_module']);
      $table->index(['rhs_module']);
      $table->index(['relationship_type']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('relationships');
  }
};
