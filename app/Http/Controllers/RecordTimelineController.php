<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Module;
use App\Models\Relationship;
use App\Models\User;
use App\Services\Relationships\RelationshipService;
use Illuminate\Http\Request;

class RecordTimelineController extends Controller
{
    public function index(string $module, string $recordId)
    {
        $moduleModel = Module::where('slug', $module)->where('is_active', true)->firstOrFail();

        $auditEntries = AuditLog::with(['user', 'impersonator'])
            ->forRecord($module, $recordId)
            ->latest('created_at')
            ->limit(50)
            ->get()
            ->map(function (AuditLog $log) {
                return array_merge($log->toDisplayArray(), [
                    'source' => 'audit',
                    'timestamp' => $log->created_at,
                ]);
            });

        $activityEntries = collect();
        $activityModulesPayload = collect();

        if ($moduleModel->has_activity) {
            $activityModules = Module::where('is_activity', true)->get();

            foreach ($activityModules as $activityModule) {
                $activityModulesPayload->push([
                    'slug' => $activityModule->slug,
                    'label' => $activityModule->label,
                    'single_label' => $activityModule->single_label,
                    'icon' => $activityModule->icon,
                    'color' => $activityModule->color,
                    'fields' => $activityModule->allFields()->values(),
                ]);

                $relationshipName = "{$module}_{$activityModule->slug}";

                if (! Relationship::where('name', $relationshipName)->exists()) {
                    continue;
                }

                $records = RelationshipService::getRelatedRecords($relationshipName, $module, $recordId);

                foreach ($records as $record) {
                    $activityEntries->push([
                        'source' => 'activity',
                        'entry_type' => $activityModule->slug,
                        'module' => [
                            'slug' => $activityModule->slug,
                            'label' => $activityModule->label,
                            'icon' => $activityModule->icon,
                            'color' => $activityModule->color,
                        ],
                        'record' => $record,
                        'timestamp' => $record->created_at,
                    ]);
                }
            }
        }


        $ownerIds = $activityEntries->pluck('record.owner_id')->filter()->unique();
        $ownerNames = $ownerIds->isNotEmpty()
            ? User::whereIn('id', $ownerIds)->pluck('name', 'id')
            : collect();

        $activityEntries = $activityEntries->map(function ($entry) use ($ownerNames) {
            $entry['owner_label'] = $ownerNames->get($entry['record']->owner_id);

            return $entry;
        });

        $timeline = $auditEntries->concat($activityEntries)
            ->sortByDesc('timestamp')
            ->values();

        return response()->json([
            'data' => $timeline,
            'activityModules' => $activityModulesPayload->values(),
        ]);
    }
}
