<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('relationship_links', function (Blueprint $table) {
      $table->uuid('id')->primary();

      $table->uuid('relationship_id');

      $table->uuid('lhs_id');
      $table->uuid('rhs_id');

      $table->timestamps();

      $table->unique(
        ['relationship_id', 'lhs_id', 'rhs_id'],
        'rel_unique_link'
      );

      $table->index(['lhs_id']);
      $table->index(['rhs_id']);
      $table->index(['relationship_id']);

      // FK is safe here because relationships are system-defined
      $table->foreign('relationship_id')
        ->references('id')
        ->on('relationships')
        ->cascadeOnDelete();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('relationship_links');
  }
};
