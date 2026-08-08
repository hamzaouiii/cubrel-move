<?php

namespace App\Http\Controllers;

use App\Models\Dashboard;
use App\Models\Module;
use App\Services\Aggregation\AggregationService;
use App\Support\Filters\FilterQueryBuilder;
use App\Services\Users\OwnershipService;
use App\Support\DashboardPresets;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user      = Auth::user();
        $dashboard = Dashboard::where('user_id', $user->id)->first();

        return Inertia::render('Dashboard/Index', [
            'ownedRecords'     => $this->getOwnedRecords($user),
            'dashboardLayout'  => $dashboard?->layout ?? DashboardPresets::layout(DashboardPresets::presetType($user)),
            'dashboardModules' => $this->getActiveModules(),
            'dashboardConfig'  => config('dashboard'),
            'filterOperators'  => config('filter_operators'),
        ]);
    }

    public function widgetData(Request $request): JsonResponse
    {
        $request->validate([
            'type'          => ['required', 'string', Rule::in(config('dashboard.widget_types'))],
            'config'        => ['required', 'array'],
            'config.module' => ['required', 'string'],
        ]);

        $module = Module::where('slug', $request->input('config.module'))
            ->where('is_active', true)
            ->first();

        if (!$module) {
            abort(422, 'Module not found or inactive.');
        }

        $config = $request->input('config');
        $user   = Auth::user();

        // Scope non-admin, non-org-wide users to their own records by default.
        // Widgets can opt out via config.showAllRecords — module-level access
        // already governs what the user can see, this is just a default filter.
        if (!$user->isAdmin() && !in_array($user->type ?? '', config('dashboard.org_wide_types')) && $module->has_owner && !($config['showAllRecords'] ?? false)) {
            $hasOwnerFilter = collect($config['filters'] ?? [])
                ->contains(fn ($f) => ($f['field'] ?? null) === 'owner_id');

            if (!$hasOwnerFilter) {
                $config['filters'][] = [
                    'field'    => 'owner_id',
                    'operator' => 'equals',
                    'value'    => $user->id,
                ];
            }
        }

        $result = match ($request->input('type')) {
            'time-series' => AggregationService::timeSeries($module, $config),
            'metric'      => AggregationService::metric($module, $config),
            'breakdown'   => AggregationService::breakdown($module, $config),
            'record-list' => AggregationService::recordList($module, $config),
            'people'      => AggregationService::people($module, $config),
        };

        return response()->json($result);
    }

    public function moduleFields(string $slug): JsonResponse
    {
        $module = Module::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $fields = $module->allFields()->map(fn ($f) => [
            'name'           => $f->name,
            'label'          => $f->label,
            'type'           => $f->type,
            'related_module' => $f->related_module,
        ]);

        return response()->json($fields->values());
    }

    public function moduleRelationships(string $slug): JsonResponse
    {
        $module = Module::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $relationships = $module->relationships()->map(fn ($r) => [
            'name'         => $r->name,
            'label'        => $r->label,
            'related_slug' => $r->related_slug,
        ]);

        return response()->json($relationships->values());
    }

    public function filterableFields(string $slug): JsonResponse
    {
        $module = Module::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $fields = collect(FilterQueryBuilder::allowedFieldsMap($module))
            ->map(fn ($f) => [
                'name'           => $f->name,
                'label'          => $f->label,
                'type'           => $f->type,
                'related_module' => $f->related_module,
                'dropdown_list'  => $f->dropdown_list
                    ? ['values' => array_values($f->dropdown_list->values ?? [])]
                    : null,
            ])
            ->values();

        return response()->json($fields);
    }

    public function saveLayout(Request $request): JsonResponse
    {
        $request->validate([
            'layout' => ['present', 'array'],
        ]);

        $user = Auth::user();

        Dashboard::updateOrCreate(
            ['user_id' => $user->id],
            [
                'layout'     => $request->layout,
                'slug'       => 'dashboard_' . $user->id,
                'name'       => 'My Dashboard',
            ]
        );

        return response()->json(['ok' => true]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function getOwnedRecords(object $user): Collection
    {
        return app(OwnershipService::class)->getRecordsByUser($user->id);
    }

    private function getActiveModules(): array
    {
        return Module::where('is_active', true)
            ->orderBy('name')
            ->get(['slug', 'name', 'label', 'icon'])
            ->toArray();
    }
}
