<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('products', function (Blueprint $table) {
      $table->uuid('id')->primary();

      $table->string('name');
      $table->string('sku')->nullable();
      $table->text('description')->nullable();

      $table->string('category')->nullable()->index();
      $table->decimal('price', 15, 2)->nullable();
      $table->string('unit')->nullable();
      $table->string('tax_rate')->nullable();
      
      $table->boolean('is_active')->default(true)->index();
      $table->json('custom_fields')->default(DB::raw("(JSON_OBJECT())"));

      $table->foreignUuid('owner_id')->nullable()->constrained('users')->nullOnDelete();
      $table->index('owner_id');

      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('products');
  }
};
