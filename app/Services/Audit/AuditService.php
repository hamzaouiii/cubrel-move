<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AuditService
{
    public static function log(string $action, ?string $moduleSlug, ?string $recordId, ?array $changes = null, ?array $affectedRecords = null): void
    {
        if (! auth()->check()) {
            return;
        }
        // logs who did what and when. if impersonation session is ongoing then the who did what as whom
        $log = AuditLog::create([
            'module_slug' => $moduleSlug,
            'record_id' => $recordId,
            'user_id' => auth()->id(),
            'impersonator_id' => Session::get('impersonator_id'),
            'action' => $action,
            'diff' => $changes,
        ]);
        // bulk update
        if (! empty($affectedRecords)) {
            $isKeyed = ! array_is_list($affectedRecords);

            $rows = collect($affectedRecords)
                ->mapWithKeys(fn ($value, $key) => $isKeyed ? [(string) $key => $value] : [(string) $value => null])
                ->map(fn ($oldValue, $id) => [
                    'audit_log_id' => $log->id,
                    'record_id' => $id,
                    'old_value' => $oldValue === null ? null : json_encode($oldValue),
                ])
                ->values();

            foreach ($rows->chunk(1000) as $chunk) {
                DB::table('audit_log_affected_records')->insert($chunk->all());
            }
        }
    }
}
