<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Module;
use Illuminate\Http\Request;

class RecordHistoryController extends Controller
{
    public function index(Request $request, string $module, string $recordId)
    {
        Module::where('slug', $module)->where('is_active', true)->firstOrFail();
        $paginator = AuditLog::with(['user', 'impersonator'])
            ->where('module_slug', $module)
            ->where(function ($query) use ($recordId) {
                $query->where('record_id', $recordId)
                    ->orWhereJsonContains('diff->affected_ids', $recordId);
            })
            ->latest('created_at')
            ->paginate($request->get('perPage', 15));

        return response()->json([
            'data' => collect($paginator->items())->map->toDisplayArray(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
