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


        // Look up the leads module
        $leadsModule = DB::table('modules')
            ->where('slug', 'leads')
            ->first();

        if ($leadsModule) {

            DB::table('layouts')->insert([
                'id'               => Str::uuid(),
                'module_id'        => $leadsModule->id,
                'module_name'      => 'leads',
                'type'             => 'list',
                'name'             => 'Leads Default List Layout',
                'definition'       => "{\"actions\": [{\"type\": \"show\", \"label\": \"Anzeigen\"}, {\"type\": \"edit\", \"label\": \"Bearbeiten\"}], \"columns\": [{\"key\": \"first_name\", \"label\": \"Vorname\", \"sortable\": true}, {\"key\": \"last_name\", \"label\": \"Nachname\", \"sortable\": true}, {\"key\": \"email\", \"label\": \"E-Mail\", \"sortable\": true}, {\"key\": \"phone\", \"label\": \"Telefon\", \"sortable\": false}, {\"key\": \"company\", \"label\": \"Firma\", \"sortable\": true}, {\"key\": \"created_at\", \"label\": \"Erstellt am\", \"format\": \"datetime\", \"sortable\": true}], \"defaultSort\": {\"key\": \"created_at\", \"direction\": \"desc\"}}",
                'is_list_default'  => true,
                'is_record_default'=> false,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            DB::table('layouts')->insert([
            'id'               => Str::uuid(),
            'module_id'        => $leadsModule->id,
            'module_name'      => 'leads',
            'type'             => 'record',
            'name'             => 'Record Layout',
            'definition'       => json_encode([
                'sections' => [
                  0=> [ 'name' => 'Card',
                  'layout' => [
                    0 => [
                      'key' => 'first_name',
                      'label' => 'First Name',
                    ],
                    1=>[
                      'key' => 'last_name',
                      'label' => 'Last Name',
                    ], 
                    2=>[
                      'key' => 'email',
                      'label' => 'Email',
                      'format' => 'email',
                    ], 
                    3=>[
                      'key' => 'phone',
                      'label' => 'Phone',
                      'format' => 'phone',
                    ], 
                    4=>[
                      'key' => 'company',
                      'label' => 'Company'
                    ], 
                    5 =>[
                      'key' => 'description',
                      'label' => 'Description',
                      'format' => 'Textarea',
                    ], 
                    6=> 
                   [
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

        }
    }
}
