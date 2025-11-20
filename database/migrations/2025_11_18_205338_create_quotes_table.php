<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->uuid('id')->primary();   
            $table->string('name');

            $table->foreignUuid('account_id')
              ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignUuid('contact_id')
              ->nullable()
            ->constrained()
            ->cascadeOnDelete();

            $table->string('number')->unique();
            $table->string('status')->default('draft'); // draft, sent, accepted, rejected
            $table->date('valid_until')->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
