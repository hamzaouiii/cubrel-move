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
    Schema::create('userinvites', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->string('email')->unique();
      $table->string('description')->nullable();
      $table->string('name')->nullable();
      $table->string('status')->nullable()->default("pending");
      $table->string('token_hash', 64)->unique();
      $table->uuid('invited_by');
      $table->foreign('invited_by')->references('id')->on('users');
      $table->boolean('is_admin')->default(false);
      $table->timestamp('accepted_at')->nullable();
      $table->timestamp('expires_at')->index();
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
    Schema::dropIfExists('userinvites');
  }
};
