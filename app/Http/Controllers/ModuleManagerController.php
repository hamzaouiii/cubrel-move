<?php


namespace App\Http\Controllers;

use App\Models\Module;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Settings\SettingItem;
use App\Services\ModuleScaffolder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Filesystem\Filesystem;


class ModuleManagerController extends Controller
{
  public function index(Request $request)
  {
    $modules = Module::query()
      ->with([
        'layouts' => function ($q) {
          $q->orderBy('type')->orderBy('name');
        },
      ])
      ->orderBy('id')
      ->get();
    $item = SettingItem::where('path', 'like', '%' . $request->path())->first();

    return Inertia::render('Settings/Modules/List', [
      'item'     => $item,
      'setting_modules' => $modules
    ]);
  }

  public function show(Request $request)
  {
    $id = last($request->segments());
    $module = Module::where('id', $id)->firstOrFail();
    return Inertia::render('Settings/Modules/Record', [
      'settingModule' => $module
    ]);
  }


  public function update(Request $request, $id)
  {
    $module = Module::where('id', $id)->firstOrFail();


    // this won't run if validation fails
    $data = $request->except('_token', '_method', 'label');
    $module->fill($data)->save();

    return redirect()->to('/settings/modules');
  }

  public function create()
  {

    return Inertia::render('Settings/Modules/Create');
  }

  public function edit(Module $module)
  {
    return Inertia::render('Settings/Modules/Edit', [
      'settingModule' => $module,
    ]);
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'display_label'        => ['required', 'string', 'max:255'],
      'icon'        => ['nullable', 'string', 'max:255'],
      'color'       => ['nullable', 'string', 'max:255'],
      'description' => ['nullable', 'string'],
      'slug'        => ['required', 'string', 'max:255', 'alpha_dash', 'unique:modules,slug'],
      'show_in_sidebar' => ['boolean'],
    ]);

    $DEFAULT_ICON          = 'fa-solid fa-bahai';
    $DEFAULT_COLOR         = '#000000';
    $DEFAULT_SORT_ORDER    = (Module::max('sort_order') ?? 0) + 1;
    $DEFAULT_IS_ACTIVE     = true;
    $DEFAULT_PERMISSION    = true;
    $DEFAULT_SHOW_SIDEBAR  = true;

    $baseName = Str::studly($validated['slug']);
    $handler_class = "App\\Handlers\\Modules\\Custom\\" . $baseName . "ModuleHandler";
    $model_class = "App\\Models\\Modules\\Custom\\" . $baseName;

    $module = Module::create([
      'slug'        => $validated['slug'],
      'name'        => $validated['display_label'],
      'icon'        => $validated['icon'] ?? $DEFAULT_ICON,
      'color'       => $validated['color'] ?: $DEFAULT_COLOR,
      'label'       => 'custom/modules/' . $validated['slug'] . '.label',
      'handler_class' => $handler_class,
      'path'        => '/' . $validated['slug'],
      'sort_order'  => $DEFAULT_SORT_ORDER,
      'is_active'   => $DEFAULT_IS_ACTIVE,
      'description' => $validated['description'] ?? '',
      'can_view'    => $DEFAULT_PERMISSION,
      'can_create'  => $DEFAULT_PERMISSION,
      'can_edit'    => $DEFAULT_PERMISSION,
      'can_delete'  => $DEFAULT_PERMISSION,
      'model_class' =>  $model_class,
      'table_name'  => Str::snake($validated['slug']) . "_cstm",
      'show_in_sidebar' => $request->boolean('show_in_sidebar', $DEFAULT_SHOW_SIDEBAR),
      'is_custom' => 1
    ]);


    app(ModuleScaffolder::class)->scaffold($module, $validated['display_label']);
    return redirect()
      ->route('settings.modules.show', $module->id)
      ->with('success', __('settings.module_save_success'));
  }

  protected function createLangFiles(string $slug, string $label): void
  {
    $files = app(Filesystem::class); // Instantiate Filesystem here
    $langPath = lang_path(); // Use helper function instead of base_path('lang')

    if (!$files->exists($langPath)) {
      Log::warning("Language directory not found: {$langPath}");
      return;
    }

    $locales = array_filter(scandir($langPath), function ($item) use ($langPath) {
      return $item !== '.'
        && $item !== '..'
        && is_dir($langPath . '/' . $item);
    });

    foreach ($locales as $locale) {
      $directory = $langPath . "/{$locale}/custom/modules";

      if (!$files->exists($directory)) {
        $files->makeDirectory($directory, 0755, true);
      }

      $path = $directory . "/{$slug}.php";

      if ($files->exists($path)) {
        continue;
      }

      $contents = <<<PHP
<?php
return [
    'label' => '{$label}',
];
PHP;

      $files->put($path, $contents);
    }
  }



  protected function createModelFile(string $baseName, string $table): void
  {
    $directory = app_path('Models/Modules/Custom');
    $files = app(Filesystem::class); // Instantiate Filesystem here
    if (! $files->exists($directory)) {
      $files->makeDirectory($directory, 0755, true);
    }

    $path = $directory . "/{$baseName}.php";

    if ($files->exists($path)) {
      return;
    }

    $contents = <<<PHP
        <?php

        namespace App\Models\\Modules\\Custom;

        use App\\Models\\BaseModule;

        class {$baseName} extends BaseModule
        {
            protected \$table = '{$table}';

            protected \$guarded = [];
        }

        PHP;

    $files->put($path, $contents);
  }
}
