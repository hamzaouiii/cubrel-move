<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Str;

use function in_array;
use function is_array;

class PdfValueRenderer
{
    public function render(string $type, mixed $value, ?array $dropdownValues = null): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        // Handle before the generic is_array fallback so address arrays are formatted properly.
        if ($type === 'address') {
            return $this->formatAddress($value);
        }

        if (is_array($value)) {
            return implode(', ', array_filter(array_map('strval', $value)));
        }

        return match (true) {
            in_array($type, ['select', 'dropdown', 'status'])
                => $this->resolveDropdown((string) $value, $dropdownValues),

            $type === 'date'
                => $this->formatDate((string) $value),

            $type === 'datetime'
                => $this->formatDatetime((string) $value),

            in_array($type, ['decimal', 'number', 'currency'])
                => is_numeric($value) ? PdfNumberFormatter::format((float) $value) : (string) $value,

            in_array($type, ['bool', 'checkbox'])
                => (bool) $value ? __('globals.pdf.yes') : __('globals.pdf.no'),

            default => (string) $value,
        };
    }

    private function formatAddress(mixed $value): string
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value   = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($value)) {
            return '—';
        }

        $street  = trim((string) ($value['street']      ?? ''));
        $postal  = trim((string) ($value['postal_code'] ?? ''));
        $city    = trim((string) ($value['city']        ?? ''));
        $state   = trim((string) ($value['state']       ?? ''));
        $country = trim((string) ($value['country']     ?? ''));

        $line2 = implode(' ', array_filter([$postal, $city]));
        $line3 = implode(', ', array_filter([$state, $country]));

        $lines = array_filter([$street, $line2, $line3]);
        return $lines ? implode("\n", $lines) : '—';
    }

    private function resolveDropdown(string $value, ?array $dropdownValues): string
    {
        if (empty($dropdownValues)) {
            return $value;
        }

        foreach ($dropdownValues as $entry) {
            if (($entry['value'] ?? null) === $value) {
                $label = $entry['label'] ?? $value;
                return Str::contains($label, '.') ? __($label) : $label;
            }
        }

        return $value;
    }

    private function formatDate(string $value): string
    {
        try {
            $format = Settings::get('date_format', 'd/m/Y');
            return Carbon::parse($value)->format($format);
        } catch (\Throwable) {
            return $value;
        }
    }

    private function formatDatetime(string $value): string
    {
        try {
            $format = Settings::get('datetime_format', 'd/m/Y H:i');
            return Carbon::parse($value)->format($format);
        } catch (\Throwable) {
            return $value;
        }
    }
}
