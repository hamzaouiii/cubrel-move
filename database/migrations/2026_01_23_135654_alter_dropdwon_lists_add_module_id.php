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
    Schema::table('dropdown_lists', function (Blueprint $table) {
      $table->string('module_id')->nullable()->after('field_key');
      $table->foreign('module_id')
        ->references('id')
        ->on('modules')
        ->cascadeOnDelete();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('dropdown_lists', function (Blueprint $table) {
      $table->dropColumn('module_id');
      $table->dropIndex(['module_id']);
    });
  }
};
