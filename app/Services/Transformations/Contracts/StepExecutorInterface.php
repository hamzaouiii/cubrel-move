<?php

namespace App\Services\Transformations\Contracts;

use App\Services\Transformations\TransformationContext;

interface StepExecutorInterface
{
    /**
     * Runs this step's effect against the given context, mutating it
     * @param array<string, mixed> $configuration this step row's `configuration` JSON
     */
    public function execute(TransformationContext $context, array $configuration): void;
}
