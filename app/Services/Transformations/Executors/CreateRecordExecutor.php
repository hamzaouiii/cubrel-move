<?php

namespace App\Services\Transformations\Executors;

use App\Models\Module;
use App\Services\Transformations\Contracts\StepExecutorInterface;
use App\Services\Transformations\TransformationContext;
use App\Services\Transformations\TransformationException;

/**
 * Creates the target record. */
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
        $target->save();

        $context->targetRecord = $target;
    }
}
