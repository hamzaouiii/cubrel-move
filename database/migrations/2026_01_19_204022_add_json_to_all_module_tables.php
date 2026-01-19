<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Module;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function getTables()
  {
    return DB::table('modules')
      ->where('is_active', 1)
      ->whereNotNull('table_name')
      ->pluck('table_name');
  }

  public function up(): void
  {
    foreach ($this->getTables() as $tableName) {
      if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'json')) {
        Schema::table($tableName, function (Blueprint $table) {
          $table->json('json')->nullable();
        });
      }
    }
  }

  public function down(): void
  {
    foreach ($this->getTables() as $tableName) {
      if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'json')) {
        Schema::table($tableName, function (Blueprint $table) {
          $table->dropColumn('json');
        });
      }
    }
  }
};
