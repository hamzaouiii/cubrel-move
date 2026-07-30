<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function getTables(): array
    {
        return [
            'leads',
            'accounts',
            'contacts',
            'deals',
            'quotes',
            'orders',
            'invoices',
            'line_items',
            'products',
            'cases',
            'tasks',
            'calls',
            'meetings',
            'notes',
            'emails',
        ];
    }

    public function up(): void
    {
        foreach ($this->getTables() as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'created_by')) {
                    $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn($tableName, 'updated_by')) {
                    $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->getTables() as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'created_by')) {
                    $table->dropConstrainedForeignId('created_by');
                }
                if (Schema::hasColumn($tableName, 'updated_by')) {
                    $table->dropConstrainedForeignId('updated_by');
                }
            });
        }
    }
};
