<?php

namespace App\Services\Transformations;

use App\Models\Module;
use Illuminate\Support\Str;

/**
 * Helpers fot mapping. 
 * @phpstan-type Segment array{type: 'text'|'field'|'helper', value: string}
 */
class ExpressionEvaluator
{
    /** @var array<string, callable> */
    protected array $helpers;

    public function __construct()
    {
        $this->helpers = [
            'today' => fn () => now()->toDateString(),
            'current_user' => fn (TransformationContext $context) => $context->actor->name,
            'uuid' => fn () => (string) Str::uuid(),
        ];
    }

    /**
     * @param array<int, array{type: string, value: string}> $segments
     * @throws InvalidExpressionException
     */
    public function evaluate(array $segments, TransformationContext $context): string
    {
        if (empty($segments)) {
            throw new InvalidExpressionException('Expression has no segments.');
        }

        $result = '';

        foreach ($segments as $segment) {
            $result .= $this->resolveSegment($segment, $context);
        }

        return $result;
    }

    protected function resolveSegment(array $segment, TransformationContext $context): string
    {
        return match ($segment['type'] ?? null) {
            'text' => (string) ($segment['value'] ?? ''),
            'field' => $this->resolveField($segment['value'] ?? '', $context),
            'helper' => $this->resolveHelper($segment['value'] ?? '', $context),
            default => throw new InvalidExpressionException(
                'Unknown expression segment type: '.($segment['type'] ?? 'null')
            ),
        };
    }

    protected function resolveField(string $field, TransformationContext $context): string
    {
        $module = Module::where('slug', $context->sourceModuleSlug)->firstOrFail();
        $knownFields = $module->allFields()->pluck('name');

        if (! $knownFields->contains($field) && ! in_array($field, ['name', 'description'], true)) {
            throw new InvalidExpressionException("Unknown source field '{$field}'.");
        }

        return (string) ($context->sourceRecord->{$field} ?? '');
    }

    protected function resolveHelper(string $name, TransformationContext $context): string
    {
        if (! isset($this->helpers[$name])) {
            throw new InvalidExpressionException("Unknown helper '{$name}'.");
        }

        return (string) ($this->helpers[$name])($context);
    }
}
