<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('orders', function (Blueprint $table) {
      $table->uuid('id')->primary();

      $table->string('name');
      $table->string('order_number')->unique();

      $table->uuid('opportunity_id')->nullable();
      $table->text('description')->nullable();

      $table->decimal('total_amount', 15, 2)->nullable();
      $table->string('currency', 3)->default('EUR');

      $table->string('status')->index();
      // draft, confirmed, shipped, completed, cancelled

      $table->date('order_date')->nullable();
      $table->date('due_date')->nullable();

      $table->uuid('assigned_user_id')->nullable()->index();

      $table->timestamps();

      $table->index(['status', 'order_date']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('orders');
  }
};
