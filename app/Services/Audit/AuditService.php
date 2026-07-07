<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Session;

class AuditService
{

    public static function log(string $action, ?string $moduleSlug, ?string $recordId, ?array $changes = null): void
    {
        if (! auth()->check()) {
            return;
        }
        // logs who did what and when. if impersonation session is ongoing then the who did what as whom
        AuditLog::create([
            'module_slug' => $moduleSlug,
            'record_id' => $recordId,
            'user_id' => auth()->id(),
            'impersonator_id' => Session::get('impersonator_id'),
            'action' => $action,
            'diff' => $changes,
        ]);
    }
}
