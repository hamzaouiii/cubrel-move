<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Module;

class SettingsModuleSeeder extends Seeder
{
    public function run(): void
    {
        // Adjust fields here to match your actual modules table structure.
        // Assumes: id (uuid), name, slug, table_name, model_class, handler_class, is_custom, is_active
        Module::updateOrCreate(
            ['slug' => 'settings'],
            [
                'id'            => Str::uuid(),
                'name'          => 'Settings',
                'label'          => 'Settings',
                'color'          => '#1f2420',
                'icon'          => 'fa-gears',
                'path'          => '/setting',
                'description'          => 'Settings',
                'slug'          => 'settings',
                'table_name'    => 'settings',
                'model_class'   => 'App\Models\Modules\Settings',
                'handler_class' => 'App\Handlers\Modules\SettingsModuleHandler',
                'is_active'     => true,
            ]
        );
    }
}
