<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::table('dropdown_lists', function (Blueprint $table) {
      $table->boolean('is_status')->default(false)->after('is_draft');
    });

    // Flag the lists already backing status-typed stock fields, using the
    // same "{module}_{field}_list" convention as StockFieldsSeeder, so
    // existing databases don't need a fresh migrate to pick this up.
    $keys = [];
    foreach (config('stock_fields', []) as $moduleSlug => $definitions) {
      foreach ($definitions as $fieldKey => $definition) {
        if (($definition['type'] ?? null) === 'status') {
          $keys[] = $fieldKey === 'currency' ? "{$fieldKey}_list" : "{$moduleSlug}_{$fieldKey}_list";
        }
      }
    }

    if (!empty($keys)) {
      DB::table('dropdown_lists')->whereIn('key', $keys)->update(['is_status' => true]);
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('dropdown_lists', function (Blueprint $table) {
      $table->dropColumn('is_status');
    });
  }
};
