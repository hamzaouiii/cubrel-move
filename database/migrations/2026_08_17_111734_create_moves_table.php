<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moves', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->date('umzugstermin')->nullable();
            $table->json('abholadresse')->nullable();
            $table->json('zieladresse')->nullable();
            $table->decimal('entfernung_km')->nullable();
            $table->integer('anzahl_umzugshelfer')->nullable();
            $table->decimal('endgueltiges_volumen_m3')->nullable();
            $table->decimal('endpreis')->nullable();
            $table->string('zahlungsstatus')->nullable();
            $table->boolean('langer_tragweg')->nullable();
            $table->boolean('zerbrechliche_gegenstaende')->nullable();
            $table->boolean('demontage')->nullable();
            $table->boolean('montage')->nullable();
            $table->longText('notizen')->nullable();
            $table->string('status')->nullable();
            $table->json('custom_fields')->nullable();
            $table->foreignUuid('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->index('owner_id');
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moves');
    }
};
