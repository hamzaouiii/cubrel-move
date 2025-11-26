<?php

namespace Database\Seeders;

use App\Models\Icon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class IconSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('.\..\storage\app\fa-icons-solid.json');

        if (! File::exists($path)) {
            $this->command->error("fa-icons.json not found at: {$path}");
            $this->command->warn('Make sure you placed fa-icons.json in database/data/');
            return;
        }

        $json = File::get($path);
        $icons = json_decode($json, true);

        if (! is_array($icons) || empty($icons)) {
            $this->command->error('fa-icons.json is empty or invalid JSON.');
            return;
        }

        $this->command->info('Truncating icons table...');
        Icon::truncate();

        $count = 0;

        foreach ($icons as $icon) {
            if (empty($icon['name']) || empty($icon['class'])) {
                continue;
            }

            Icon::create([
                'name'  => $icon['name'],
                'style' => $icon['style'] ?? 'solid',
                'class' => $icon['class'],
            ]);

            $count++;
        }

        $this->command->info("Seeded {$count} icons into the icons table.");
    }
}
