<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InsertLeadsRecordLayoutSeeder extends Seeder
{
    public function run(): void
    {
        $module   = 'leads';

        // Find leads module id (adjust table/column names if needed)
        $leadsModuleId = DB::table('modules')
            ->where('slug', $module)
            ->value('id');

        if (! $leadsModuleId) {
            throw new \RuntimeException("Module with slug '{$module}' not found.");
        }

        $definition = [
            'sections' => [
                [
                    'name' => 'Card',
                    'layout' => [
                        [
                            'key'   => 'first_name',
                            'label' => "modules.$module.fields.first_name",
                        ],
                        [
                            'key'   => 'last_name',
                            'label' => "modules.$module.fields.last_name",
                        ],
                        [
                            'key'    => 'email',
                            'label'  => "modules.$module.fields.email",
                            'format' => 'email',
                        ],
                        [
                            'key'    => 'phone',
                            'label'  => "modules.$module.fields.phone",
                            'format' => 'phone',
                        ],
                        [
                            'key'   => 'company',
                            'label' => "modules.$module.fields.company",
                        ],
                        [
                            'key'    => 'description',
                            'label'  => "modules.$module.fields.description",
                            'format' => 'Textarea',
                        ],
                        [
                            'key'      => 'created_at',
                            'label'    => "modules.$module.fields.created_at",
                            'format'   => 'datetime',
                            'sortable' => true,
                        ],
                    ],
                ],
            ],
        ];


        DB::table('layouts')->insert([
            'id'               => (string) Str::uuid(),
            'module_id'        => $leadsModuleId,
            'module_name'      => $module,
            'type'             => 'record',
            'name'             => 'Record Layout',
            'definition'       => json_encode($definition, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            'is_list_default'  => false,
            'is_record_default'=> true,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }
}
