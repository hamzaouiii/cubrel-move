<?php

namespace App\Services\Import;

use App\Models\Field;
use App\Support\Settings;
use Carbon\Carbon;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

use function in_array;
use function is_numeric;

/**
 * Convert raw data to Cubrel scoped data. i.e resolve dropdown values 
 * valditaion for values before importing
 */
class ImportValueCoercer
{
    /**
     * @return array{value: mixed, error: ?string}
     */
    public function coerce(Field $field, mixed $raw): array
    {
        $value = is_string($raw) ? trim($raw) : $raw;

        if ($value === null || $value === '') {
            return ['value' => null, 'error' => null];
        }

        return match (true) {
            in_array($field->type, ['select', 'status'], true) => $this->coerceDropdown($field, (string) $value),
            $field->type === 'date' => $this->coerceDate($field, (string) $value),
            $field->type === 'datetime' => $this->coerceDatetime($field, (string) $value),
            in_array($field->type, ['number', 'integer', 'decimal', 'currency', 'percentage'], true) => $this->coerceNumber($field, (string) $value),
            $field->type === 'checkbox' => $this->coerceCheckbox($field, (string) $value),
            default => ['value' => (string) $value, 'error' => null],
        };
    }

    private function coerceDropdown(Field $field, string $value): array
    {
        $options = $field->dropdown_list?->values ?? [];

        foreach ($options as $option) {
            if ($this->dropdownOptionMatches($option, $value)) {
                return ['value' => $option['value'], 'error' => null];
            }
        }

        return ['value' => null, 'error' => "'{$value}' is not a valid option for {$field->name}"];
    }

    /**
     * Dropdown option labels are usually translation keys, not literal display
     * text A raw import value never matches the key itself,
     * so it has to be compared against the translated text instead.
     * we need to check the lang key against all possible translations
     * */
    private function dropdownOptionMatches(array $option, string $value): bool
    {
        $rawValue = (string) ($option['value'] ?? '');
        $rawLabel = (string) ($option['label'] ?? '');

        if (strcasecmp($rawValue, $value) === 0 || strcasecmp($rawLabel, $value) === 0) {
            return true;
        }

        if (! Str::contains($rawLabel, '.')) {
            return false;
        }

        foreach ($this->enabledLocales() as $locale) {
            $translated = Lang::get($rawLabel, [], $locale);

            if (is_string($translated) && strcasecmp($translated, $value) === 0) {
                return true;
            }
        }

        return false;
    }

    // every locale the app is configured for — falls back to the active
    // locale alone if the setting is missing/empty
    private function enabledLocales(): array
    {
        $locales = json_decode((string) Settings::get('enabled_languages'), true);

        return is_array($locales) && $locales !== [] ? $locales : [app()->getLocale()];
    }

    private function coerceDate(Field $field, string $value): array
    {
        try {
            return ['value' => Carbon::parse($value)->toDateString(), 'error' => null];
        } catch (\Throwable) {
            return ['value' => null, 'error' => "'{$value}' is not a valid date for {$field->name}"];
        }
    }

    private function coerceDatetime(Field $field, string $value): array
    {
        try {
            return ['value' => Carbon::parse($value)->toDateTimeString(), 'error' => null];
        } catch (\Throwable) {
            return ['value' => null, 'error' => "'{$value}' is not a valid datetime for {$field->name}"];
        }
    }

    private function coerceNumber(Field $field, string $value): array
    {
        $normalized = str_replace(',', '', $value);

        if (! is_numeric($normalized)) {
            return ['value' => null, 'error' => "'{$value}' is not a valid number for {$field->name}"];
        }

        return ['value' => $normalized + 0, 'error' => null];
    }

    // checkbox accepts defined values in config/import.checkbox_true_values(yes/no/true/false/1/0/etc.) and also translations values for checkbox_yes and checkbox_no (ja, nein, oui, non....)
    private function coerceCheckbox(Field $field, string $value): array
    {
        $normalized = strtolower($value);

        if (in_array($normalized, config('import.checkbox_true_values'), true)) {
            return ['value' => true, 'error' => null];
        }

        if (in_array($normalized, config('import.checkbox_false_values'), true)) {
            return ['value' => false, 'error' => null];
        }

        foreach ($this->enabledLocales() as $locale) {
            if (strcasecmp($value, Lang::get('fields.checkbox_yes', [], $locale)) === 0) {
                return ['value' => true, 'error' => null];
            }

            if (strcasecmp($value, Lang::get('fields.checkbox_no', [], $locale)) === 0) {
                return ['value' => false, 'error' => null];
            }
        }

        return ['value' => null, 'error' => "'{$value}' is not a valid yes/no value for {$field->name}"];
    }
}
