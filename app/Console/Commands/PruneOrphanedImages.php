<?php

namespace App\Console\Commands;

use App\Models\Field;
use App\Models\Module;
use App\Scopes\AdminOnlyModuleScope;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PruneOrphanedImages extends Command
{
    protected $signature = 'images:prune-orphans';

    protected $description = 'Deletes ad-hoc uploaded images no longer referenced by any user avatar, setting, or module custom field';

    public function handle(): void
    {
        $disk = Storage::disk('public');
        $files = collect($disk->files('uploads/images'));

        $referencedPaths = $this->referencedPaths();

        $cutoff = now()->subHours(24);
        $deleted = 0;

        foreach ($files as $file) {
            if ($referencedPaths->contains($file)) {
                continue;
            }

            $lastModified = $disk->lastModified($file);

            if ($lastModified && Carbon::createFromTimestamp($lastModified)->gt($cutoff)) {
                continue;
            }

            $disk->delete($file);
            $deleted++;
        }

        $this->info("Deleted {$deleted} orphaned image(s).");
    }

    /**
     * Every place an uploaded image's path could legitimately still be
     * referenced: user avatars, image-type settings, and image-type custom
     * fields across every module's table.
     */
    protected function referencedPaths()
    {
        $referenced = collect();

        $referenced = $referenced->merge(
            DB::table('users')->whereNotNull('avatar')->pluck('avatar')
        );

        $referenced = $referenced->merge(
            DB::table('setting_values')->where('type', 'image')->whereNotNull('value')->pluck('value')
        );

        $imageFields = Field::where('type', 'image')->get();
        $globalImageFields = $imageFields->where('is_global', true);
        $scopedImageFields = $imageFields->where('is_global', false)->groupBy('module_id');

        $modules = Module::withoutGlobalScope(AdminOnlyModuleScope::class)
            ->whereNotNull('table_name')
            ->get();

        foreach ($modules as $module) {
            if (! Schema::hasTable($module->table_name)) {
                continue;
            }

            $fieldsForModule = $scopedImageFields->get($module->id, collect())
                ->merge($globalImageFields);

            foreach ($fieldsForModule as $field) {
                if (! Schema::hasColumn($module->table_name, 'custom_fields')) {
                    continue;
                }

                $values = DB::table($module->table_name)
                    ->whereNotNull("custom_fields->{$field->name}")
                    ->pluck("custom_fields->{$field->name}");

                $referenced = $referenced->merge($values);
            }
        }

        return $referenced->filter()
            ->map(fn ($value) => ltrim(str_replace('/storage/', '', $value), '/'))
            ->unique()
            ->values();
    }
}
