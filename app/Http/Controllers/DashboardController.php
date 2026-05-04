<?php

namespace App\Http\Controllers;

use App\Models\Modules\Account;
use App\Models\Modules\SupportCase as SupportCase;
use App\Models\Modules\Contact;
use App\Models\Modules\Lead;
use App\Models\Modules\Opportunity;
use App\Models\Modules\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();

        return Inertia::render('Dashboard', [
            'stats'         => $this->getStats($user),
            'recordCounts'  => $this->getRecordCounts($user),
            'recentOrders'  => $this->getRecentOrders($user),
            'dealsOverTime' => $this->getOpportunitiesOverTime($user),
        ]);
    }

    /**
     * Three top metric cards:
     *  1. Pipeline value  — sum of amount on non-closed opportunities
     *  2. New leads       — leads created this month
     *  3. Open cases      — cases not yet closed
     */
    private function getStats(object $user): array
    {
        $now       = now();
        $thisMonth = [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
        $lastMonth = [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()];

        // Pipeline value
        $pipelineNow  = Opportunity::where('owner_id', $user->id)
            ->whereNotIn('sales_stage', ['Closed Won', 'Closed Lost'])
            ->sum('amount');

        $pipelineLast = Opportunity::where('owner_id', $user->id)
            ->whereNotIn('sales_stage', ['Closed Won', 'Closed Lost'])
            ->whereBetween('created_at', $lastMonth)
            ->sum('amount');

        // New leads this month
        $leadsNow  = Lead::where('owner_id', $user->id)
            ->whereBetween('created_at', $thisMonth)
            ->count();

        $leadsLast = Lead::where('owner_id', $user->id)
            ->whereBetween('created_at', $lastMonth)
            ->count();

        // Open cases (no closed_at date yet)
        $casesNow  = SupportCase::where('owner_id', $user->id)
            ->whereNull('closed_at')
            ->count();

        $casesLast = SupportCase::where('owner_id', $user->id)
            ->whereNull('closed_at')
            ->whereBetween('created_at', $lastMonth)
            ->count();

        return [
            'pipeline' => [
                'label'  => 'Pipeline Value',
                'value'  => (float) $pipelineNow,
                'change' => $this->percentChange($pipelineLast, $pipelineNow),
                'format' => 'currency',
            ],
            'leads' => [
                'label'  => 'New Leads',
                'value'  => $leadsNow,
                'change' => $this->percentChange($leadsLast, $leadsNow),
                'format' => 'number',
            ],
            'cases' => [
                'label'  => 'Open Cases',
                'value'  => $casesNow,
                'change' => $this->percentChange($casesLast, $casesNow),
                'format' => 'number',
            ],
        ];
    }

    /**
     * Record counts per core module owned by user.
     */
    private function getRecordCounts(object $user): array
    {
        $modules = [
            ['label' => 'Accounts',      'icon' => 'fa-solid fa-building',   'color' => 'primary', 'count' => Account::where('owner_id', $user->id)->count()],
            ['label' => 'Contacts',      'icon' => 'fa-solid fa-user',        'color' => 'info',    'count' => Contact::where('owner_id', $user->id)->count()],
            ['label' => 'Leads',         'icon' => 'fa-solid fa-bullseye',    'color' => 'warning', 'count' => Lead::where('owner_id', $user->id)->count()],
            ['label' => 'Opportunities', 'icon' => 'fa-solid fa-handshake',   'color' => 'success', 'count' => Opportunity::where('owner_id', $user->id)->count()],
        ];

        return [
            'total'   => array_sum(array_column($modules, 'count')),
            'modules' => $modules,
        ];
    }

    /**
     * Last 5 orders owned by user — replaces the "transactions" widget.
     */
    private function getRecentOrders(object $user): array
    {
        return Order::where('owner_id', $user->id)
            ->latest('order_date')
            ->limit(5)
            ->get(['order_number', 'total_amount', 'currency', 'status', 'order_date'])
            ->map(fn (Order $o) => [
                'order_number' => $o->order_number,
                'total_amount' => (float) $o->total_amount,
                'currency'     => $o->currency ?? 'USD',
                'status'       => $o->status,
                'date'         => $o->order_date,
            ])
            ->toArray();
    }

    /**
     * Opportunities grouped by month — last 12 months.
     * Prop is ready for a chart; placeholder rendered for now.
     */
    private function getOpportunitiesOverTime(object $user): array
    {
        return Opportunity::where('owner_id', $user->id)
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as value')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($r) => [
                'month' => $r->month,
                'count' => (int) $r->count,
                'value' => (float) $r->value,
            ])
            ->toArray();
    }

    private function percentChange(float|int $old, float|int $new): float|null
    {
        if ($old == 0) {
            return $new > 0 ? 100.0 : null;
        }

        return round((($new - $old) / $old) * 100, 2);
    }
}