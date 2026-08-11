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
        Schema::table('userinvites', function (Blueprint $table) {
            if (! Schema::hasColumn('userinvites', 'created_by')) {
                $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('userinvites', 'updated_by')) {
                $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('userinvites', function (Blueprint $table) {
            if (Schema::hasColumn('userinvites', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
            if (Schema::hasColumn('userinvites', 'updated_by')) {
                $table->dropConstrainedForeignId('updated_by');
            }
        });
    }
};
