<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Inertia\Inertia;
use Illuminate\Support\Str;
use App\Contracts\ModuleHandler;

class ListController extends Controller
{
  public function __invoke(string $module)
  {
    $moduleModel = Module::query()
      ->where('slug', $module)
      ->where('is_active', true)
      ->firstOrFail();

    $handlerClass = $moduleModel->handler_class
      ?? "App\\Handlers\\Modules\\" . Str::studly($moduleModel->slug) . "ModuleHandler";

    if (empty($handlerClass)) {
      dd("No Handler Class found for module {$moduleModel->slug}");
    }

    $props = [];

    if (class_exists($handlerClass)) {
      $handler = app($handlerClass);

      if ($handler instanceof ModuleHandler) {
        $params = request()->all();
        $params['perPage'] = $params['perPage'] ?? request()->query('perPage', 18);

        $props = $handler->getListData($params);
      }
    }

    $listLayout = $moduleModel->listLayout();
    $fields = $moduleModel->fields()->get();
    //TODO: make the fields list customizable
    // remove readonly fields and fields unsuited for mass update such as emails and so on.
    return Inertia::render('Modules/List', array_merge([
      'module'     => $moduleModel,
      'title'      => $moduleModel->name,
      'listLayout' => $listLayout,
      'fields'     => $fields,
      'filters'    => request()->only(['search', 'perPage']),
    ], $props));
  }
}
