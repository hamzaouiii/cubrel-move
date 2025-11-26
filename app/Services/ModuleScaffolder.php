<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\Filesystem;
use App\Models\Module; 

class ModuleScaffolder
{
    public function __construct(
        protected Filesystem $files
    ) {
    }

    public function scaffold(Module $module): void
    {
        $slug = $module->slug; 
        $baseName = Str::studly(Str::singular($slug)); 

        $modelClass = "App\\Models\\Modules\\Custom\\{$baseName}";
        $handlerClass = "App\\Handlers\\Modules\\Custom\\{$baseName}Handler";

        $module->update([
            'model_class' => $modelClass,
            'handler_class' => $handlerClass ?? null, 
            'table_name' => $module->table_name ?: Str::snake(Str::pluralStudly($slug))."_cstm",
        ]);

        $table = $module->table_name;

        $this->createModelFile($baseName, $table);
        $this->createHandlerFile($baseName, $modelClass);
        $this->createTable($table);
    }

    protected function createModelFile(string $baseName, string $table): void
    {
        $directory = app_path('Models/Modules/Custom');

        if (! $this->files->exists($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        $path = $directory . "/{$baseName}.php";

        if ($this->files->exists($path)) {
            return; 
        }

        $contents = <<<PHP
        <?php

        namespace App\Models\\Modules\\Custom;

        use Illuminate\\Database\\Eloquent\\Model;

        class {$baseName} extends Model
        {
            protected \$table = '{$table}';

            protected \$guarded = [];
        }

        PHP;

        $this->files->put($path, $contents);
    }

    protected function createHandlerFile(string $baseName, string $modelClass): void
    {
        $directory = app_path('Handlers/Modules/Custom');

        if (! $this->files->exists($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        $handlerName = "{$baseName}ModuleHandler";
        $path = $directory . "/{$handlerName}.php";

        if ($this->files->exists($path)) {
            return;
        }

        $contents = <<<PHP
        <?php

        namespace App\Handlers\Modules\Custom;

        use {$modelClass};
        use Illuminate\Database\Eloquent\Builder;
        use App\Handlers\Modules\BasePaginatedModuleHandler;

        class {$handlerName} extends BasePaginatedModuleHandler
        {
            protected string \$model = {$baseName}::class;

            protected function query(array \$params = []): Builder
            {
                \$query = {$baseName}::query();

                // apply filters if needed

                return \$query;
            }
        }

        PHP;

        $this->files->put($path, $contents);
    }


    protected function createTable(string $table): void
    {
        if (Schema::hasTable($table)) {
            return;
        }

        Schema::create($table, function (Blueprint $tableBlueprint) {
            $tableBlueprint->uuid('id')->primary();
            $tableBlueprint->string('name')->nullable();
            $tableBlueprint->text('description')->nullable();
            $tableBlueprint->json('data')->nullable();
            $tableBlueprint->timestamps();
            $tableBlueprint->softDeletes();
        });
    }
}
