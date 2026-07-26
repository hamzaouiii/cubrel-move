<?php

namespace App\Services;

use App\Models\BaseModule;
use App\Models\Module;
use Illuminate\Support\Facades\Storage;

/**
 * Deletes the disk file(s) backing a record's image-type field(s) — called
 * when a record is deleted (nothing left to reference the file) or when a
 * field's value is replaced (the old file is no longer referenced by
 * anything). Image field values are stored as host-relative URLs, e.g.
 * '/storage/uploads/images/xyz.png' (see ImageUploadController::store()).
 */
class ImageCleanupService
{
    /**
     * Deletes every image-type field's current value for a single record —
     * used right before the record itself is deleted.
     */
    public static function cleanupAllForRecord(Module $module, BaseModule $record): void
    {
        foreach (self::imageFields($module) as $field) {
            self::deletePath($record->{$field->name} ?? null);
        }
    }

    /**
     * Deletes the old file for any image-type field whose value changed —
     * used right after a record update succeeds. Compares old vs new
     * directly rather than relying on Eloquent's dirty-tracking, since a
     * custom field change only ever shows up as one opaque 'custom_fields'
     * dirty key, not decomposed per field name (see HasCustomFields).
     *
     * getOriginal($fieldName) only ever finds a real DB column — a custom
     * field's original value only lives inside getOriginal('custom_fields'),
     * which the model's own 'array' cast already decodes for us. Checking
     * both covers real-column and custom_fields-backed image fields alike
     * without needing a (currently unreliable — not even selected by
     * Module::allFields()) is_custom flag to pick a branch.
     */
    public static function cleanupReplacedFields(Module $module, BaseModule $model): void
    {
        $originalCustomFields = $model->getOriginal('custom_fields') ?? [];

        foreach (self::imageFields($module) as $field) {
            $old = $originalCustomFields[$field->name] ?? $model->getOriginal($field->name);
            $new = $model->{$field->name} ?? null;

            if ($old && $old !== $new) {
                self::deletePath($old);
            }
        }
    }

    protected static function imageFields(Module $module)
    {
        return $module->allFields()->where('type', 'image');
    }

    protected static function deletePath(?string $value): void
    {
        if (! $value) {
            return;
        }

        $path = ltrim(str_replace('/storage/', '', $value), '/');

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
