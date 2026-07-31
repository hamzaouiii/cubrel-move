<?php

namespace App\Console\Commands;

use Database\Seeders\DefaultFiltersSeeder;
use Database\Seeders\ModulesTableSeeder;
use Database\Seeders\RelationshipDropdownSeeder;
use Database\Seeders\RelationshipSeeder;
use Database\Seeders\SettingValuesSeeder;
use Database\Seeders\StockFieldsSeeder;
use Database\Seeders\TransformationSeeder;
use Database\Seeders\dropdownListSeeder;
use Illuminate\Console\Command;

class SyncDefaults extends Command
{
    protected $signature = 'cubrel:sync-defaults';

    protected $description = 'Inserts any module/field/dropdown/filter/setting/relationship/transformation defined in config but missing from this tenant\'s database. Insert-only — never overwrites or deletes an existing row, so live customizations made through the UI are untouched. Safe to run on every deploy.';

    public function handle(): int
    {
        $seeders = [
            ModulesTableSeeder::class,
            dropdownListSeeder::class,
            StockFieldsSeeder::class,
            DefaultFiltersSeeder::class,
            SettingValuesSeeder::class,
            RelationshipDropdownSeeder::class,
            RelationshipSeeder::class,
            TransformationSeeder::class,
        ];

        foreach ($seeders as $seeder) {
            $this->call('db:seed', [
                '--class' => $seeder,
                '--force' => true,
            ]);
        }

        $this->info('Defaults synced.');

        return self::SUCCESS;
    }
}
