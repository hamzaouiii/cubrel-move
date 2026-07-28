<?php

namespace App\Services\Transformations;

use App\Models\BaseModule;
use App\Models\Transformation;
use App\Models\User;
use App\Services\Transformations\Contracts\StepExecutorInterface;
use App\Services\Transformations\Executors\CopyFieldsExecutor;
use App\Services\Transformations\Executors\CopyRelationshipsExecutor;
use App\Services\Transformations\Executors\CreateRecordExecutor;
use App\Services\Transformations\Executors\LinkRecordsExecutor;
use Illuminate\Support\Facades\DB;

/**
 * Loads a Transformation's ordered steps and runs them against a
 * source record inside a single DB transaction, producing a target
 * record. All V1 step types are pure DB writes, so wrapping the whole
 * run in one transaction is safe: any step failing rolls back the
 * record creation, field copies, and line-item copies together.
 *
 * Non-transactional step types (PDF/email/webhook/delay) are explicitly
 * out of V1 scope; adding them later will require revisiting this
 * transaction boundary 
 * TODO: add non transactional steps for future Automations feature
 */
class TransformationEngine
{
    /** @var array<string, class-string<StepExecutorInterface>> */
    protected const EXECUTORS = [
        'create_record' => CreateRecordExecutor::class,
        'copy_fields' => CopyFieldsExecutor::class,
        'copy_relationships' => CopyRelationshipsExecutor::class,
        'link_records' => LinkRecordsExecutor::class,
    ];

    public function run(
        Transformation $transformation,
        BaseModule $sourceRecord,
        ?User $actor = null,
        array $userOverrides = [],
        array $relationshipSelections = [],
        bool $skipLinking = false,
    ): BaseModule {
        if (! $transformation->enabled) {
            throw new TransformationException("Transformation [{$transformation->name}] is disabled.");
        }

        return DB::transaction(function () use ($transformation, $sourceRecord, $actor, $userOverrides, $relationshipSelections, $skipLinking) {
            $context = new TransformationContext(
                transformation: $transformation,
                sourceRecord: $sourceRecord,
                sourceModuleSlug: $transformation->source_module,
                targetModuleSlug: $transformation->target_module,
                actor: $actor ?? auth()->user() ?? User::where('username', 'admin')->first() ?? User::firstOrFail(),
            );
            $context->userOverrides = $userOverrides;
            $context->relationshipSelections = $relationshipSelections;

            foreach ($transformation->steps as $step) {
                if ($skipLinking && $step->type === 'link_records') {
                    continue;
                }

                $this->resolveExecutor($step->type)->execute($context, $step->configuration ?? []);
            }

            if (! $context->targetRecord) {
                throw new TransformationException(
                    "Transformation [{$transformation->name}] completed without producing a target record."
                );
            }

            return $context->targetRecord;
        });
    }

    protected function resolveExecutor(string $type): StepExecutorInterface
    {
        if (! isset(self::EXECUTORS[$type])) {
            throw new TransformationException("Unsupported step type [{$type}] — executor not implemented yet.");
        }

        return app(self::EXECUTORS[$type]);
    }
}
