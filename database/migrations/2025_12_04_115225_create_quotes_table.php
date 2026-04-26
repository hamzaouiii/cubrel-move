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
    Schema::create('quotes', function (Blueprint $table) {
      $table->char('id', 36)->primary();
      $table->string('name');
      $table->text('description')->nullable();
      $table->string('number')->unique();
      $table->string('status')->default('draft');
      $table->date('valid_until')->nullable();
      $table->string('currency', 3)->default('EUR');
      $table->decimal('subtotal', 15)->default(0);
      $table->decimal('tax', 15)->default(0);
      $table->decimal('total', 15)->default(0);
      $table->text('notes')->nullable();
      $table->foreignUuid('owner_id')->nullable()->constrained('users')->nullOnDelete();
      $table->index('owner_id');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('quotes');
  }
};
