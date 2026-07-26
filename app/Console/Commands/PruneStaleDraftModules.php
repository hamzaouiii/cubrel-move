<?php

namespace App\Console\Commands;

use App\Models\Field;
use App\Models\Module;
use App\Services\ModuleScaffolder;
use App\Support\Settings;
use Illuminate\Console\Command;

class PruneStaleDraftModules extends Command
{
    protected $signature = 'modules:prune-stale-drafts';

    protected $description = 'Discards draft modules whose edit lock has expired and which have seen no activity for a while';

    public function handle(ModuleScaffolder $scaffolder): void
    {
        $cutoff = now()->subDays(Settings::get('retention_draft_modules_days', 7));

        $candidates = Module::where('is_draft', true)
            ->where(function ($query) {
                $query->whereNull('locked_until')->orWhere('locked_until', '<', now());
            })
            ->where('updated_at', '<=', $cutoff)
            ->get();

        $discarded = 0;

        foreach ($candidates as $module) {
            $latestFieldActivity = Field::where('module_id', $module->id)->max('updated_at');

            if ($latestFieldActivity && $latestFieldActivity > $cutoff) {
                continue;
            }

            $scaffolder->discardDraft($module);
            $discarded++;
        }

        $this->info("Discarded {$discarded} stale draft module(s).");
    }
}
