<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UpdateLeadsRecordLayout extends Seeder
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
                    ],
                ],
            ],
        ];
        DB::table('layouts')->updateOrInsert(
            [
                'id' => '58bcb01a-a4cd-4fc4-bcad-c4d77c368855',
            ],
            [
                'definition' => json_encode($definition, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
                'updated_at' => now(),
            ]
        );
    }
}
