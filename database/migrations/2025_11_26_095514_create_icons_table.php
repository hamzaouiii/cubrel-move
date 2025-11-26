<?php

namespace Database\Seeders;

use App\Models\Icon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class IconSeeder extends Seeder
{
    public function run(): void
    {
        $path = 'fa-icons-solid.json'; // storage/app/fa-icons-solid.json

        if (! Storage::disk('local')->exists($path)) {
            $this->command->error("{$path} not found in storage/app.");
            return;
        }

        $json  = Storage::disk('local')->get($path);
        $icons = json_decode($json, true);

        if (! is_array($icons) || empty($icons)) {
            $this->command->error("{$path} is empty or invalid JSON.");
            return;
        }

        $this->command->info('Truncating icons table...');
        Icon::truncate();

        $count = 0;

        foreach ($icons as $icon) {
            Icon::create([
                'name'  => $icon['name'],
                'style' => 'solid',
                'class' => $icon['class'],
            ]);
            $count++;
        }

        $this->command->info("Seeded {$count} solid icons into the icons table.");
    }
}
