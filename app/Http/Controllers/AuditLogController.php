<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Field;
use App\Models\Module;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('perPage', Settings::get('list_view_limit', 15));

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

        // Field metadata per module — not just {name, label} for the
        // "Changes" column, but everything HistoryModal needs (type,
        // related_module, dropdown_list) since clicking a row opens that
        // same modal for the row's record. One query for every module's
        // fields (not $module->allFields() in a loop, which re-queries —
        // and re-eager-loads dropdown_list — once per module).
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
}
