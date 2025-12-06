<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GlobalLayoutUpdater extends Seeder
{
    public function run(): void
    {
        $module = 'global';

        $value = '{"actions": [], "columns": [{"key": "name", "label": "modules.defaults.name", "sortable": true}, {"key": "created_at", "label": "modules.defaults.created_at", "format": "datetime", "sortable": true}, {"key": "updated_at", "label": "modules.defaults.updated_at", "format": "datetime", "sortable": true}], "defaultSort": null}';

        DB::table('layouts')
            ->where('module_name', $module)
            ->where('type', 'list')
            ->update([
                'definition' => $value,
                'updated_at' => now(),
            ]);
    }
}
