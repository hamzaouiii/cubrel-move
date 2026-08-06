<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ModuleRecordRequest;
use App\Http\Resources\Api\V1\RecordResource;
use App\Models\Module;
use App\Services\Api\RecordApiService;
use Illuminate\Http\Request;

class RecordController extends Controller
{
    public function __construct(protected RecordApiService $records) {}

    // GET /api/v1/leads?search=acme&sort=name&direction=asc&per_page=25
    public function index(Request $request, string $module)
    {
        $this->records->authorizeAbility($module, 'read');
        $moduleModel = $this->records->resolveModule($module);

        $params = [
            'search' => $request->query('search'),
            'sort' => $request->query('sort'),
            'direction' => $request->query('direction', 'asc'),
            'filter' => $request->query('filter'),
            'perPage' => (int) $request->query('per_page', 25),
        ];

        $listData = $this->records->list($moduleModel, $params);

        return response()->json(RecordResource::collectionFromListData($listData, $module));
    }

    // GET /api/v1/leads/{id}
    public function show(string $module, string $id)
    {
        $this->records->authorizeAbility($module, 'read');
        $moduleModel = $this->records->resolveModule($module);

        $record = $this->records->find($moduleModel, $id);

        return response()->json(['data' => $this->presentRecord($record, $moduleModel, $module, $id)]);
    }

    // POST /api/v1/leads  { "name": "Jane Doe", "email": "jane@example.com" }
    public function store(ModuleRecordRequest $request, string $module)
    {
        // authorizeAbility() already ran in ModuleRecordRequest::authorize(), before rules() built anything.
        $moduleModel = $this->records->resolveModule($module);

        $record = $this->records->create($moduleModel, $request->validated());

        return response()->json(['data' => $this->presentRecord($record, $moduleModel, $module, $record->id)], 201);
    }

    // PUT /api/v1/leads/{id}  { "name": "Jane Doe (updated)" } - partial patch, omitted fields keep their value
    public function update(ModuleRecordRequest $request, string $module, string $id)
    {
        $moduleModel = $this->records->resolveModule($module);

        $record = $this->records->update($moduleModel, $id, $request->validated());

        return response()->json(['data' => $this->presentRecord($record, $moduleModel, $module, $id)]);
    }

    // DELETE /api/v1/leads/{id}
    public function destroy(string $module, string $id)
    {
        $this->records->authorizeAbility($module, 'delete');
        $moduleModel = $this->records->resolveModule($module);

        $this->records->delete($moduleModel, $id);

        return response()->json(null, 204);
    }

    /**
     * Embeds line_items/attendees/related on single-record responses only,
     * skipped on index() to avoid extra queries per row on a list page.
     */
    protected function presentRecord($record, Module $moduleModel, string $module, string $recordId): array
    {
        $data = (new RecordResource($record, $module))->toArray(request());

        if ($moduleModel->has_line_items) {
            $data['line_items'] = $this->records->lineItemsFor($moduleModel, $recordId);
        }

        if ($module === 'meetings') {
            $data['attendees'] = $this->records->attendeesFor($recordId);
        }

        $data['related'] = $this->records->relatedRecordsFor($module, $recordId);

        return $data;
    }
}
