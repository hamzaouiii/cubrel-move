<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();   
            $table->string('name');
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('shipping_address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
