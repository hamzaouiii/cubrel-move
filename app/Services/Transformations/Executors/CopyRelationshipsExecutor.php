<?php

namespace App\Services\Transformations\Executors;

use App\Models\Modules\LineItem;
use App\Services\Relationships\RelationshipService;
use App\Services\Transformations\Contracts\StepExecutorInterface;
use App\Services\Transformations\TransformationContext;
use App\Services\Transformations\TransformationException;

/**
 * Copies the relationships that the transformation offers AND that
 * the user actually checked in the overlay at run time.
 * Configured relationship keys are module slugs
 */
class CopyRelationshipsExecutor implements StepExecutorInterface
{
    public const LINE_ITEMS_KEY = 'line_items';

    public function execute(TransformationContext $context, array $configuration): void
    {
        if (! $context->targetRecord) {
            throw new TransformationException('copy_relationships step ran before the target record was created.');
        }

        $offered = $configuration['relationships'] ?? [];
        $selected = array_intersect($offered, $context->relationshipSelections);

        foreach ($selected as $key) {
            if ($key === self::LINE_ITEMS_KEY) {
                $this->copyLineItems($context);
            } else {
                $this->copyGenericRelationship($key, $context);
            }
        }
    }

    protected function copyLineItems(TransformationContext $context): void
    {
        $lineItems = LineItem::where('parent_type', $context->sourceModuleSlug)
            ->where('parent_id', $context->sourceRecord->id)
            ->orderBy('sort_order')
            ->get();

        foreach ($lineItems as $lineItem) {
            $clone = $lineItem->replicate(['parent_type', 'parent_id']);
            $clone->parent_type = $context->targetModuleSlug;
            $clone->parent_id = $context->targetRecord->id;
            $clone->calculateTotals()->save();
        }

        $context->summary['line_items_copied'] = $lineItems->count();
    }

    /**
     * @param string $relatedModuleSlug the related module's slug (e.g. "notes"), not a relationship name
     */
    protected function copyGenericRelationship(string $relatedModuleSlug, TransformationContext $context): void
    {
        $sourceSideRelationship = RelationshipService::getRelationshipBetween(
            $context->sourceModuleSlug,
            $relatedModuleSlug,
        )->first();

        $targetSideRelationship = RelationshipService::getRelationshipBetween(
            $context->targetModuleSlug,
            $relatedModuleSlug,
        )->first();

        if (! $sourceSideRelationship || ! $targetSideRelationship) {
            return;
        }

        $relatedRecords = RelationshipService::getRelatedRecords(
            $sourceSideRelationship->name,
            $context->sourceModuleSlug,
            $context->sourceRecord->id,
        );

        foreach ($relatedRecords as $related) {
            RelationshipService::link(
                $targetSideRelationship->name,
                $context->targetModuleSlug,
                $context->targetRecord->id,
                $related->id,
            );
        }

        $context->summary['relationships_copied'][$relatedModuleSlug] = $relatedRecords->count();
    }
}
