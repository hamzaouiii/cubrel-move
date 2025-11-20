<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->uuid('id')->primary();   
            $table->string('name');
            $table->string('to');
            $table->boolean('sent')->default(false);
            $table->string('subject')->nullable();
            $table->text('mailable_class');
            $table->unsignedBigInteger('related_id')->nullable(); // e.g. contact_message_id
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emails');
    }
};
