<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Field;
use App\Models\Module;
use App\Services\Api\RecordApiService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * One generic request for every module's create/update payload.
 * rules are built at runtime from the module's Field metadata.
 */
class ModuleRecordRequest extends FormRequest
{
    /**
     * Runs before rules() - without this, an excluded module's field names
     * would leak through a 422 before RecordController ever gets a say.
     */
    public function authorize(): bool
    {
        app(RecordApiService::class)->authorizeAbility($this->route('module'), 'write');

        return true;
    }

    public function rules(): array
    {
        // Same call RecordController makes next - cached, so not a second query.
        $module = app(RecordApiService::class)->resolveModule($this->route('module'));
        $isUpdate = in_array($this->method(), ['PUT', 'PATCH']);

        $rules = [];

        foreach ($module->allFields() as $field) {
            /** @var Field $field */
            // owner_id is auto-assigned (BaseModule::booted()) and never partner-writable, so never required here.
            if ($field->readonly || $field->is_calculated || $field->name === 'owner_id') {
                continue;
            }

            $fieldRules = $this->rulesForField($field);

            // required only applies to store(); update() is a partial patch.
            $fieldRules[] = ($field->required && ! $isUpdate) ? 'required' : 'sometimes';

            $rules[$field->name] = $fieldRules;
        }

        return $rules;
    }

    protected function rulesForField(Field $field): array
    {
        return match ($field->type) {
            'number' => ['numeric'],
            'date', 'datetime' => ['date'],
            'boolean' => ['boolean'],
            'email' => ['email'],
            'record' => [$this->existsRuleForRelatedModule($field->related_module)],
            'dropdown' => $field->hasOptions() ? ['in:'.implode(',', $field->options)] : ['string'],
            default => ['string'],
        };
    }

    protected function existsRuleForRelatedModule(?string $relatedModuleSlug): string
    {
        $table = $relatedModuleSlug
            ? Module::where('slug', $relatedModuleSlug)->value('table_name')
            : null;

        return $table ? "exists:{$table},id" : 'string';
    }
}
