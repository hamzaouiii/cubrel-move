<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessImportJob;
use App\Models\Field;
use App\Models\Import;
use App\Models\Module;
use App\Services\Import\CsvDelimiterDetector;
use App\Services\Import\ImportFileReader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

use function in_array;

class ImportController extends Controller
{
    public function preview(Request $request, string $module)
    {
        // get the module
        Module::query()->where('slug', $module)->where('is_active', true)->firstOrFail();

        // validate the file
        $request->validate([
            'file' => ['required', 'file', 'extensions:csv,json', 'max:10240'],
        ]);

        // read the file
        $file = $request->file('file');

        // extension is now guaranteed csv or json by validation above
        $extension = strtolower($file->getClientOriginalExtension());
        $format = $this->detectFormat($extension);

        // sanity check: check if a json is indeed a json 
        if ($format === 'csv' && $this->looksLikeJson($file->getRealPath())) {
            return response()->json(['message' => __('globals.import.mislabeled_csv')], 422);
        }

        $storedPath = $file->storeAs('imports', Str::uuid().'.'.$extension, 'local');
        $absolutePath = Storage::disk('local')->path($storedPath);

        $delimiter = null;
        if ($format === 'csv') {
          // ";" or ","
          $delimiter = CsvDelimiterDetector::detect($this->firstLine($absolutePath));
        }

        $reader = app(ImportFileReader::class);

        try {
            $headers = $reader->headers($absolutePath, $format, $delimiter);
            $totalRows = $reader->countRows($absolutePath, $format, $delimiter);
            $sampleRows = $reader->sample($absolutePath, $format, $delimiter, 20);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($storedPath);

            return response()->json(['message' => $e->getMessage()], 422);
        }

        if ($totalRows > config('import.max_rows')) {
            Storage::disk('local')->delete($storedPath);

            return response()->json([
                'message' => __('globals.import.too_many_rows', ['count' => $totalRows, 'max' => config('import.max_rows')]),
            ], 422);
        }

        $import = Import::create([
            'module_slug' => $module,
            'user_id' => $request->user()?->id,
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $storedPath,
            'format' => $format,
            'delimiter' => $delimiter,
            'status' => config('import.statuses.mapping'),
            'total_rows' => $totalRows,
        ]);

        return response()->json([
            'importId' => $import->id,
            'format' => $format,
            'delimiter' => $delimiter,
            'headers' => $headers,
            'sampleRows' => $sampleRows,
            'totalRows' => $totalRows,
        ]);
    }

    public function start(Request $request, string $module, Import $import)
    {
        $moduleModel = Module::query()->where('slug', $module)->where('is_active', true)->firstOrFail();

        abort_unless($import->module_slug === $module, 404);
        abort_unless($import->status === config('import.statuses.mapping'), 422, 'This import has already been started.');

        $data = $request->validate([
            'mapping' => ['required', 'array'],
            'matchField' => ['nullable', 'string'],
            'delimiter' => ['nullable', 'string', Rule::in([',', ';'])],
        ]);

        $mapping = array_filter($data['mapping'], fn ($fieldName) => ! empty($fieldName));
        abort_if(empty($mapping), 422, 'Map at least one column to a field.');

        $mappedFieldNames = array_values($mapping);
        $matchField = $data['matchField'] ?? null;

        if ($matchField && ! in_array($matchField, $mappedFieldNames, true)) {
            abort(422, 'The match field must be one of the mapped fields.');
        }

        $fields = Import::mappableFields($moduleModel->allFields());

        $mappableNames = $fields->pluck('name')->all();
        $invalidTargets = array_diff($mappedFieldNames, $mappableNames);

        if (! empty($invalidTargets)) {
            abort(422, 'These fields cannot be imported into: '.implode(', ', $invalidTargets));
        }

        $missingRequired = $fields
            ->filter(fn (Field $field) => $field->required
                && empty($field->default_value)
                && ! in_array($field->name, $mappedFieldNames, true)
            )
            ->pluck('label');

        if ($missingRequired->isNotEmpty()) {
            abort(422, 'The following required fields must be mapped: '.$missingRequired->implode(', '));
        }

        $import->update([
            'column_mapping' => $mapping,
            'match_field' => $matchField,
            'delimiter' => $data['delimiter'] ?? $import->delimiter,
            'status' => config('import.statuses.queued'),
        ]);

        // if for some reason total_rows is null we should never run through sync.
        if ($import->total_rows !== null && $import->total_rows <= config('import.sync_row_threshold')) {
            ProcessImportJob::dispatchSync($import->id);
        } else {
            ProcessImportJob::dispatch($import->id);
        }

        $import->refresh();
        $isTerminal = in_array($import->status, [config('import.statuses.completed'), config('import.statuses.failed')], true);

        return response()->json($this->statusPayload($import), $isTerminal ? 200 : 202);
    }

    public function status(string $module, Import $import)
    {
        abort_unless($import->module_slug === $module, 404);

        return response()->json($this->statusPayload($import));
    }

    private function statusPayload(Import $import): array
    {
        return [
            'id' => $import->id,
            'status' => $import->status,
            'totalRows' => $import->total_rows,
            'processedRows' => $import->processed_rows,
            'createdCount' => $import->created_count,
            'updatedCount' => $import->updated_count,
            'skippedCount' => $import->skipped_count,
            'progressPercent' => $import->progressPercent(),
            'errors' => $import->errors ?? [],
            'errorsTruncated' => $import->errors_truncated,
            'failedReason' => $import->failed_reason,
        ];
    }

    private function detectFormat(string $extension): string
    {
        // extension is locked to csv/json by validation
        return $extension === 'json' ? 'json' : 'csv';
    }

    // sanity check for a .csv upload
    private function looksLikeJson(string $realPath): bool
    {
        // attempt to open the file
        $handle = fopen($realPath, 'r');

        // is the file open ? then read the first 512 bytes (should be enough before EOL)
        $chunk = $handle ? fread($handle, 512) : '';

        // if opened, then close handle to release the resource
        if ($handle) {
            fclose($handle);
        }

        // first non-blank char is [ or { -> almost certainly JSON, not CSV
        $firstChar = ltrim((string) $chunk)[0] ?? '';

        return $firstChar === '[' || $firstChar === '{';
    }

    private function firstLine(string $absolutePath): string
    {
        $handle = fopen($absolutePath, 'r');
        $line = $handle ? (fgets($handle) ?: '') : '';
        if ($handle) {
            fclose($handle);
        }

        return $line;
    }
}
