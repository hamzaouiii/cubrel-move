<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Field;

return new class extends Migration
{
    public function up(): void
    {
        Field::whereIn('name', ['to_addresses', 'cc_addresses'])
            ->where('type', 'json')
            ->update(['type' => 'multivalue']);
    }

    public function down(): void
    {
        Field::whereIn('name', ['to_addresses', 'cc_addresses'])
            ->where('type', 'multivalue')
            ->update(['type' => 'json']);
    }
};
