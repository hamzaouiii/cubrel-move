<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Field;
use App\Models\Module;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('perPage', Settings::getPersonal('list_view_limit', 15));

        $query = AuditLog::query()->with(['user', 'impersonator'])->latest('created_at');

        if ($module = $request->get('module')) {
            $query->where('module_slug', $module);
        }

        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($action = $request->get('action')) {
            $query->where('action', $action);
        }

        if ($from = $request->get('date_from')) {
            $query->where('created_at', '>=', $from);
        }

        if ($to = $request->get('date_to')) {
            $query->where('created_at', '<=', $to);
        }

        $paginator = $query->paginate($perPage);

        $modules = Module::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'slug', 'name', 'label', 'color', 'icon']);

        $allFields = Field::query()
            ->where(function ($q) use ($modules) {
                $q->whereIn('module_id', $modules->pluck('id'))->orWhere('is_global', true);
            })
            ->with('dropdown_list')
            ->get(['id', 'module_id', 'dropdown_list_id', 'name', 'label', 'type', 'related_module', 'is_global']);

        $globalFields = $allFields->where('is_global', true);

        $fieldsByModule = $modules->mapWithKeys(fn (Module $module) => [
            $module->slug => $allFields->where('module_id', $module->id)
                ->merge($globalFields)
                ->unique('name')
                ->map(fn ($field) => [
                    'name' => $field->name,
                    'label' => $field->label,
                    'type' => $field->type,
                    'related_module' => $field->related_module,
                    'dropdown_list' => $field->dropdown_list,
                ])
                ->values(),
        ]);

        $last = $paginator->lastPage();
        $pages = [];
        for ($p = 1; $p <= $last; $p++) {
            $pages[] = [
                'label' => (string) $p,
                'page' => $p,
                'url' => $paginator->url($p),
                'active' => $p === $paginator->currentPage(),
            ];
        }

        return Inertia::render('Settings/AuditTrail/Index', [
            'logs' => collect($paginator->items())->map->toDisplayArray(),
            'meta' => [
                'total' => $paginator->total(),
                'perPage' => $paginator->perPage(),
                'currentPage' => $paginator->currentPage(),
                'lastPage' => $last,
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'links' => [
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ],
                'pages' => $pages,
            ],
            'filters' => $request->only(['module', 'user_id', 'action', 'date_from', 'date_to', 'perPage']),
            'audit_modules' => $modules,
            'fields_by_module' => $fieldsByModule,
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Per-record breakdown of a bulk update/delete batch row — the global log only
     * shows one summary row per batch, so this is what the "list of affected
     * records" view opens when that row is clicked.
     */
    public function affectedRecords(Request $request, AuditLog $auditLog)
    {
        $moduleModel = Module::where('slug', $auditLog->module_slug)->first();
        $modelClass = $moduleModel?->model_class;

        $paginator = DB::table('audit_log_affected_records')
            ->where('audit_log_id', $auditLog->id)
            ->orderBy('id')
            ->paginate($request->get('perPage', 15));

        $ids = collect($paginator->items())->pluck('record_id')->all();

        $liveLabels = ($auditLog->action === 'updated' && $modelClass && class_exists($modelClass))
            ? $modelClass::whereIn('id', $ids)->pluck('name', 'id')
            : collect();

        $data = collect($paginator->items())->map(function ($row) use ($auditLog, $liveLabels) {
            $oldValue = $row->old_value !== null ? json_decode($row->old_value, true) : null;

            return [
                'record_id' => $row->record_id,
                'label' => $auditLog->action === 'deleted'
                    ? ($oldValue ?? $row->record_id)
                    : ($liveLabels->get($row->record_id) ?? $row->record_id),
                'old_value' => $auditLog->action === 'updated' ? $oldValue : null,
                'still_exists' => $auditLog->action !== 'deleted',
            ];
        });

        return response()->json([
            'log' => $auditLog->toDisplayArray(),
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
