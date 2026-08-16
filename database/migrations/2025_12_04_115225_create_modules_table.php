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
        Schema::create('modules', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('label')->nullable();
            $table->string('single_label')->nullable();
            $table->string('module_category_id')->nullable();
            $table->string('icon')->default('fa-bahai');
            $table->string('color')->default('#0d6efd');
            $table->string('path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_relatable')->default(true);
            $table->boolean('is_activity')->default(false);
            $table->boolean('has_activity')->default(false);
            $table->boolean('show_in_module_manager')->default(true);
            $table->boolean('show_in_sidebar')->default(true);
            $table->string('handler_class')->nullable();
            $table->text('description')->nullable();
            $table->string('model_class')->nullable();
            $table->string('table_name')->nullable();
            $table->boolean('has_owner')->default(true);
            $table->boolean('is_custom')->default(false);
            $table->boolean('is_draft')->default(false);
            $table->uuid('locked_by')->nullable();
            $table->timestamp('locked_until')->nullable();
            $table->boolean('has_line_items')->default(false);
            $table->boolean('is_product_like')->default(false);
            $table->string('line_item_source_module')->nullable();
            $table->index('locked_until');
            $table->timestamps();
            $table->index('module_category_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
