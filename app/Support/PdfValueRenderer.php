<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Str;

class PdfValueRenderer
{
    public function render(string $type, mixed $value, ?array $dropdownValues = null): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return match (true) {
            in_array($type, ['select', 'dropdown', 'status'])
                => $this->resolveDropdown((string) $value, $dropdownValues),

            $type === 'date'
                => $this->formatDate((string) $value),

            $type === 'datetime'
                => $this->formatDatetime((string) $value),

            in_array($type, ['decimal', 'number', 'currency'])
                => is_numeric($value) ? number_format((float) $value, 2) : (string) $value,

            in_array($type, ['bool', 'checkbox'])
                => (bool) $value ? __('pdf.yes') : __('pdf.no'),

            default => (string) $value,
        };
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
