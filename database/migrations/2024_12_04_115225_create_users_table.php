<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('users', function (Blueprint $table) {
      // Identity
      $table->uuid('id')->primary();
      $table->string('name')->unique();
      $table->string('username', 64)->unique();
      $table->string('first_name', 100)->nullable();
      $table->string('last_name', 100)->nullable();
      $table->string('type')->nullable();
      $table->text('description')->nullable();

      // Auth
      $table->string('email', 191)->unique();
      $table->timestamp('email_verified_at')->nullable();
      $table->string('password');
      $table->rememberToken();
      $table->string('two_factor_secret')->nullable();
      $table->text('two_factor_recovery_codes')->nullable();
      $table->timestamp('two_factor_confirmed_at')->nullable();
      $table->timestamp('last_login_at')->nullable();
      $table->string('last_login_ip', 45)->nullable();

      // Status & access control
      $table->string('status')->default('active')->index();
      $table->boolean('is_admin')->default(false)->index();
      $table->boolean('is_root')->default(false)->index();
      $table->timestamp('password_changed_at')->nullable();
      $table->unsignedTinyInteger('failed_login_attempts')->default(0);
      $table->timestamp('locked_until')->nullable();

      // Profile / CRM-specific
      $table->string('title', 100)->nullable();          // Mr, Ms, Dr…
      $table->string('phone', 30)->nullable();
      $table->string('mobile', 30)->nullable();
      $table->string('avatar')->nullable();              // path or URL
      $table->string('locale', 10)->default('en');
      $table->string('timezone', 64)->default('UTC');
      $table->string('date_format', 20)->default('Y-m-d');
      $table->string('time_format', 10)->default('H:i');
      $table->enum('theme', ['light', 'dark', 'system'])->default('system');


      // Soft delete + timestamps
      $table->softDeletes();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('users');
  }
};
