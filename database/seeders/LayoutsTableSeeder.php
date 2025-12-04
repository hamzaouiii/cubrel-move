<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LayoutsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('layouts')->delete();
        
        \DB::table('layouts')->insert(array (
            0 => 
            array (
                'id' => '0bc2ae38-7711-4079-b7a9-18a7b1bb6eff',
                'module_id' => '0d4fc7e6-7463-4458-ab5a-6357ee4f2a2d',
                'type' => 'list',
                'module_name' => 'inquiries',
                'name' => 'inquiries Default List Layout',
                'definition' => '{"actions": [{"type": "show", "label": "Anzeigen"}, {"type": "edit", "label": "Bearbeiten"}], "columns": [{"key": "name", "label": "Name", "sortable": true}, {"key": "email", "label": "email", "format": "email", "sortable": true}, {"key": "message", "label": "message", "sortable": true}, {"key": "phone", "label": "phone", "format": "phone", "sortable": true}, {"key": "created_at", "label": "Created", "format": "datetime", "sortable": true}, {"key": "updated_at", "label": "Updated", "format": "datetime", "sortable": true}], "defaultSort": {"key": "created_at", "direction": "desc"}}',
                'is_record_default' => 0,
                'is_list_default' => 1,
                'created_at' => '2025-11-22 22:03:39',
                'updated_at' => '2025-11-22 22:03:39',
            ),
            1 => 
            array (
                'id' => '58bcb01a-a4cd-4fc4-bcad-c4d77c368855',
                'module_id' => '019aa1a5-7393-73bf-9a57-549ae2311228',
                'type' => 'record',
                'module_name' => 'leads',
                'name' => 'Record Layout',
                'definition' => '{"sections": [{"name": "Card", "layout": [{"key": "first_name", "label": "modules.leads.fields.first_name"}, {"key": "last_name", "label": "modules.leads.fields.last_name"}, {"key": "email", "label": "modules.leads.fields.email", "format": "email"}, {"key": "phone", "label": "modules.leads.fields.phone", "format": "phone"}, {"key": "company", "label": "modules.leads.fields.company"}, {"key": "description", "label": "modules.leads.fields.description", "format": "Textarea"}, {"key": "created_at", "label": "modules.leads.fields.created_at", "format": "datetime", "sortable": true}]}]}',
                'is_record_default' => 1,
                'is_list_default' => 0,
                'created_at' => '2025-12-01 16:47:12',
                'updated_at' => '2025-12-01 16:47:12',
            ),
            2 => 
            array (
                'id' => '6a060dd3-1f3e-4a05-912b-9cabd2b45c12',
                'module_id' => '019aa1a5-7393-73bf-9a57-549ae2311228',
                'type' => 'list',
                'module_name' => 'leads',
                'name' => 'Leads Default List Layout',
                'definition' => '{"actions": [{"type": "show", "label": "modules.leads.actions.show"}, {"type": "edit", "label": "modules.leads.actions.edit"}], "columns": [{"key": "first_name", "label": "modules.leads.fields.first_name", "sortable": true}, {"key": "last_name", "label": "modules.leads.fields.last_name", "sortable": true}, {"key": "email", "label": "modules.leads.fields.email", "sortable": true}, {"key": "phone", "label": "modules.leads.fields.phone", "sortable": false}, {"key": "company", "label": "modules.leads.fields.company", "sortable": true}, {"key": "created_at", "label": "modules.leads.fields.created_at", "format": "datetime", "sortable": true}], "defaultSort": {"key": "created_at", "direction": "desc"}}',
                'is_record_default' => 0,
                'is_list_default' => 1,
                'created_at' => '2025-12-01 16:02:32',
                'updated_at' => '2025-12-01 16:02:32',
            ),
            3 => 
            array (
                'id' => '73110fb5-8e16-436a-9591-f5827b90a45f',
                'module_id' => NULL,
                'type' => 'list',
                'module_name' => 'global',
                'name' => 'Global Default List Layout',
                'definition' => '{"actions": [], "columns": [{"key": "name", "label": "Name", "sortable": true}, {"key": "created_at", "label": "Erstellt am", "format": "datetime", "sortable": true}], "defaultSort": null}',
                'is_record_default' => 0,
                'is_list_default' => 1,
                'created_at' => '2025-11-20 17:20:19',
                'updated_at' => '2025-11-20 17:20:19',
            ),
            4 => 
            array (
                'id' => '87aabed3-d667-4502-a556-21565a49cbb0',
                'module_id' => NULL,
                'type' => 'record',
                'module_name' => 'global',
                'name' => 'Global Default Record Layout',
                'definition' => '{"sections": [{"name": "Card", "layout": [{"key": "name", "label": "Name", "sortable": true}, {"key": "created_at", "label": "Erstellt am", "format": "datetime", "sortable": true}]}]}',
                'is_record_default' => 1,
                'is_list_default' => 0,
                'created_at' => '2025-11-20 17:20:19',
                'updated_at' => '2025-11-20 17:20:19',
            ),
        ));
        
        
    }
}