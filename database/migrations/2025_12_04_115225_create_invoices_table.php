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
    Schema::create('invoices', function (Blueprint $table) {
      $table->char('id', 36)->primary();
      $table->string('name');
      $table->text('description')->nullable();
      $table->char('account_id', 36)->nullable()->index('invoices_account_id_foreign');
      $table->char('contact_id', 36)->nullable()->index('invoices_contact_id_foreign');
      $table->char('quote_id', 36)->nullable()->index('invoices_quote_id_foreign');
      $table->string('number')->unique();
      $table->string('status')->default('draft');
      $table->date('issue_date')->nullable();
      $table->date('due_date')->nullable();
      $table->string('currency', 3)->default('EUR');
      $table->decimal('subtotal', 15)->default(0);
      $table->decimal('tax', 15)->default(0);
      $table->decimal('total', 15)->default(0);
      $table->text('notes')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('invoices');
  }
};
