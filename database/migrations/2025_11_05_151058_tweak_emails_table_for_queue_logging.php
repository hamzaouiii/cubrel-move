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
          if (!Schema::hasTable('emails')) {
            Schema::create('emails', function ($table) {
                $table->id();
                $table->string('to', 320)->nullable();                // allow null
                $table->string('subject')->nullable();
                $table->string('mailable_class', 191);
                $table->unsignedBigInteger('related_id')->nullable();
                $table->string('status', 32)->default('queued');       // queued|sent|failed
                $table->text('error')->nullable();                     // store failure reason
                $table->timestamps();
            });
            return;
          }
           // Existing table: relax constraints without requiring doctrine/dbal
        DB::statement('ALTER TABLE `emails` MODIFY `to` VARCHAR(320) NULL');
        // Make subject nullable (if not already)
        try { DB::statement('ALTER TABLE `emails` MODIFY `subject` VARCHAR(255) NULL'); } catch (\Throwable $e) {}
        // Ensure status column exists with default
        if (!Schema::hasColumn('emails', 'status')) {
            DB::statement("ALTER TABLE `emails` ADD `status` VARCHAR(32) NOT NULL DEFAULT 'queued' AFTER `related_id`");
        }
        // Add error column if missing
        if (!Schema::hasColumn('emails', 'error')) {
            DB::statement("ALTER TABLE `emails` ADD `error` TEXT NULL AFTER `status`");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
