<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('opportunities', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->string('name');
      $table->decimal('amount', 15, 2)->nullable();
      $table->string('currency', 3)->default('EUR');
      $table->text('description')->nullable();
      $table->string('sales_stage')->nullable();
      $table->unsignedTinyInteger('probability')->nullable(); // 0-100
      $table->date('expected_close_date')->nullable();
      $table->string('type')->nullable(); // new_business, existing_business
      $table->json('custom_fields')->default(DB::raw("(JSON_OBJECT())"));
      // $table->uuid('assigned_user_id')->nullable()->index();

      $table->timestamps();

      $table->index(['sales_stage', 'expected_close_date']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('opportunities');
  }
};
