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
        Schema::create('quotes', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('number')->nullable();
            $table->string('status')->nullable()->default('draft');
            $table->date('valid_until')->nullable();
            $table->decimal('subtotal', 15, 2)->nullable();
            $table->decimal('discount_amount', 15, 2)->nullable();
            $table->decimal('tax_amount', 15, 2)->nullable();
            $table->decimal('total', 15, 2)->nullable();
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('quotes');
    }
};
