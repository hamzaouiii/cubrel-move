<?php

namespace Database\Seeders;

use App\Models\Dashboard;
use App\Models\User;
use App\Support\DashboardPresets;
use Illuminate\Database\Seeder;

class DashboardPresetSeeder extends Seeder
{
    public function run(): void
    {
        // Only fill in users who have no saved dashboard yet.
        // On a fresh migrate:fresh --seed the table is empty so every user gets
        // seeded.  On an incremental run, only new users are touched.
        User::each(function (User $user) {
            Dashboard::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'slug'       => 'dashboard_' . $user->id,
                    'name'       => 'My Dashboard',
                    'is_default' => true,
                    'layout'     => DashboardPresets::layout(DashboardPresets::presetType($user)),
                ]
            );
        });
    }
}
