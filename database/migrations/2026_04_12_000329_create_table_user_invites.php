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
    Schema::create('user_invites', function (Blueprint $table) {
      $table->uuid('id');
      $table->string('email')->unique();
      $table->string('status')->default("pending");
      $table->string('token', 64)->unique();
      $table->uuid('invited_by');
      $table->foreign('invited_by')->references('id')->on('users');
      $table->boolean('is_admin')->default(false);
      $table->timestamp('accepted_at')->nullable();
      $table->timestamp('expires_at');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('user_invites');
  }
};
