<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Module;
use App\Services\Relationships\RelationshipService;
use App\Support\Settings;

class RelationshipLinkController extends Controller
{
  public function getRecordsForLinking(Request $request, string $module, string $record_id, string $relationship)
  {
    $relationshipObj = RelationshipService::get($relationship);
    $limit = Settings::get('linking_panel_limit');
    return RelationshipService::getRecordsForLinking(
      $relationshipObj,
      $module,
      $record_id,
      $limit,
      $request->get('search')
    );
  }
  public function getRecordsForUpdateSingleLinking(Request $request, string $module, string $record_id, string $relationship)
  {
    $relationshipObj = RelationshipService::get($relationship);
    $limit = Settings::get('linking_panel_limit');

    return RelationshipService::getRecordsForUpdateSingleLinking($relationshipObj, $module, $record_id, $limit, $request->get('search', $request->get('q')));
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

  public function unlink(Request $request, string $module, string $record_id, string $relationship, string $related_id)
  {
    $moduleModel = Module::where('slug', $module)->firstOrFail();
    $modelClass = $moduleModel->model_class;

    $record = $modelClass::findOrFail($record_id);

    $record->unlinkRelation($relationship, $related_id);
  }


  public function loadRecords(Request $request, $module, $record_id, $relationshipName)
  {
    $page = (int) $request->get('page', 1);

    $perPage = (int) Settings::get('related_panel_limit');

    $offset = ($page - 1) * $perPage;

    $query = RelationshipService::loadRelatedRecords($module, $record_id, $relationshipName);

    $total = $query->count();

    $records = $query
      ->skip($offset)
      ->take($perPage)
      ->get();

    return response()->json([
      'data' => $records,
      'page' => $page,
      'per_page' => $perPage,
      'total' => $total,
      'has_more' => ($offset + $perPage) < $total,
    ]);
  }
}
