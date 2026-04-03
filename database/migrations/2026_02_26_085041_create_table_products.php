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
      $table->string('currency', 3)->nullable();

      $table->boolean('is_active')->nullable()->index();
      $table->json('custom_fields')->default(DB::raw("(JSON_OBJECT())"));


      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('products');
  }
};
