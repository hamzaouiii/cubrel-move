<?php

namespace App\Console\Commands;

use App\Models\Field;
use App\Models\Label;
use App\Models\Layout;
use App\Models\Module;
use App\Models\Relationship;
use App\Scopes\AdminOnlyModuleScope;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Inverse of modules:import — tears down a module and everything that
 * command (or the in-app module builder) creates for it: the module row,
 * its fields, labels, layouts and relationships (relationship_links cascade
 * off the relationships FK automatically), the generated model/handler
 * files (custom modules only — core files are hand-written and committed,
 * so those are left alone), the generated migration file (core modules
 * only — custom modules never get one), and finally the underlying DB
 * table. Dev-only tool — not meant to run against production data.
 */
class DeleteModule extends Command
{
    use ConfirmableTrait;

    protected $signature = 'modules:delete {slug : The slug of the module to delete}
        {--keep-table : Leave the underlying database table in place}
        {--force : Allow running outside local/testing without a confirmation prompt}';

    protected $description = 'Dev tool: deletes a module along with its fields, labels, layouts, relationships, generated files and table';

    public function handle(): int
    {
        if (! $this->confirmToProceed('This is a dev-only teardown tool and is not meant to run against production data.')) {
            return self::FAILURE;
        }

        $slug = $this->argument('slug');

        $module = Module::withoutGlobalScope(AdminOnlyModuleScope::class)->where('slug', $slug)->first();

        if (! $module) {
            $this->error("Module not found: {$slug}");

            return self::FAILURE;
        }

        $fieldCount = Field::where('module_id', $module->id)->count();
        $layoutCount = Layout::where('module_id', $module->id)->count();
        $relationships = Relationship::where('left_module', $slug)->orWhere('right_module', $slug)->get();
        $dropTable = ! $this->option('keep-table') && $module->table_name && Schema::hasTable($module->table_name);

        $this->warn("About to delete module [{$slug}]:");
        $this->line("  - {$fieldCount} field(s) and their labels");
        $this->line("  - {$layoutCount} layout(s)");
        $this->line("  - {$relationships->count()} relationship(s) (and their links)");
        $this->line('  - generated model/handler files'.($module->is_custom ? '' : ' (skipped — core module, hand-written)'));
        $this->line($dropTable
            ? "  - table [{$module->table_name}], including all its data".($module->is_custom ? '' : ' (and its migration file)')
            : '  - table left in place (--keep-table or none scaffolded)');

        if (! $this->option('force') && ! $this->confirm("Really delete module [{$slug}]? This cannot be undone.")) {
            return self::FAILURE;
        }

        DB::transaction(function () use ($module, $relationships) {
            foreach ($relationships as $relationship) {
                $relationship->cleanupRelationshipPanels();
                $relationship->delete();
            }

            Layout::where('module_id', $module->id)->delete();
            Label::where('module_id', $module->id)->delete();
            Field::where('module_id', $module->id)->delete();

            $module->delete();
        });

        $this->info("Deleted module [{$slug}] row, fields, labels, layouts and relationships.");

        if ($module->is_custom) {
            $this->deleteGeneratedFiles($module);
        }

        if ($dropTable) {
            if (! $module->is_custom) {
                $this->deleteMigrationFile($module);
            }

            Schema::dropIfExists($module->table_name);
            $this->info("Dropped table [{$module->table_name}].");
        }

        $this->info('Done.');

        return self::SUCCESS;
    }

    protected function deleteGeneratedFiles(Module $module): void
    {
        foreach ([$module->model_class, $module->handler_class] as $fqcn) {
            if (! $fqcn) {
                continue;
            }

            $path = $this->classFilePath($fqcn);

            if (File::exists($path)) {
                File::delete($path);
                $this->info("Deleted file [{$path}].");
            }
        }
    }

    /**
     * Inverse of ImportModuleFromJson::createTableViaMigration() — core
     * modules get their table via a generated migration file instead of a
     * direct Schema::create(), so deleting one has to roll that migration
     * back (drops the table and clears its migrations-table row) before
     * removing the file itself.
     */
    protected function deleteMigrationFile(Module $module): void
    {
        $files = File::glob(database_path("migrations/*_create_{$module->table_name}_table.php"));

        foreach ($files as $file) {
            Artisan::call('migrate:rollback', [
                '--path' => 'database/migrations/'.basename($file),
                '--force' => true,
            ]);

            File::delete($file);
            $this->info("Deleted migration [{$file}].");
        }
    }

    /**
     * Maps a fully qualified class name onto its expected file path under
     * app/, e.g. "App\Models\Modules\Custom\Move" -> app/Models/Modules/Custom/Move.php.
     */
    protected function classFilePath(string $fqcn): string
    {
        $relative = Str::after($fqcn, 'App\\');

        return app_path(str_replace('\\', '/', $relative).'.php');
    }
}
