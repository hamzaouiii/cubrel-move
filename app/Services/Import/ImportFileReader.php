<?php

namespace App\Services\Import;

use Generator;
use RuntimeException;

/**
 * Import rendering
 */
class ImportFileReader
{
    /**
     * @return array<int, string>
     */
    public function headers(string $absolutePath, string $format, ?string $delimiter, int $scanLimit = 20): array
    {
        if ($format === 'csv') {
            $handle = $this->openCsv($absolutePath);
            $headers = $this->dedupeHeaders(fgetcsv($handle, 0, $delimiter, '"', '\\') ?: []);
            fclose($handle);

            return $headers;
        }

        $headers = [];
        foreach ($this->jsonRows($absolutePath) as $i => $row) {
            if ($i >= $scanLimit) {
                break;
            }
            foreach (array_keys($row) as $key) {
                if (! in_array($key, $headers, true)) {
                    $headers[] = $key;
                }
            }
        }

        return $headers;
    }

    /**
     * First $limit data rows, keyed by header, for the mapping-step preview table.
     *
     * @return array<int, array<string, mixed>>
     */
    public function sample(string $absolutePath, string $format, ?string $delimiter, int $limit = 20): array
    {
        $rows = [];
        foreach ($this->rows($absolutePath, $format, $delimiter) as [, $row]) {
            $rows[] = $row;
            if (count($rows) >= $limit) {
                break;
            }
        }

        return $rows;
    }

    public function countRows(string $absolutePath, string $format, ?string $delimiter): int
    {
        $count = 0;
        foreach ($this->rows($absolutePath, $format, $delimiter) as $row) {
            $count++;
        }

        return $count;
    }

    /**
     * Yields [1-based row number, associative row array] for every data row.
     *
     * @return Generator<int, array{0: int, 1: array<string, mixed>}>
     */
    public function rows(string $absolutePath, string $format, ?string $delimiter): Generator
    {
        if ($format === 'csv') {
            yield from $this->csvRows($absolutePath, $delimiter);

            return;
        }

        $rowNumber = 0;
        foreach ($this->jsonRows($absolutePath) as $row) {
            $rowNumber++;
            yield [$rowNumber, $row];
        }
    }

    private function csvRows(string $absolutePath, ?string $delimiter): Generator
    {
        $handle = $this->openCsv($absolutePath);
        $headers = $this->dedupeHeaders(fgetcsv($handle, 0, $delimiter, '"', '\\') ?: []);

        $rowNumber = 0;
        while (($fields = fgetcsv($handle, 0, $delimiter, '"', '\\')) !== false) {
            if ($fields === [null] || $fields === false) {
                continue;
            }
            $rowNumber++;
            yield [$rowNumber, $this->combineRow($headers, $fields)];
        }

        fclose($handle);
    }

    /**
     * @return Generator<int, array<string, mixed>>
     */
    private function jsonRows(string $absolutePath): Generator
    {
        $decoded = json_decode(file_get_contents($absolutePath), true);

        if (! is_array($decoded)) {
            throw new RuntimeException(__('globals.import.invalid_json'));
        }

        if (! array_is_list($decoded)) {
            throw new RuntimeException(__('globals.import.json_must_be_array'));
        }

        foreach ($decoded as $row) {
            if (! is_array($row)) {
                throw new RuntimeException(__('globals.import.json_rows_must_be_objects'));
            }
            yield $row;
        }
    }

    private function openCsv(string $absolutePath)
    {
        $handle = fopen($absolutePath, 'r');

        if ($handle === false) {
            throw new RuntimeException(__('globals.import.unable_to_read_file'));
        }

        return $handle;
    }

    /**
     * @param  array<int, string|null>  $headers
     * @return array<int, string>
     */
    private function dedupeHeaders(array $headers): array
    {
        $seen = [];
        $result = [];

        foreach ($headers as $header) {
            $header = trim((string) $header);
            $base = $header;
            $suffix = 2;

            while (isset($seen[$header])) {
                $header = "{$base} ({$suffix})";
                $suffix++;
            }

            $seen[$header] = true;
            $result[] = $header;
        }

        return $result;
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, mixed>  $fields
     * @return array<string, mixed>
     */
    private function combineRow(array $headers, array $fields): array
    {
        $count = count($headers);
        $fields = array_pad($fields, $count, null);
        $fields = array_slice($fields, 0, $count);

        return array_combine($headers, $fields);
    }
}
