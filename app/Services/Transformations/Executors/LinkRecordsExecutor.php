<?php

namespace App\Services\Transformations\Executors;

use App\Services\Relationships\RelationshipService;
use App\Services\Transformations\Contracts\StepExecutorInterface;
use App\Services\Transformations\TransformationContext;
use App\Services\Transformations\TransformationException;

/**
 * Links the source record to the newly created target 
 */
class LinkRecordsExecutor implements StepExecutorInterface
{
    public function execute(TransformationContext $context, array $configuration): void
    {
        if (! $context->targetRecord) {
            throw new TransformationException('link_records step ran before the target record was created.');
        }

        $relationship = $context->transformation->relationship()->firstOrFail();

        RelationshipService::link(
            $relationship->name,
            $context->sourceModuleSlug,
            $context->sourceRecord->id,
            $context->targetRecord->id,
        );
    }
}
