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

    /**
     * >0 while a run's step loop is executing, including nested runs
     * (an automatic transformation can itself trigger another one via
     * TransformationAutomationObserver). AuditObserver and
     * RelationshipService check this to skip the owner-facing
     * notifications that each step's saves/links would otherwise fire,
     * since a single conversion already gets its own
     * RecordConvertedNotification/TransformationTriggeredNotification
     * once its run completes — the per-step ones were just noise.
     * Audit log entries are unaffected, only notifications are skipped.
     * A counter (not a bool) so an inner run finishing doesn't
     * re-enable notifications while an outer run is still in progress.
     */
    protected static int $suppressionDepth = 0;

    public static function notificationsSuppressed(): bool
    {
        return self::$suppressionDepth > 0;
    }

    public function run(
        Transformation $transformation,
        BaseModule $sourceRecord,
        ?User $actor = null,
        bool $skipLinking = false,
    ): BaseModule {
        if (! $transformation->enabled) {
            throw new TransformationException("Transformation [{$transformation->name}] is disabled.");
        }

        return DB::transaction(function () use ($transformation, $sourceRecord, $actor, $skipLinking) {
            $context = new TransformationContext(
                transformation: $transformation,
                sourceRecord: $sourceRecord,
                sourceModuleSlug: $transformation->source_module,
                targetModuleSlug: $transformation->target_module,
                actor: $actor ?? auth()->user() ?? User::where('username', 'admin')->first() ?? User::firstOrFail(),
            );

            self::$suppressionDepth++;

            try {
                foreach ($transformation->steps as $step) {
                    if ($skipLinking && $step->type === 'link_records') {
                        continue;
                    }

                    $this->resolveExecutor($step->type)->execute($context, $step->configuration ?? []);
                }
            } finally {
                self::$suppressionDepth--;
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
