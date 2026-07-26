<?php

namespace App\Jobs;

use App\Models\Field;
use App\Models\Import;
use App\Models\Module;
use App\Services\Import\ImportFileReader;
use App\Services\Import\ImportValueCoercer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ProcessImportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries;

    public int $timeout;

    public function __construct(private readonly int $importId)
    {
        $this->tries = config('import.tries');
        $this->timeout = config('import.timeout');
    }

    public function handle(ImportFileReader $reader, ImportValueCoercer $coercer): void
    {
        $import = Import::find($this->importId);

        if (! $import) {
            return;
        }

        try {
            $this->process($import, $reader, $coercer);
        } catch (Throwable $e) {
            $import->update([
                'status' => config('import.statuses.failed'),
                'failed_reason' => Str::limit($e->getMessage(), 500),
                'completed_at' => now(),
            ]);

            if ($import->file_path) {
                Storage::disk('local')->delete($import->file_path);
            }

            report($e);
        }
    }

    private function process(Import $import, ImportFileReader $reader, ImportValueCoercer $coercer): void
    {
        $import->update(['status' => config('import.statuses.processing'), 'started_at' => now()]);

        $moduleModel = Module::query()->where('slug', $import->module_slug)->where('is_active', true)->first();

        if (! $moduleModel) {
            throw new RuntimeException("Module '{$import->module_slug}' is no longer available.");
        }

        $modelClass = $moduleModel->model_class;

        if (! $modelClass || ! class_exists($modelClass)) {
            throw new RuntimeException("No model class configured for module '{$import->module_slug}'.");
        }

        $fields = $moduleModel->allFields()->keyBy('name');
        $mapping = $import->column_mapping ?? [];
        $matchField = $import->match_field;
        $matchFieldDef = $matchField ? $fields->get($matchField) : null;

        $absolutePath = Storage::disk('local')->path($import->file_path);

        $progress = [
            'processed_rows' => 0,
            'created_count' => 0,
            'updated_count' => 0,
            'skipped_count' => 0,
            'errors' => [],
            'errors_truncated' => false,
        ];

        foreach ($reader->rows($absolutePath, $import->format, $import->delimiter) as [$rowNumber, $row]) {
            $progress['processed_rows']++;

            [$attributes, $rowError] = $this->buildAttributes($row, $mapping, $fields, $coercer);

            if ($rowError === null) {
                try {
                    $wasUpdate = $this->upsert($modelClass, $matchField, $matchFieldDef, $attributes, $moduleModel->has_owner ? $import->user_id : null);
                    $wasUpdate ? $progress['updated_count']++ : $progress['created_count']++;
                } catch (Throwable $e) {
                    $rowError = $e->getMessage();
                }
            }

            if ($rowError !== null) {
                $progress['skipped_count']++;
                $this->recordError($progress, $rowNumber, $rowError);
            }

            if ($progress['processed_rows'] % 50 === 0) {
                $import->update($progress);
            }
        }

        $import->update($progress);

        $import->update([
            'status' => config('import.statuses.completed'),
            'completed_at' => now(),
        ]);

        Storage::disk('local')->delete($import->file_path);
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, string>  $mapping  source column => target field name
     * @param  Collection<string, Field>  $fields
     * @return array{0: array<string, mixed>, 1: ?string}
     */
    private function buildAttributes(array $row, array $mapping, Collection $fields, ImportValueCoercer $coercer): array
    {
        $attributes = [];

        foreach ($mapping as $sourceColumn => $fieldName) {
            $field = $fields->get($fieldName);

            if (! $field) {
                continue;
            }

            ['value' => $value, 'error' => $error] = $coercer->coerce($field, $row[$sourceColumn] ?? null);

            if ($error !== null) {
                return [[], $error];
            }

            if ($field->required && ($value === null || $value === '')) {
                return [[], "The field '{$field->label}' is required."];
            }

            $attributes[$fieldName] = $value;
        }

        return [$attributes, null];
    }


    private function upsert(string $modelClass, ?string $matchField, ?Field $matchFieldDef, array $attributes, ?string $ownerId): bool
    {
        if ($matchField && $matchFieldDef) {
            $value = $attributes[$matchField] ?? null;

            if ($value !== null && $value !== '') {
                $column = $matchFieldDef->is_custom ? "custom_fields->{$matchField}" : $matchField;
                $existing = $modelClass::where($column, $value)->first();

                if ($existing) {
                    $existing->fill($attributes)->save();

                    return true;
                }
            }
        }

        if ($ownerId !== null) {
            $attributes['owner_id'] = $ownerId;
        }

        $modelClass::create($attributes);

        return false;
    }

    private function recordError(array &$progress, int $rowNumber, string $reason): void
    {
        if (count($progress['errors']) < config('import.max_stored_errors')) {
            $progress['errors'][] = ['row' => $rowNumber, 'reason' => $reason];
        } else {
            $progress['errors_truncated'] = true;
        }
    }
}
