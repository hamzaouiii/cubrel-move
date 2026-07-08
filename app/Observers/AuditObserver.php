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
        AuditService::log('deleted', $module->slug, $model->id, [
            'record_label' => $model->name ?? $model->number ?? $model->id,
        ]);
    }
}
