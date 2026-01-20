<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('setting_values', function (Blueprint $table) {
      // First, drop the foreign key constraint if it exists
      $table->dropForeign(['setting_item_id']);

      // Drop the old column
      $table->dropColumn('setting_item_id');

      // Add the new string column
      $table->string('setting_item')->after('id')->nullable();

      // Add index for better query performance
      $table->index('setting_item');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('setting_values', function (Blueprint $table) {
      // Remove the new column
      $table->dropColumn('setting_item');
      $table->dropIndex(['setting_item']);

      // Re-add the old column
      $table->uuid('setting_item_id')->after('id')->nullable();

      // Re-add foreign key (assuming setting_items table exists)
      // Note: This might need adjustment based on your actual table structure
      $table->foreign('setting_item_id')
        ->references('id')
        ->on('setting_items')
        ->onDelete('set null');
    });
  }
};
