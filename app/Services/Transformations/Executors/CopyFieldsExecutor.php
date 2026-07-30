<?php

namespace App\Services\Transformations\Executors;

use App\Models\Module;
use App\Services\Transformations\Contracts\StepExecutorInterface;
use App\Services\Transformations\ExpressionEvaluator;
use App\Services\Transformations\TransformationContext;
use App\Services\Transformations\TransformationException;

/**
 * Applies configured source-field / static / expression mappings onto
 * the target record. Studio config is the only source of truth here,
 * there is no runtime override, see the plan's "no more overlay"
 * decision.
 *
 * Also does the target record's one and only save() — CreateRecordExecutor
 * intentionally leaves it unsaved so the record is only ever written to
 * the DB once, with the create_record fields and these mappings merged
 * together, instead of a "created" row immediately followed by an
 * "updated" one.
 */
class CopyFieldsExecutor implements StepExecutorInterface
{
    public function __construct(protected ExpressionEvaluator $evaluator)
    {
    }

    public function execute(TransformationContext $context, array $configuration): void
    {
        if (! $context->targetRecord) {
            throw new TransformationException('copy_fields step ran before the target record was created.');
        }

        foreach ($configuration['mappings'] ?? [] as $mapping) {
            $targetField = $mapping['target_field'] ?? null;

            if (! $targetField) {
                continue;
            }

            $context->targetRecord->{$targetField} = $this->resolveValue($mapping, $context);
            $context->summary['fields_copied']++;
        }

        $this->assertRequiredFieldsHaveValues($context);

        $context->targetRecord->save();
    }

    /**
     * Studio only guarantees required target fields are *mapped* to
     * something (TransformationsManagerController::assertRequiredTargetFieldsAreMapped);
     * it can't guarantee the mapped source field actually holds a value on
     * a given record. Without this, an empty source value silently reaches
     * save() and surfaces as a raw DB "not null" error instead of a
     * user-facing message.
     */
    protected function assertRequiredFieldsHaveValues(TransformationContext $context): void
    {
        $targetModule = Module::where('slug', $context->targetModuleSlug)->first();

        if (! $targetModule) {
            return;
        }

        $missing = $targetModule->allFields()
            ->filter(fn ($field) => $field->required && ! $field->readonly && ! $field->is_calculated)
            ->filter(function ($field) use ($context) {
                $value = $context->targetRecord->{$field->name} ?? null;

                return $value === null || $value === '';
            })
            ->map(fn ($field) => __($field->label));

        if ($missing->isNotEmpty()) {
            throw new TransformationException(__('globals.transformations.messages.required_field_empty_on_source', [
                'fields' => $missing->implode(', '),
            ]));
        }
    }

    protected function resolveValue(array $mapping, TransformationContext $context): mixed
    {
        return match ($mapping['mode'] ?? 'field') {
            'field' => $context->sourceRecord->{$mapping['source_field']} ?? null,
            'static' => $this->resolveStaticValue($mapping['value'] ?? null, $context),
            'expression' => $this->evaluator->evaluate($mapping['expression'] ?? [], $context),
            default => throw new TransformationException("Unknown field mapping mode: {$mapping['mode']}"),
        };
    }

    protected function resolveStaticValue(mixed $value, TransformationContext $context): mixed
    {
        return $value === '@current_user' ? $context->actor->id : $value;
    }
}
