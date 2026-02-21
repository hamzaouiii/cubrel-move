<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Module;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Services\Relationships\RelationshipService;

class RelationshipLinkController extends Controller
{
  public function getRecordsForLinking(
    Request $request,
    string $module,
    string $record_id,
    string $relationship
  ) {
    // 1. Resolve module
    $moduleModel = Module::where('slug', $module)->first();

    if (!$moduleModel) {
      throw new NotFoundHttpException("Module {$module} not found.");
    }

    // 2. Ensure record exists
    $modelClass = $moduleModel->model_class;

    $record = $modelClass::find($record_id);

    if (!$record) {
      throw new NotFoundHttpException("Record {$record_id} not found.");
    }

    // 3. Load relationship definition
    $relationshipObj = RelationshipService::get($relationship);

    // 4. Delegate to service
    $records = RelationshipService::getRecordsForLinking(
      $relationshipObj,
      $modelClass,
      $record_id
    );

    // 5. Return lightweight response
    return response()->json($records);
  }
}
