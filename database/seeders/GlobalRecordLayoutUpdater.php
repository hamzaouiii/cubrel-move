<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GlobalRecordLayoutUpdater extends Seeder{

    public function run(): void
    {
        $module = 'global';

        $value = '{"sections": [{"name": "Card", "layout": [{"key": "name", "label": "modules.defaults.name", "sortable": true}, {"key": "created_at", "label": "modules.defaults.created_at", "format": "datetime", "sortable": true}, {"key": "updated_at", "label": "modules.defaults.updated_at", "format": "datetime", "sortable": true}]}]}';

        DB::table('layouts')
            ->where('module_name', $module)
            ->where('type', 'record')
            ->update([
                'definition' => $value,
                'updated_at' => now(),
            ]);
    }
}
