<?php

namespace App\Http\Controllers;

use App\Models\ImpersonationSession;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ImpersonationSessionController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('perPage', Settings::getPersonal('list_view_limit', 15));

        $query = ImpersonationSession::query()->with(['impersonator', 'targetUser'])->latest('started_at');

        if ($impersonatorId = $request->get('impersonator_id')) {
            $query->where('impersonator_id', $impersonatorId);
        }

        if ($targetUserId = $request->get('target_user_id')) {
            $query->where('target_user_id', $targetUserId);
        }

        if ($from = $request->get('date_from')) {
            $query->where('started_at', '>=', $from);
        }

        if ($to = $request->get('date_to')) {
            $query->where('started_at', '<=', $to);
        }

        $paginator = $query->paginate($perPage);

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

        return Inertia::render('Settings/ImpersonationSessions/Index', [
            'sessions' => collect($paginator->items())->map->toDisplayArray(),
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
            'filters' => $request->only(['impersonator_id', 'target_user_id', 'date_from', 'date_to', 'perPage']),
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
