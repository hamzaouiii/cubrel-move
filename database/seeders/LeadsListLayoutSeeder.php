<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LeadsListLayoutSeeder extends Seeder
{
    public function run(): void
    {
        $moduleSlug = 'leads';

        // Get the module id by slug (adjust table/column if different)
        $moduleId = DB::table('modules')
            ->where('slug', $moduleSlug)
            ->value('id');

        if (! $moduleId) {
            // Optional: bail out if module does not exist
            throw new \RuntimeException("Module with slug '{$moduleSlug}' not found.");
        }

        // Base layout definition (your JSON)
        $definition = [
            'actions' => [
                ['type' => 'show', 'label' => 'Anzeigen'],
                ['type' => 'edit', 'label' => 'Bearbeiten'],
            ],
            'columns' => [
                ['key' => 'first_name', 'label' => 'Vorname',     'sortable' => true],
                ['key' => 'last_name',  'label' => 'Nachname',    'sortable' => true],
                ['key' => 'email',      'label' => 'E-Mail',      'sortable' => true],
                ['key' => 'phone',      'label' => 'Telefon',     'sortable' => false],
                ['key' => 'company',    'label' => 'Firma',       'sortable' => true],
                ['key' => 'created_at', 'label' => 'Erstellt am', 'format' => 'datetime', 'sortable' => true],
            ],
            'defaultSort' => [
                'key'       => 'created_at',
                'direction' => 'desc',
            ],
        ];

        // Replace action labels with translation keys: module.leads.actions.show|edit
        foreach ($definition['actions'] as &$action) {
            $action['label'] = "modules.{$moduleSlug}.actions.{$action['type']}";
        }
        unset($action);

        // Replace column labels with translation keys: module.leads.fields.first_name etc.
        foreach ($definition['columns'] as &$column) {
            $column['label'] = "modules.{$moduleSlug}.fields.{$column['key']}";
        }
        unset($column);

        DB::table('layouts')->insert([
            'id'               => (string) Str::uuid(),
            'module_id'        => $moduleId,
            'type'             => 'list',                         // enum('list','record','form')
            'module_name'      => $moduleSlug,
            'name'             => 'Leads Default List Layout',    // human readable
            'definition'       => json_encode($definition, JSON_UNESCAPED_UNICODE),
            'is_record_default'=> 0,
            'is_list_default'  => 1,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }
}
