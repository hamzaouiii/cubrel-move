<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LayoutsSeeder extends Seeder
{
    public function run(): void
    {
        // ---------------------------------------------------------
        // GLOBAL DEFAULT LIST LAYOUT
        // ---------------------------------------------------------
        DB::table('layouts')->insert([
            'id'               => Str::uuid(),
            'module_id'        => null,
            'module_name'      => 'global',
            'type'             => 'list',
            'name'             => 'Global Default List Layout',
            'definition'       => json_encode([
                'actions' => [],
                'columns' => [ 0 => [
                      'key' => 'name',
                      'label' => 'Name',
                      'sortable' => true
                    ],
                    1=> [
                      'key' => 'created_at',
                      'label' => 'Erstellt am',
                      'format' => 'datetime',
                      'sortable' => true
                    ]],
                'defaultSort' => null,
            ]),
            'is_list_default'  => true,
            'is_record_default'=> false,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        // ---------------------------------------------------------
        // GLOBAL DEFAULT RECORD LAYOUT
        // ---------------------------------------------------------
        DB::table('layouts')->insert([
            'id'               => Str::uuid(),
            'module_id'        => null,
            'module_name'      => 'global',
            'type'             => 'record',
            'name'             => 'Global Default Record Layout',
            'definition'       => json_encode([
                'sections' => [
                  0=> [ 'name' => 'Card',
                  'layout' => [
                    0 => [
                      'key' => 'name',
                      'label' => 'Name',
                      'sortable' => true
                    ],
                    1=> [
                      'key' => 'created_at',
                      'label' => 'Erstellt am',
                      'format' => 'datetime',
                      'sortable' => true
                    ]
                  ]
                  ]
                ],
            ]),
            'is_list_default'  => false,
            'is_record_default'=> true,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        // ---------------------------------------------------------
        // LEADS MODULE DEFAULT LIST LAYOUT
        // ---------------------------------------------------------



    }
}
