<?php

namespace App\Services\Api;

use App\Contracts\ModuleHandler;
use App\Exceptions\ModuleHandlerNotFoundException;
use App\Http\Resources\Api\V1\RecordResource;
use App\Models\BaseModule;
use App\Models\Module;
use App\Models\MeetingAttendee;
use App\Models\Modules\LineItem;
use App\Scopes\AdminOnlyModuleScope;
use App\Services\Relationships\RelationshipService;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * API wrapper around Module/handler dynamic resolution 
 */
class RecordApiService
{
    //Per-instance cache
     
    protected array $moduleCache = [];

    public function resolveModule(string $slug): Module
    {
        if (array_key_exists($slug, $this->moduleCache)) {
            return $this->moduleCache[$slug];
        }

        // AdminOnlyModuleScope is for system modules anyway if the ability allows it we might as well allow it here.
        return $this->moduleCache[$slug] = Module::withoutGlobalScope(AdminOnlyModuleScope::class)
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();
    }

    /**
     * Answers the question: Is this token allowed to do this?
     */
    public function authorizeAbility(string $module, string $verb): void
    {
        if (in_array($module, config('api.excluded_modules', []), true)) {
            abort(404);
        }

        if ($verb !== 'read' && in_array($module, config('api.read_only_modules', []), true)) {
            abort(403, __('api.errors.forbidden_read_only_module', ['module' => $module]));
        }

        $token = request()->user()->currentAccessToken();

        if (! $token->can('*') && ! $token->can("{$module}:{$verb}")) {
            abort(403, __('api.errors.forbidden_missing_ability', ['module' => $module, 'verb' => $verb]));
        }
    }

    public function resolveHandler(Module $module): ModuleHandler
    {
        $handlerClass = $module->handler_class
            ?? 'App\\Handlers\\Modules\\'.Str::studly($module->slug).'ModuleHandler';

        if (! class_exists($handlerClass)) {
            throw new ModuleHandlerNotFoundException(
                "Handler class [{$handlerClass}] not found for module [{$module->slug}]."
            );
        }

        return app($handlerClass);
    }

    public function list(Module $module, array $params): array
    {
        return $this->resolveHandler($module)->getListData($module, $params);
    }

    public function find(Module $module, string $id): BaseModule
    {
        $modelClass = $module->model_class;

        return $modelClass::findOrFail($id);
    }

    public function create(Module $module, array $input): BaseModule
    {
        $modelClass = $module->model_class;

        return $modelClass::create($this->allowedInput($module, $input));
    }

    public function update(Module $module, string $id, array $input): BaseModule
    {
        $modelClass = $module->model_class;

        $record = $modelClass::findOrFail($id);

        $record->fill($this->allowedInput($module, $input))->save();

        return $record;
    }

    public function delete(Module $module, string $id): void
    {
        $modelClass = $module->model_class;

        $modelClass::findOrFail($id)->delete();
    }

    public function lineItemsFor(Module $module, string $recordId): array
    {
        return LineItem::query()
            ->where('parent_type', $module->slug)
            ->where('parent_id', $recordId)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (LineItem $item) => (new RecordResource($item, 'line_items'))->toArray(request()))
            ->values()
            ->all();
    }

  // Fetch a meeting's attendees ordered organizer-first, then required, then optional, then alphabetically by name, and shape each one through RecordResource before returning them as a plain array
    public function attendeesFor(string $meetingId): array
    {
        return MeetingAttendee::query()
            ->where('meeting_id', $meetingId)
            ->orderByRaw("CASE role WHEN 'organizer' THEN 0 WHEN 'required' THEN 1 WHEN 'optional' THEN 2 ELSE 3 END")
            ->orderBy('name')
            ->get()
            ->map(fn (MeetingAttendee $attendee) => (new RecordResource($attendee, 'meeting_attendees'))->toArray(request()))
            ->values()
            ->all();
    }

    /**
     * Strips getAllRelatedRecords() down to just records per relationship,
     * skipping its panel metadata 
     */
    public function relatedRecordsFor(string $module, string $recordId): array
    {
        $excluded = config('api.excluded_modules', []);

        return RelationshipService::getAllRelatedRecords($module, $recordId, includePanelData: false)
            ->reject(fn (array $relationship) => in_array($relationship['related_slug'], $excluded, true))
            ->map(fn (array $relationship) => collect($relationship['records'])
                ->map(fn ($record) => (new RecordResource($record, $relationship['related_slug']))->toArray(request()))
                ->values()
                ->all())
            ->all();
    }

    /**
     * Allowlist input against writable fields. Custom fields pass through
     * flat
     */
    protected function allowedInput(Module $module, array $input): array
    {
        return Arr::only($input, $module->writableFieldNames());
    }
}
