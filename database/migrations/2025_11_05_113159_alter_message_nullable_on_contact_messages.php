<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // If you have doctrine/dbal installed, you can use change():
        // Schema::table('contact_messages', function (Blueprint $table) {
        //     $table->text('message')->nullable()->change();
        // });

        // Works without doctrine/dbal (MySQL):
        DB::statement('ALTER TABLE contact_messages MODIFY message TEXT NULL');

        // (Optional hardening to match your validation)
        // DB::statement('ALTER TABLE contact_messages MODIFY phone VARCHAR(50) NULL');
        // DB::statement("ALTER TABLE contact_messages MODIFY user_agent VARCHAR(1024) NULL");
        // DB::statement("ALTER TABLE contact_messages MODIFY status VARCHAR(20) NOT NULL DEFAULT 'new'");
    }

    public function down(): void
    {
        // Revert to NOT NULL (adjust type to what you had originally if different)
        DB::statement('ALTER TABLE contact_messages MODIFY message TEXT NOT NULL');

        // Optionally revert others if you changed them above:
        // DB::statement('ALTER TABLE contact_messages MODIFY phone VARCHAR(50) NOT NULL');
        // DB::statement('ALTER TABLE contact_messages MODIFY user_agent VARCHAR(255) NOT NULL');
        // DB::statement("ALTER TABLE contact_messages MODIFY status VARCHAR(20) NOT NULL");
    }
};
