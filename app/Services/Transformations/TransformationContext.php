<?php

namespace App\Services\Transformations;

use App\Models\BaseModule;
use App\Models\Transformation;
use App\Models\User;

/**
 * Mutable value object threaded through every step of a transformation
 * run. Executors read from and write to this rather than passing a
 * growing argument list between them.
 */
class TransformationContext
{
    public ?BaseModule $targetRecord = null;

    /** @var array<string, mixed> field values the user edited in the overlay before submit; these win over configured mappings */
    public array $userOverrides = [];

    /** @var string[] relationship keys the user checked in the overlay ('line_items' sentinel + relationship names) */
    public array $relationshipSelections = [];

    /** @var array{fields_copied: int, line_items_copied: int, relationships_copied: array<string, int>} */
    public array $summary = [
        'fields_copied' => 0,
        'line_items_copied' => 0,
        'relationships_copied' => [],
    ];

    public function __construct(
        public readonly Transformation $transformation,
        public readonly BaseModule $sourceRecord,
        public readonly string $sourceModuleSlug,
        public readonly string $targetModuleSlug,
        public readonly User $actor,
    ) {
    }
}
