<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Inertia\Inertia;
use Illuminate\Support\Str;
use App\Contracts\ModuleHandler;
use App\Support\Settings;
use App\Exceptions\ModuleHandlerNotFoundException;

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
      throw new ModuleHandlerNotFoundException(
        "Handler class [{$handlerClass}] not found for module [{$module}]. Please check if the file exists or re-deploy."
      );
    }

    $props = [];

    if (class_exists($handlerClass)) {
      $handler = app($handlerClass);

      if ($handler instanceof ModuleHandler) {
        $params = request()->all();
        $params['perPage'] = $params['perPage'] ?? Settings::get('list_view_limit');

        $props = $handler->getListData($moduleModel, $params);
      }
    }

    $listLayout = $moduleModel->listLayout();
    $recorddropdownLists = $moduleModel->dropdownLists;
    $fields = $moduleModel->allFields();

    //TODO: make the fields list customizable
    // remove readonly fields and fields unsuited for mass update such as emails and so on.
    return Inertia::render('Modules/List', array_merge([
      'module'     => $moduleModel,
      'title'      => $moduleModel->name,
      'listLayout' => $listLayout,
      'fields'     => $fields,
      'filters'    => request()->only(['search', 'perPage']),
      'dropdownLists' => $recorddropdownLists,

    ], $props));
  }
}
