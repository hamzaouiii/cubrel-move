<?php

namespace App\Observers;

use App\Models\BaseModule;
use App\Models\Transformation;
use App\Services\Notifications\NotificationService;
use App\Services\Transformations\TransformationEngine;
use Illuminate\Support\Facades\Auth;

/**
 * The V1 "minimal automation hook": runs transformations whenever their conditions become true on a save.
 * Deliberately narrow, this is a targeted observer, not a generic
 * trigger/condition/action automation engine
 *
 * Idempotency: on an update, only fires when at least one condition
 * field actually changed in this save. A create is always eligible
 * instead of going through that same wasChanged() check — Eloquent's
 * $model->changes is only ever populated by Model::performUpdate()'s
 * syncChanges() call, never by performInsert(), so wasChanged() is
 * unconditionally false right after a create regardless of what was
 * set — checking it there would mean a condition field filled in at
 * creation time could never trigger automation. passesConditions()
 * below still has to actually pass either way, so this doesn't loosen
 * what fires, only when the field-changed pre-filter applies.
 */

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

            $conditionFieldChanged = $model->wasRecentlyCreated
                || collect($conditions)->contains(fn (array $condition) => $model->wasChanged($condition['field'] ?? null));

            if (! $conditionFieldChanged) {
                continue;
            }

            if ($transformation->passesConditions($model)) {
                $target = app(TransformationEngine::class)->run($transformation, $model);

                NotificationService::notifyTransformationRun(
                    $transformation,
                    $model,
                    $target,
                    Auth::user(),
                    automatic: true,
                );
            }
        }
    }
}
