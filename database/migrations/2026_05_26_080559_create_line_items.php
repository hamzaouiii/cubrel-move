<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('line_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('parent_id');
            $table->string('parent_type');
            $table->index(['parent_type', 'parent_id'], 'line_items_parent_index');

            $table->uuid('product_id')->nullable();
            $table->index('product_id');

            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('unit_price', 15, 4)->nullable();
            $table->decimal('quantity', 15, 4)->default(1);
            $table->decimal('discount', 5, 2)->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0);

            $table->decimal('subtotal', 15, 4)->nullable();
            $table->decimal('discount_amount', 15, 4)->nullable();
            $table->decimal('tax_amount', 15, 4)->nullable();
            $table->decimal('total', 15, 4)->nullable();

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->foreignUuid('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->index('owner_id');
            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('line_items');
    }
};
