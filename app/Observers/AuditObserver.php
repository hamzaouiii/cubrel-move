<?php

namespace App\Observers;

use App\Models\BaseModule;
use App\Models\Module;
use App\Scopes\AdminOnlyModuleScope;
use App\Services\Audit\AuditService;

class AuditObserver
{
    public function created(BaseModule $model): void
    {
        $module = $this->resolveModule($model);
        if (! $module) {
            return;
        }

        AuditService::log('created', $module->slug, $model->id, null);
    }

    public function updated(BaseModule $model): void
    {
        $module = $this->resolveModule($model);
        if (! $module) {
            return;
        }
        $calculatedFields = $module->allFields()
            ->where('is_calculated', true)
            ->pluck('name')
            ->all();

        $changes = collect($model->getChanges())
            ->except(array_merge(['updated_at'], $calculatedFields))
            ->mapWithKeys(fn ($new, $key) => [
                $key => $this->buildFieldChange($module, $key, $model->getOriginal($key), $new),
            ])
            ->all();

        if (empty($changes)) {
            return;
        }

        AuditService::log('updated', $module->slug, $model->id, $changes);
    }

    /**
     * For 'record' type fields (e.g. owner_id -> users), old/new hold the
     * related record's id — meaningless to a viewer on its own. Resolve
     * and store the related record's display label alongside the id, same
     * "snapshot now, don't rely on it still existing later" reasoning as
     * deleted()'s record_label.
     */
    private function buildFieldChange(Module $module, string $key, $old, $new): array
    {
        $diff = ['old' => $old, 'new' => $new];

        $field = $module->allFields()->firstWhere('name', $key);

        if ($field && $field->type === 'record' && $field->related_module) {
            $diff['old_label'] = $this->resolveRecordLabel($field->related_module, $old);
            $diff['new_label'] = $this->resolveRecordLabel($field->related_module, $new);
        }

        return $diff;
    }

    /**
     * Null-safe Module lookup, bypassing AdminOnlyModuleScope for the same
     * reason as BaseModule::getModuleSlug()/moduleDefinition() (see
     * docs/audit-trail-implementation.md §5.1) — but unlike those two,
     * deliberately returns null instead of throwing when no Module row
     * exists for this class at all (not an admin-scope issue, a genuinely
     * missing registration — e.g. a fresh install before modules are
     * seeded, or a test that creates a User without registering one).
     * Audit logging must never crash the save it's observing.
     */
    private function resolveModule(BaseModule $model): ?Module
    {
        return Module::withoutGlobalScope(AdminOnlyModuleScope::class)
            ->where('model_class', get_class($model))
            ->first();
    }

    private function resolveRecordLabel(string $relatedModuleSlug, ?string $id): ?string
    {
        if (! $id) {
            return null;
        }

        $module = Module::withoutGlobalScope(AdminOnlyModuleScope::class)
            ->where('slug', $relatedModuleSlug)
            ->first();

        if (! $module || ! $module->model_class || ! class_exists($module->model_class)) {
            return null;
        }

        $record = $module->model_class::find($id);

        return $record ? ($record->name ?? $record->number ?? $id) : null;
    }

    public function deleted(BaseModule $model): void
    {
        $module = $this->resolveModule($model);
        if (! $module) {
            return;
        }

        // Once a record is gone, module_slug + record_id alone can't identify
        // it again — capture its display label now, matching the same
        // name ?? number ?? id fallback used elsewhere (e.g. Record.vue).
        AuditService::log('deleted', $module->slug, $model->id, [
            'record_label' => $model->name ?? $model->number ?? $model->id,
        ]);
    }
}
