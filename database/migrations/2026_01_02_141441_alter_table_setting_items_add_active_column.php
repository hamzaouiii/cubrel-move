<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('setting_items', function (Blueprint $table) {
      $table
        ->boolean('active')
        ->default(true)
        ->after('category');
    });
  }

  public function down(): void
  {
    Schema::table('setting_items', function (Blueprint $table) {
      $table->dropColumn('active');
    });
  }
};
