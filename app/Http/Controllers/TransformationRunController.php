<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Transformation;
use App\Services\Notifications\NotificationService;
use App\Services\Transformations\TransformationEngine;
use Illuminate\Http\Request;


class TransformationRunController extends Controller
{
    /**
     * Lists every enabled Converssion available from a given
     * record's module, for the record page's "Convert" modal.
     */
    public function available(string $module, string $recordId)
    {

        $this->resolveRecord($module, $recordId);
        $moduleIcons = Module::pluck('icon', 'slug');

        $transformations = Transformation::where('source_module', $module)
            ->where('enabled', true)
            ->get()
            ->map(fn (Transformation $t) => [
                'id' => $t->id,
                'name' => $t->name,
                'icon' => $moduleIcons[$t->target_module] ?? null,
                'target_module' => $t->target_module,
            ])
            ->values();

        return response()->json($transformations);
    }

    /**
     * Checks whether running this transformation would silently replace
     * an existing one-to-one link 
     */
    public function preview(Transformation $transformation, string $recordId)
    {
        $record = $this->resolveRecord($transformation->source_module, $recordId);

        return response()->json([
            'existing_link' => $transformation->findConflictingLink($record),
        ]);
    }

    public function run(Request $request, Transformation $transformation, string $recordId)
    {
        $record = $this->resolveRecord($transformation->source_module, $recordId);

        if (! $transformation->enabled) {
            abort(404);
        }

        $skipLink = (bool) $request->boolean('skip_link');

        $target = app(TransformationEngine::class)->run(
            $transformation,
            $record,
            $request->user(),
            $skipLink,
        );

        NotificationService::notifyTransformationRun(
            $transformation,
            $record,
            $target,
            $request->user(),
            automatic: false,
        );

        return response()->json([
            'module' => $transformation->target_module,
            'record' => [
                'id' => $target->id,
                'name' => $target->name,
            ],
        ]);
    }

    protected function resolveRecord(string $moduleSlug, string $recordId): \App\Models\BaseModule
    {
        $module = Module::where('slug', $moduleSlug)->firstOrFail();

        return $module->model_class::findOrFail($recordId);
    }
}
