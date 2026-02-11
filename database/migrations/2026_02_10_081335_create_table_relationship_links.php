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

      $table->uuid('left_id');
      $table->uuid('right_id');

      $table->timestamps();

      $table->unique(
        ['relationship_id', 'left_id', 'right_id'],
        'rel_unique_link'
      );

      $table->index(['left_id']);
      $table->index(['right_id']);
      $table->index(['relationship_id']);

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
