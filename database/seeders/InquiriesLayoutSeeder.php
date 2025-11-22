<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Module;
use App\Models\Layout;

class InquiriesLayoutSeeder extends Seeder
{
    public function run(): void
    {
        $module = Module::where('slug', 'inquiries')->first();

        if (! $module) {
            // Ensure the module seeder ran first.
            return;
        }

        // // TODO: adjust columns to match your module fields.
        $definition = [
            'columns' => [
                ['key' => 'name', 'label' => 'Name', 'sortable' => true],
                ['key' => 'email', 'label' => 'email', 'sortable' => true, 'format' => 'email'],
                ['key' => 'message', 'label' => 'message', 'sortable' => true],
                ['key' => 'phone', 'label' => 'phone', 'sortable' => true, 'format' => 'phone'],
                ['key' => 'created_at', 'label' => 'Created', 'sortable' => true, 'format' => 'datetime'],
                ['key' => 'updated_at', 'label' => 'Updated', 'sortable' => true, 'format' => 'datetime'],
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
                'id'               => Str::uuid(),
                'module_id'        => $module->id,
                'module_name'      => 'inquiries',
                'type'             => 'list',
                'name'             => 'inquiries Default List Layout',
                'definition'       => $definition,
                'is_list_default'  => true,
                'is_record_default'=> false,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]
        );



    }
}
