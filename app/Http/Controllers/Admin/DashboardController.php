<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{
    Sale,
    Purchase,
    Product,
    Expense,
    CashRegister,
    User,
    Customer,
    Supplier
};
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $user     = auth()->user();
        $roleName = $user->roles->first()?->name ?? 'Admin';
        $roleKey  = strtolower(str_replace(' ', '_', trim($roleName)));
        $cacheKey = "dashboard.{$roleKey}.{$user->id}";

        $data = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($user) {

            $now        = now();
            $today      = $now->copy()->startOfDay();
            $weekStart  = $now->copy()->startOfWeek();
            $weekEnd    = $now->copy()->endOfWeek();
            $monthStart = $now->copy()->startOfMonth();
            $year       = $now->year;

            // ── PERMISSION CLÉ ─────────────────────────────────────
            // true  → Admin, Gestionnaire, Comptable
            // false → Caissier, Magasinier, Commercial, Livreur
            $canFinancials = $user->can('view dashboard.financials');

            /*
            |----------------------------------------------------------
            | SALES
            |----------------------------------------------------------
            */
            $sales = null;
            if ($user->can('view sales')) {
                $sales = Sale::whereIn('status', ['confirmed', 'paid', 'validated'])
                    ->selectRaw("
                        COALESCE(SUM(total_amount),0) as total,
                        COALESCE(SUM(CASE WHEN sale_date >= ? THEN total_amount END),0) as today,
                        COALESCE(SUM(CASE WHEN sale_date BETWEEN ? AND ? THEN total_amount END),0) as week,
                        COALESCE(SUM(CASE WHEN sale_date BETWEEN ? AND ? THEN total_amount END),0) as month
                    ", [$today, $weekStart, $weekEnd, $monthStart, $now])
                    ->first();
            }

            /*
            |----------------------------------------------------------
            | PURCHASES
            |----------------------------------------------------------
            */
            $purchases = null;
            if ($user->can('view purchases')) {
                $purchases = Purchase::whereIn('status', ['confirmed', 'paid', 'validated'])
                    ->selectRaw("
                        COALESCE(SUM(total_amount),0) as total,
                        COALESCE(SUM(CASE WHEN purchase_date >= ? THEN total_amount END),0) as today,
                        COALESCE(SUM(CASE WHEN purchase_date BETWEEN ? AND ? THEN total_amount END),0) as month
                    ", [$today, $monthStart, $now])
                    ->first();
            }

            /*
            |----------------------------------------------------------
            | EXPENSES
            |----------------------------------------------------------
            */
            $expensesTotal   = 0;
            $monthlyExpenses = 0;
            if ($user->can('view expenses')) {
                $expensesTotal   = Expense::where('status', 'approved')->sum('amount');
                $monthlyExpenses = Expense::where('status', 'approved')
                    ->whereBetween('expense_date', [$monthStart, $now])
                    ->sum('amount');
            }

            /*
            |----------------------------------------------------------
            | STOCK
            |----------------------------------------------------------
            */
            $lowStockProducts   = 0;
            $outOfStockProducts = 0;
            if ($user->can('view products')) {
                $lowStockProducts   = Product::whereColumn('stock_quantity', '<=', 'minimum_stock')
                    ->where('is_active', true)->count();
                $outOfStockProducts = Product::where('stock_quantity', '<=', 0)
                    ->where('is_active', true)->count();
            }

            /*
            |----------------------------------------------------------
            | UNPAID / DEBTORS
            |----------------------------------------------------------
            */
            $unpaidSales     = $user->can('view sales')
                ? Sale::where('due_amount', '>', 0)->count() : 0;
            $unpaidPurchases = $user->can('view purchases')
                ? Purchase::where('due_amount', '>', 0)->count() : 0;
            $debtCustomers   = $user->can('view customers')
                ? Customer::where('current_balance', '>', 0)->count() : 0;
            $creditSuppliers = $user->can('view suppliers')
                ? Supplier::where('current_balance', '>', 0)->count() : 0;

            /*
            |----------------------------------------------------------
            | CASH REGISTER
            |----------------------------------------------------------
            */
            $openCashRegister = null;
            if ($user->can('view cash_registers')) {
                $cr = CashRegister::where('status', 'open')
                    ->first(['id','name','opening_balance','total_in','total_out']);
                if ($cr) {
                    $openCashRegister = [
                        'id'              => $cr->id,
                        'name'            => $cr->name,
                        'opening_balance' => (float) $cr->opening_balance,
                        'total_in'        => (float) $cr->total_in,
                        'total_out'       => (float) $cr->total_out,
                        'current_balance' =>
                            (float)$cr->opening_balance +
                            (float)$cr->total_in -
                            (float)$cr->total_out,
                    ];
                }
            }

            /*
            |----------------------------------------------------------
            | USERS
            |----------------------------------------------------------
            */
            $activeUsers = $user->can('view users')
                ? User::where('is_active', true)->count() : 0;

            /*
            |----------------------------------------------------------
            | RECENT DATA
            |----------------------------------------------------------
            */
            $recentSales = $user->can('view sales')
                ? Sale::latest()->limit(6)
                    ->get(['reference','total_amount','created_at'])->toArray()
                : [];

            $recentPurchases = $user->can('view purchases')
                ? Purchase::latest()->limit(6)
                    ->get(['reference','total_amount','created_at'])->toArray()
                : [];

            /*
            |----------------------------------------------------------
            | CHARTS — uniquement avec accès financials
            |----------------------------------------------------------
            */
            $salesChart = ($canFinancials && $user->can('view sales'))
                ? Sale::selectRaw('MONTH(sale_date) m, SUM(total_amount) t')
                    ->whereYear('sale_date', $year)
                    ->whereIn('status', ['confirmed','paid','validated'])
                    ->groupBy('m')->pluck('t','m')->toArray()
                : [];

            $purchaseChart = ($canFinancials && $user->can('view purchases'))
                ? Purchase::selectRaw('MONTH(purchase_date) m, SUM(total_amount) t')
                    ->whereYear('purchase_date', $year)
                    ->whereIn('status', ['confirmed','paid','validated'])
                    ->groupBy('m')->pluck('t','m')->toArray()
                : [];

            // Charts ventes aussi pour caissier (sans totaux globaux)
            $salesChartCaissier = (!$canFinancials && $user->can('view sales'))
                ? Sale::selectRaw('MONTH(sale_date) m, SUM(total_amount) t')
                    ->whereYear('sale_date', $year)
                    ->whereIn('status', ['confirmed','paid','validated'])
                    ->groupBy('m')->pluck('t','m')->toArray()
                : [];

            return [
                // Financials (Admin / Gestionnaire / Comptable)
                'canFinancials'      => $canFinancials,
                'totalSales'         => $canFinancials ? ($sales->total    ?? 0) : null,
                'todaySales'         => $sales->today  ?? 0,   // caissier en a besoin
                'weekSales'          => $sales->week   ?? 0,   // caissier en a besoin
                'monthlySales'       => $canFinancials ? ($sales->month    ?? 0) : null,
                'totalPurchases'     => $canFinancials ? ($purchases->total ?? 0) : null,
                'todayPurchases'     => $canFinancials ? ($purchases->today ?? 0) : null,
                'monthPurchases'     => $canFinancials ? ($purchases->month ?? 0) : null,
                'totalExpenses'      => $canFinancials ? $expensesTotal           : null,
                'monthlyExpenses'    => $canFinancials ? $monthlyExpenses         : null,
                'profit'             => $canFinancials
                    ? (($sales->total ?? 0) - (($purchases->total ?? 0) + $expensesTotal))
                    : null,

                // Opérationnel (selon permissions)
                'lowStockProducts'   => $lowStockProducts,
                'outOfStockProducts' => $outOfStockProducts,
                'unpaidSales'        => $unpaidSales,
                'unpaidPurchases'    => $unpaidPurchases,
                'debtCustomers'      => $debtCustomers,
                'creditSuppliers'    => $creditSuppliers,
                'recentSales'        => $recentSales,
                'recentPurchases'    => $recentPurchases,
                'openCashRegister'   => $openCashRegister,
                'activeUsers'        => $activeUsers,

                // Charts
                'salesChart'         => $salesChart,
                'purchaseChart'      => $purchaseChart,
                'salesChartCaissier' => $salesChartCaissier,
            ];
        });

        $data['role'] = $roleName;

        return view('admin.dashboard', $data);
    }
}