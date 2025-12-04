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
        Schema::create('setting_values', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('setting_item_id', 36);
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('label')->nullable();
            $table->string('type', 50)->default('string');
            $table->boolean('autoload')->default(true);
            $table->timestamps();

            $table->unique(['setting_item_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setting_values');
    }
};
