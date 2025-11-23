<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Module;
use App\Models\Layout;

class SettingsLayoutSeeder extends Seeder
{
    public function run(): void
    {
        $module = Module::where('slug', 'settings')->first();

        if (! $module) {
            // Ensure the module seeder ran first.
            return;
        }

        // TODO: adjust columns to match your module fields.
        $definition = [
            'columns' => [
                ['key' => 'name', 'label' => 'Name', 'sortable' => true],
                ['key' => 'description', 'label' => 'description', 'sortable' => true],
                ['key' => 'category', 'label' => 'category', 'sortable' => true]
            ],
            'defaultSort' => [
                'key'       => 'created_at',
                'direction' => 'desc',
            ],
            'actions' => [
                ['type' => 'show', 'label' => 'Anzeigen'],
                ['type' => 'edit', 'label' => 'Bearbeiten'],
            ],
        ];

        Layout::updateOrCreate(
            [
                'module_id'       => $module->id,
                'type'            => 'list',
                'is_list_default' => true,
            ],
            [
                'id'                => Str::uuid(),
                'name'              => 'Settings Liste',
                'definition'        => $definition,
                'is_record_default' => false,
            ]
        );
    }
}
