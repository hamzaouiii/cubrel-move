<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Module;

class InquiriesModuleSeeder extends Seeder
{
    public function run(): void
    {
        // Adjust fields here to match your actual modules table structure.
        // Assumes: id (uuid), name, slug, table_name, model_class, handler_class, is_custom, is_active
        Module::updateOrCreate(
            ['slug' => 'inquiries'],
            [
                'id'            => Str::uuid(),
                'name'          => 'Inquiries',
                'label'          => 'Inquiries',
                'color'          => '#cceb34',
                'icon'          => 'fa-envelopes-bulk',
                'path'          => '/ar-admin/inquiries',
                'description'          => 'Manage messages from the contact form',
                'slug'          => 'inquiries',
                'table_name'    => 'contact_messages',
                'model_class'   => 'App\Models\Modules\ContactMessage',
                'handler_class' => 'App\Handlers\Modules\InquiriesModuleHandler',
                'is_active'     => true,
            ]
        );
    }
}