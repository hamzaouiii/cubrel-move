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
        Schema::create('ip_whitelists', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('ip', 45)->unique()->comment('IPv4/IPv6 in text form');
            $table->boolean('active')->default(true)->index();
            $table->string('label', 120)->nullable()->comment('Optional note, e.g., office gateway');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ip_whitelists');
    }
};
