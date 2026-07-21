<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecordHistoryController extends Controller
{
    public function index(Request $request, string $module, string $recordId)
    {
        Module::where('slug', $module)->where('is_active', true)->firstOrFail();
        $paginator = AuditLog::with(['user', 'impersonator'])
            ->forRecord($module, $recordId)
            ->latest('created_at')
            ->paginate($request->get('perPage', 15));

        $oldValuesByLogId = DB::table('audit_log_affected_records')
            ->whereIn('audit_log_id', collect($paginator->items())->pluck('id'))
            ->where('record_id', $recordId)
            ->pluck('old_value', 'audit_log_id');

        $data = collect($paginator->items())->map(function (AuditLog $log) use ($oldValuesByLogId) {
            $display = $log->toDisplayArray();

            if ($log->record_id === null && $oldValuesByLogId->has($log->id)) {
                $raw = $oldValuesByLogId->get($log->id);
                $display['changes']['old_value'] = $raw !== null ? json_decode($raw, true) : null;
            }

            return $display;
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
