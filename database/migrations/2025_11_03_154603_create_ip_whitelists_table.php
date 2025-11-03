<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ip_whitelists', function (Blueprint $table) {
            $table->id();
            // fits IPv4 and IPv6
            $table->string('ip', 45)->unique()->comment('IPv4/IPv6 in text form');
            $table->boolean('active')->default(true)->index();
            $table->string('label', 120)->nullable()->comment('Optional note, e.g., office gateway');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_whitelists');
    }
};
