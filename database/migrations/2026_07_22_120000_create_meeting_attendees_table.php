<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_attendees', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('meeting_id')->constrained('meetings')->cascadeOnDelete();

            $table->string('source_type')->nullable();
            $table->uuid('source_id')->nullable();

            $table->string('name');
            $table->string('email')->nullable();

            $table->string('role')->default('required');
            $table->string('rsvp_status')->default('invited');
            $table->string('attendance_status')->nullable();
            $table->timestamp('responded_at')->nullable();

            $table->foreignUuid('owner_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_attendees');
    }
};
