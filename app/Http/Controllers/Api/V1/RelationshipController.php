<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\RecordResource;
use App\Models\Relationship;
use App\Services\Api\RecordApiService;
use App\Services\Relationships\RelationshipService;
use Illuminate\Http\Request;
use RuntimeException;

class RelationshipController extends Controller
{
    public function __construct(protected RecordApiService $records) {}

    // GET /api/v1/leads/relationships
    public function index(string $module)
    {
        $this->records->authorizeAbility($module, 'read');
        $this->records->resolveModule($module);

        $excluded = config('api.excluded_modules', []);

        $relationships = RelationshipService::getRelationshipForModule($module)
            ->reject(fn (Relationship $r) => in_array($r->related_slug, $excluded, true))
            ->map(fn (Relationship $r) => [
                'name' => $r->name,
                'type' => $r->type,
                'role' => $r->role,
                'related_module' => $r->related_slug,
            ])
            ->values()
            ->all();

        return response()->json(['data' => $relationships]);
    }

    // POST /api/v1/leads/{id}/relationships/leads_tasks  { "related_ids": ["019f..."] }
    public function link(Request $request, string $module, string $recordId, string $relationshipName)
    {
        $this->records->authorizeAbility($module, 'link');
        $moduleModel = $this->records->resolveModule($module);
        $this->records->find($moduleModel, $recordId);

        $relationship = $this->resolveRelationship($relationshipName, $module);
        $relatedModule = $this->resolveRelatedModule($relationship->related_slug);

        $validated = $request->validate([
            'related_ids' => ['required', 'array', 'min:1'],
            'related_ids.*' => ['uuid', "exists:{$relatedModule->table_name},id"],
        ]);

        // Each link() call is its own transaction (RelationshipService), so a
        // failure partway through leaves earlier ids in this list linked -
        // same partial-success behavior the web app's own linkRecords() has.
        try {
            foreach ($validated['related_ids'] as $relatedId) {
                RelationshipService::link($relationshipName, $module, $recordId, $relatedId);
            }
        } catch (RuntimeException $e) {
            abort(422, __('api.errors.relationship_conflict'));
        }

        $relatedModelClass = $relatedModule->model_class;
        $linked = $relatedModelClass::findMany($validated['related_ids']);

        return response()->json([
            'data' => $linked
                ->map(fn ($record) => (new RecordResource($record, $relatedModule->slug))->toArray(request()))
                ->values()
                ->all(),
        ], 201);
    }

    // DELETE /api/v1/leads/{id}/relationships/leads_tasks/{relatedId}
    public function unlink(string $module, string $recordId, string $relationshipName, string $relatedId)
    {
        $this->records->authorizeAbility($module, 'link');
        $moduleModel = $this->records->resolveModule($module);
        $this->records->find($moduleModel, $recordId);

        $relationship = $this->resolveRelationship($relationshipName, $module);
        $this->resolveRelatedModule($relationship->related_slug);

        RelationshipService::unlink($relationshipName, $module, $recordId, $relatedId);

        return response()->json(null, 204);
    }

   
    protected function resolveRelationship(string $name, string $module): Relationship
    {
        try {
            $relationship = RelationshipService::get($name);

            return RelationshipService::getWithSide($relationship, $module);
        } catch (RuntimeException $e) {
            abort(404, __('api.errors.not_found'));
        }
    }


    protected function resolveRelatedModule(string $slug)
    {
        if (in_array($slug, config('api.excluded_modules', []), true)) {
            abort(404, __('api.errors.not_found'));
        }

        return $this->records->resolveModule($slug);
    }
}
