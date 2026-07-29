<?php

namespace App\Services\Transformations\Executors;

use App\Models\Module;
use App\Services\Transformations\Contracts\StepExecutorInterface;
use App\Services\Transformations\TransformationContext;
use App\Services\Transformations\TransformationException;

/**
 * Instantiates the target record but deliberately does not save it —
 * the always-present copy_fields step (see
 * TransformationsManagerController::STEP_ORDER) fills in the mapped
 * fields on top of this and does the one save() for both, so the
 * record is only ever created once instead of a "created" followed by
 * an immediate "updated".
 */
class CreateRecordExecutor implements StepExecutorInterface
{
    public function execute(TransformationContext $context, array $configuration): void
    {
        $module = Module::where('slug', $context->targetModuleSlug)->first();

        if (! $module || empty($module->model_class)) {
            throw new TransformationException("Target module [{$context->targetModuleSlug}] has no model_class.");
        }

        $modelClass = $module->model_class;

        /** @var \App\Models\BaseModule $target */
        $target = new $modelClass();
        $target->name = $context->sourceRecord->name;
        $target->owner_id = $context->sourceRecord->owner_id ?? $context->actor->id;

        $context->targetRecord = $target;
    }
}
