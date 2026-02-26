<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('products', function (Blueprint $table) {
      $table->uuid('id')->primary();

      $table->string('name');
      $table->string('sku')->unique();
      $table->text('description')->nullable();

      $table->string('category')->nullable()->index();
      $table->decimal('price', 15, 2);
      $table->string('currency', 3)->default('EUR');

      $table->boolean('is_active')->default(true)->index();

      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('products');
  }
};
