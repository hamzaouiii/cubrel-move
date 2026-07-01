<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name');
            $table->string('order_number')->nullable();

            $table->uuid('deal_id')->nullable();
            $table->text('description')->nullable();

            $table->decimal('subtotal', 15, 2)->nullable();
            $table->decimal('discount_amount', 15, 2)->nullable();
            $table->decimal('tax_amount', 15, 2)->nullable();
            $table->decimal('total', 15, 2)->nullable();

            $table->string('status')->nullable()->index();
            // draft, confirmed, shipped, completed, cancelled

            $table->date('order_date')->nullable();
            $table->date('due_date')->nullable();

            $table->json('custom_fields')->default(DB::raw('(JSON_OBJECT())'));
            $table->foreignUuid('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->index('owner_id');

            $table->timestamps();

            $table->index(['status', 'order_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
