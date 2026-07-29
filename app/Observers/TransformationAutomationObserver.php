<?php

namespace App\Observers;

use App\Models\BaseModule;
use App\Models\Transformation;
use App\Services\Transformations\TransformationEngine;

/**
 * The V1 "minimal automation hook": runs transformations whenever their conditions become true on a save.
 * Deliberately narrow, this is a targeted observer, not a generic
 * trigger/condition/action automation engine 
 *
 * Idempotency: only fires when at least one condition field actually
 * changed in this save */

class TransformationAutomationObserver
{
    public function saved(BaseModule $model): void
    {
        $moduleSlug = $model::getModuleSlug();

        $transformations = Transformation::where('source_module', $moduleSlug)
            ->where('enabled', true)
            ->where('automation_enabled', true)
            ->get();

        foreach ($transformations as $transformation) {
            $conditions = $transformation->conditions ?? [];

            $conditionFieldChanged = collect($conditions)
                ->contains(fn (array $condition) => $model->wasChanged($condition['field'] ?? null));

            if (! $conditionFieldChanged) {
                continue;
            }

            if ($transformation->passesConditions($model)) {
                app(TransformationEngine::class)->run($transformation, $model);
            }
        }
    }
}
