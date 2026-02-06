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
    Schema::table('fields', function (Blueprint $table) {
      $table->uuid('dropdown_list_id')->nullable();
      $table->foreign('dropdown_list_id')->references('id')->on('dropdown_lists')->cascadeOnDelete();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('fields', function (Blueprint $table) {
      $table->dropColumn('dropdown_list_id');
    });
  }
};
