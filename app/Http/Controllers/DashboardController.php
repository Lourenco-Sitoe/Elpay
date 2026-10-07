<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductAccess;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): RedirectResponse
    {
        $type = auth()->user()->type ?? 'client';

        return match ($type) {
            'admin' => redirect()->route('admin.dashboard'),
            'producer' => redirect()->route('producer.dashboard'),
            'affiliate' => redirect()->route('affiliate.dashboard'),
            default => redirect()->route('client.dashboard'),
        };
    }

    public function adminDashboard(): View
    {
        $totalUsers = class_exists(User::class) ? User::count() : 0;
        $totalProducts = class_exists(Product::class) ? Product::count() : 0;
        $totalSales = class_exists(Sale::class) ? Sale::where('status', 'paid')->count() : 0;
        $revenue = class_exists(Sale::class) ? (float) Sale::where('status', 'paid')->sum('final_price') : 0;

        $monthlySales = [];
        try {
            $rows = Sale::select(DB::raw('MONTH(created_at) as m'), DB::raw('SUM(final_price) as total'))
                ->where('status', 'paid')
                ->whereYear('created_at', now()->year)
                ->groupBy('m')->pluck('total', 'm')->toArray();
            for ($m = 1; $m <= 12; $m++) {
                $monthlySales[$m] = (float) ($rows[$m] ?? 0);
            }
        } catch (\Throwable) {
            $monthlySales = array_fill(1, 12, 0);
        }

        $salesByMethod = [];
        try {
            $salesByMethod = Sale::where('status', 'paid')->groupBy('payment_method')->selectRaw('payment_method, COUNT(*) as c')->pluck('c', 'payment_method')->toArray();
        } catch (\Throwable) {
        }

        $usersByType = [];
        try {
            $usersByType = User::groupBy('type')->selectRaw('type, COUNT(*) as c')->pluck('c', 'type')->toArray();
        } catch (\Throwable) {
        }

        $recentSales = collect();
        $topProducts = collect();
        $topAffiliates = collect();
        try {
            $recentSales = Sale::with(['product', 'buyer'])->latest()->limit(10)->get();
        } catch (\Throwable) {
        }
        try {
            $topProducts = Product::orderByDesc('sales_count')->limit(5)->get();
        } catch (\Throwable) {
            try {
                $topProducts = Product::withCount('sales')->orderByDesc('sales_count')->limit(5)->get();
            } catch (\Throwable) {
            }
        }
        try {
            $topAffiliates = class_exists(\App\Models\Affiliate::class)
                ? \App\Models\Affiliate::with('user')->orderByDesc('total_earned')->limit(5)->get()
                : collect();
        } catch (\Throwable) {
        }

        return view('dashboard.admin', [
            'totalUsers' => $totalUsers,
            'totalProducts' => $totalProducts,
            'totalSales' => $totalSales,
            'revenue' => $revenue,
            'monthlySales' => self::monthlyCollection($monthlySales),
            'salesByMethod' => $salesByMethod,
            'usersByType' => $usersByType,
            'recentSales' => $recentSales,
            'topProducts' => $topProducts,
            'topAffiliates' => $topAffiliates,
            'stats' => ['users' => $totalUsers, 'products' => $totalProducts, 'sales' => $totalSales, 'revenue' => round($revenue, 2)],
        ]);
    }

    protected static function monthlyCollection(array $byMonth): \Illuminate\Support\Collection
    {
        $names = [1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr', 5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago', 9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez'];
        $out = [];
        foreach ($names as $m => $label) {
            $out[] = ['month' => $label, 'total' => (float) ($byMonth[$m] ?? 0)];
        }

        return collect($out);
    }

    public function producerDashboard(): View
    {
        $userId = auth()->id();
        $period = request('period', '30');
        $wallet = request('wallet', 'all');
        [$from, $to, $days] = $this->periodRange($period);

        $products = collect();
        $salesCount = 0;
        $revenue = 0;
        $commissionsPaid = 0;
        $monthly = array_fill(1, 12, 0);
        $lost = 0;
        $lostCount = 0;
        $byWallet = ['mpesa' => 0, 'emola' => 0];
        $daily = [];

        try {
            $products = Product::where('producer_id', $userId)->latest()->limit(6)->get();
            $productIds = Product::where('producer_id', $userId)->pluck('id');
            $base = Sale::whereIn('product_id', $productIds)->whereBetween('created_at', [$from, $to]);
            $salesCount = (clone $base)->count();
            $revenue = (float) (clone $base)->where('status', 'paid')->sum('final_price');
            $lost = (float) (clone $base)->whereIn('status', ['cancelled', 'refunded'])->sum('final_price');
            $lostCount = (clone $base)->whereIn('status', ['cancelled', 'refunded'])->count();
            $rows = Sale::select(DB::raw('MONTH(created_at) as m'), DB::raw('SUM(final_price) as total'))
                ->whereIn('product_id', $productIds)->where('status', 'paid')
                ->whereYear('created_at', now()->year)->groupBy('m')->pluck('total', 'm')->toArray();
            foreach ($rows as $m => $t) {
                $monthly[$m] = (float) $t;
            }
            // Gráfico diário do período
            $drows = (clone $base)->select(DB::raw('DATE(created_at) as d'), DB::raw('SUM(CASE WHEN status="paid" THEN final_price ELSE 0 END) as total'))
                ->groupBy('d')->pluck('total', 'd')->toArray();
            $cursor = (clone $from)->startOfDay();
            $end = (clone $to)->startOfDay();
            while ($cursor <= $end) {
                $key = $cursor->toDateString();
                $daily[] = ['day' => $cursor->format('d/m'), 'total' => (float) ($drows[$key] ?? 0)];
                $cursor->addDay();
            }
            // Totais por carteira (vendas pagas)
            $wrows = (clone $base)->select('payment_method', DB::raw('SUM(final_price) as total'))
                ->where('status', 'paid')->groupBy('payment_method')->pluck('total', 'payment_method')->toArray();
            foreach (['mpesa', 'emola'] as $m) {
                $byWallet[$m] = (float) ($wrows[$m] ?? 0);
            }
            if (class_exists(Commission::class)) {
                $commissionsPaid = (float) Commission::whereIn('product_id', $productIds)->sum('amount');
            }
            $detail = Sale::with('product')->whereIn('product_id', $productIds)->whereBetween('created_at', [$from, $to]);
            if (in_array($wallet, ['mpesa', 'emola'])) {
                $detail->where('payment_method', $wallet);
            }
            $recentSales = (clone $detail)->latest()->limit(50)->get();
        } catch (\Throwable) {
        }

        return view('dashboard.producer', [
            'products' => $products,
            'salesCount' => $salesCount,
            'revenue' => $revenue,
            'commissionsPaid' => $commissionsPaid,
            'monthly' => $monthly,
            'monthlySales' => self::monthlyCollection($monthly),
            'recentSales' => $recentSales ?? collect(),
            'period' => $period,
            'wallet' => $wallet,
            'lost' => $lost ?? 0,
            'lostCount' => $lostCount ?? 0,
            'byWallet' => $byWallet,
            'daily' => collect($daily),
            'stats' => [
                'products' => $products instanceof \Illuminate\Support\Collection ? $products->count() : (int) Product::where('producer_id', $userId)->count(),
                'sales' => $salesCount,
                'revenue' => round($revenue, 2),
                'balance' => round((float) (auth()->user()->balance ?? 0), 2),
            ],
        ]);
    }

    /** Intervalo do filtro de período estilo Tutora. */
    private function periodRange(string $period): array
    {
        $to = now();
        return match ($period) {
            'today' => [now()->startOfDay(), $to, 1],
            '7' => [now()->subDays(6)->startOfDay(), $to, 7],
            'last_month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth(), now()->subMonthNoOverflow()->daysInMonth],
            default => [now()->subDays(29)->startOfDay(), $to, 30],
        };
    }

    public function affiliateDashboard(): View
    {
        $userId = auth()->id();
        $links = collect();
        $commissions = 0;
        $salesCount = 0;
        $clicks = 0;

        try {
            if (class_exists(\App\Models\Affiliate::class)) {
                $links = \App\Models\Affiliate::with('product')->where('user_id', $userId)->get();
                $salesCount = (int) $links->sum('total_sales');
            }
            if (class_exists(Commission::class)) {
                $commissions = (float) Commission::where('affiliate_id', $userId)->sum('amount');
                $recentCommissions = Commission::with(['product', 'sale'])->where('affiliate_id', $userId)->latest()->limit(10)->get();
            }
        } catch (\Throwable) {
            try {
                $commissions = class_exists(Commission::class) ? (float) Commission::where('affiliate_id', $userId)->sum('amount') : 0;
            } catch (\Throwable) {
            }
        }

        return view('dashboard.affiliate', [
            'links' => $links,
            'commissions' => $commissions,
            'salesCount' => $salesCount,
            'clicks' => $clicks,
            'recentCommissions' => $recentCommissions ?? collect(),
            'monthlySales' => self::monthlyCollection([]),
            'stats' => [
                'clicks' => 0,
                'conversions' => $salesCount,
                'commissions' => round($commissions, 2),
                'balance' => round((float) (auth()->user()->balance ?? 0), 2),
            ],
        ]);
    }

    public function clientDashboard(): View
    {
        $accesses = collect();
        try {
            $accesses = ProductAccess::with(['product', 'sale'])->where('user_id', auth()->id())->latest()->get();
        } catch (\Throwable) {
        }
        $spent = 0;
        try {
            $spent = (float) Sale::where('buyer_id', auth()->id())->where('status', 'paid')->sum('final_price');
        } catch (\Throwable) {
        }

        return view('dashboard.client', [
            'accesses' => $accesses,
            'purchases' => $accesses,
            'stats' => ['purchases' => $accesses->count(), 'spent' => round($spent, 2), 'items' => $accesses->count()],
        ]);
    }
}
