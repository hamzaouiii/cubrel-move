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
        Schema::create('emails', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('name');
            $table->longText('body')->nullable();
            $table->string('from_address')->nullable();
            $table->string('from_name')->nullable();
            $table->json('to_addresses')->nullable();
            $table->json('cc_addresses')->nullable();
            $table->timestamp('sent_at')->nullable();
            // Always 'logged' for now — BCC capture is one-directional. A
            // distinct value reserved for Level 2 (real outbound sending).
            $table->string('direction')->default('logged');
            // RFC 5322 Message-ID (or a content hash if a message omits
            // one), used to make relay retries idempotent.
            $table->string('provider_message_id')->nullable()->unique();
            // The slug of whichever address captured this email (a user's
            // username, or an admin-created App\Models\EmailCaptureAddress
            // slug) — lets a Transformation condition target a specific
            // mailbox, e.g. "mailbox equals leads" to auto-create a Lead.
            $table->string('mailbox')->nullable()->index();
            $table->foreignUuid('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->index('owner_id');
            $table->json('custom_fields')->default(DB::raw('(JSON_OBJECT())'));

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emails');
    }
};
