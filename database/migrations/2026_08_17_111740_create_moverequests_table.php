<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moverequests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('objekttyp')->nullable();
            $table->integer('zimmeranzahl')->nullable();
            $table->decimal('wohnflaeche')->nullable();
            $table->integer('stockwerk')->nullable();
            $table->integer('etagenanzahl')->nullable();
            $table->boolean('aufzug_vorhanden')->nullable();
            $table->json('abholadresse')->nullable();
            $table->json('zieladresse')->nullable();
            $table->decimal('entfernung_km')->nullable();
            $table->decimal('geschaetztes_volumen_m3')->nullable();
            $table->decimal('geschaetzter_preis_von')->nullable();
            $table->decimal('geschaetzter_preis_bis')->nullable();
            $table->boolean('langer_tragweg')->nullable();
            $table->boolean('zerbrechliche_gegenstaende')->nullable();
            $table->boolean('demontage')->nullable();
            $table->boolean('montage')->nullable();
            $table->date('wunschtermin')->nullable();
            $table->string('quelle')->nullable();
            $table->decimal('angebotener_preis')->nullable();
            $table->text('ablehnungsgrund')->nullable();
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
        Schema::dropIfExists('moverequests');
    }
};
