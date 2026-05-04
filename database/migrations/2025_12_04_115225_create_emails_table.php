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
    Schema::create('emails', function (Blueprint $table) {
      $table->char('id', 36)->primary();
      $table->string('name');
      $table->text('description')->nullable();
      $table->string('to', 320)->nullable();
      $table->boolean('sent')->default(false);
      $table->string('subject')->nullable();
      $table->text('mailable_class');
      $table->unsignedBigInteger('related_id')->nullable();
      $table->string('status', 32)->default('queued');
      $table->text('error')->nullable();
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
    Schema::dropIfExists('emails');
  }
};
