<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Module;
use App\Services\Relationships\RelationshipService;

class RelationshipLinkController extends Controller
{
  public function getRecordsForLinking(Request $request, string $module, string $record_id, string $relationship)
  {
    $moduleModel = Module::where('slug', $module)->firstOrFail();

    $modelClass = $moduleModel->model_class;

    $relationshipObj = RelationshipService::get($relationship);

    return RelationshipService::getRecordsForLinking(
      $relationshipObj,
      $modelClass,
      $record_id,
      $request->get('per_page', 25),
      $request->get('search')
    );
  }
  public function linkRecords(Request $request, string $module, string $record_id, string $relationship)
  {

    $request->validate([
      'related_ids' => ['required', 'array'],
      'related_ids.*' => ['uuid'],
    ]);
    $moduleModel = Module::where('slug', $module)->firstOrFail();
    $modelClass = $moduleModel->model_class;

    $record = $modelClass::findOrFail($record_id);

    foreach ($request->input('related_ids') as $id) {
      $record->link($relationship, $id);
    }

    return response()->json([
      'success' => true,
    ]);
  }
}
