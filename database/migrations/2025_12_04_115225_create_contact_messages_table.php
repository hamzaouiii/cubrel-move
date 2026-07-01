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
    Schema::create('contact_messages', function (Blueprint $table) {
      $table->char('id', 36)->primary();
      $table->string('name', 150);
      $table->string('email', 190)->nullable();
      $table->boolean('email_confirmation')->default(false);
      $table->string('phone', 50)->nullable();
      $table->text('message')->nullable();
      $table->text('description')->nullable();
      $table->string('status', 20)->nullable()->default('new')->index();
      $table->string('ip', 45)->nullable();
      $table->string('user_agent')->nullable();
      $table->foreignUuid('owner_id')->nullable()->constrained('users')->nullOnDelete();
      $table->index('owner_id');
      $table->timestamps();

      $table->index(['ip', 'created_at'], 'contact_messages_ip_created_at_idx');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('contact_messages');
  }
};
