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
    Schema::create('contacts', function (Blueprint $table) {
      $table->char('id', 36)->primary();
      $table->string('name');
      $table->text('description')->nullable();
      $table->char('account_id', 36)->nullable()->index('contacts_account_id_foreign');
      $table->string('first_name')->nullable();
      $table->string('last_name')->nullable();
      $table->string('email')->nullable();
      $table->string('phone')->nullable();
      $table->string('position')->nullable();
      $table->text('notes')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('contacts');
  }
};
