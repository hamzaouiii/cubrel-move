<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
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

            $table->foreignUuid('quote_id')
              ->nullable()
              ->constrained()
              ->cascadeOnDelete();

            $table->string('number')->unique();
            $table->string('status')->default('draft'); // draft, sent, paid, overdue, cancelled
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();
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
        Schema::dropIfExists('invoices');
    }
};
