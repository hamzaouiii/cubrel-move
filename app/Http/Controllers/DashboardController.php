<?php

namespace App\Http\Controllers;

use App\Models\Modules\Account;
use App\Models\Modules\SupportCase as SupportCase;
use App\Models\Modules\Contact;
use App\Models\Modules\Lead;
use App\Models\Modules\Deal;
use App\Models\Modules\Order;
use App\Models\Modules\Invoice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use App\Services\Users\OwnershipService;
use App\Models\DropdownList;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();

        return Inertia::render('Dashboard/Index', [
            'leads'         => $this->getLastLeads($user)->toArray(),
            'recordCounts'  => $this->getRecordCounts($user),
            'ownedRecords'  => $this->getOwnedRecords($user),
            'recentOrders'  => $this->getRecentOrders($user),
            'dealsOverTime' => $this->getDealsOverTime($user),
            'dealStages'      => $this->getDealStages($user), 
            // 'invoiceOverview' => $this->getInvoiceOverview($user),
        ]);
    }

    private function getLastLeads(object $user) : Collection
    {
          return Lead::where('owner_id', $user->id)
        ->orderBy('created_at')
        ->limit(10)
        ->get();
    }
    private function getOwnedRecords(object $user) : Collection
    {
      return  app(OwnershipService::class)->getRecordsByUser($user->id);

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
            ['label' => 'Deals', 'icon' => 'fa-solid fa-handshake',   'color' => 'success', 'count' => Deal::where('owner_id', $user->id)->count()],
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
      $dropdown_list = DropdownList::get('orders_status_list')->values;
        return Order::where('owner_id', $user->id)
            ->latest('order_date')
            ->limit(5)
            ->get(['id','order_number', 'total_amount', 'currency', 'status', 'order_date'])
            ->map(fn (Order $o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'total_amount' => (float) $o->total_amount,
                'currency'     => $o->currency ?? 'EUR',
                'status'       => $this->getItemforValue($dropdown_list, $o->status),
                'date'         => $o->order_date,
            ])
            ->toArray();
    }

    /**
     * Deals grouped by month — last 12 months.
     * Prop is ready for a chart; placeholder rendered for now.
     */
    private function getDealsOverTime(object $user): array
    {
        return Deal::where('owner_id', $user->id)
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

    /**
     * Group deals into won, lost, and open for the doughnut chart.
     */
    private function getDealStages(object $user): array
    {
        $deals = Deal::where('owner_id', $user->id)
            ->select('sales_stage', DB::raw('COUNT(*) as total'))
            ->groupBy('sales_stage')
            ->pluck('total', 'sales_stage');

        $won = $deals->get('closed_won', 0);
        $lost = $deals->get('closed_lost', 0);
        
        // Sum of all other stages represents "open"
        $open = $deals->except(['Closed Won', 'Closed Lost'])->sum();

        return [
            'won'  => (int) $won,
            'lost' => (int) $lost,
            'open' => (int) $open,
        ];
    }

    /**
     * Group invoices by status to generate the invoice overview bars.
     */
    private function getInvoiceOverview(object $user): array
    {
        $invoices = Invoice::where('owner_id', $user->id)
            ->select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as amount'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $statusMap = [
            'overdue'        => 'Overdue',
            'not_paid'       => 'Unpaid',
            'partially_paid' => 'Partially Paid',
            'fully_paid'     => 'Fully Paid',
            'draft'          => 'Draft',
        ];

        $overview = [];

        foreach ($statusMap as $key => $label) {
            $stat = $invoices->get($key);
            
            // Only return statuses that have records, or return all if you prefer placeholders
            if ($stat && $stat->count > 0) {
                $overview[] = [
                    'key'    => $key,
                    'label'  => $label,
                    'count'  => (int) $stat->count,
                    'amount' => (float) $stat->amount,
                ];
            }
        }

        // Sort descending by amount so the largest bars sit at the top
        usort($overview, fn($a, $b) => $b['amount'] <=> $a['amount']);

        return $overview;
    }

    private function percentChange(float|int $old, float|int $new): float|null
    {
        if ($old == 0) {
            return $new > 0 ? 100.0 : null;
        }

        return round((($new - $old) / $old) * 100, 2);
    }

    private static function getItemforValue(Array $list, String $value): Array
    {
      foreach ($list as $item) {
        if($item['value'] === $value){
          return $item;
        }
      }
      return [];
    }
}