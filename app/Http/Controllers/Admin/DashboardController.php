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
    /**
     * Génère la clé de cache pour un utilisateur donné.
     */
    public static function cacheKey(\App\Models\User $user): string
    {
        $roleName = $user->roles->first()?->name ?? 'Admin';
        $roleKey  = strtolower(str_replace(' ', '_', trim($roleName)));
        return "dashboard.{$roleKey}.{$user->id}";
    }

    /**
     * Invalide le cache du dashboard.
     * À appeler dans SaleController, PurchaseController, ExpenseController,
     * ProductController, CashRegisterController après chaque store/update/destroy.
     */
    public static function clearCache(\App\Models\User $user): void
    {
        Cache::forget(self::cacheKey($user));
    }

    public function index()
    {
        $user = auth()->user();

        // Pas de cache — données toujours fraîches
        Cache::forget(self::cacheKey($user));

        $now        = now();
        $today      = $now->copy()->startOfDay();
        $weekStart  = $now->copy()->startOfWeek();
        $weekEnd    = $now->copy()->endOfWeek();
        $monthStart = $now->copy()->startOfMonth();
        $year       = $now->year;

        // ── PERMISSIONS ─────────────────────────────────────────────────
        $canFinancials = $user->can('view dashboard.financials');
        $canSales      = $user->can('view sales');
        $canPurchases  = $user->can('view purchases');
        $canExpenses   = $user->can('view expenses');
        $canProducts   = $user->can('view products');
        $canCustomers  = $user->can('view customers');
        $canSuppliers  = $user->can('view suppliers');
        $canCash       = $user->can('view cash_registers');
        $canUsers      = $user->can('view users');

        /*
        |----------------------------------------------------------------------
        | VENTES
        |----------------------------------------------------------------------
        */
        $sales = null;
        if ($canSales) {
            $sales = Sale::whereIn('status', ['confirmed', 'paid', 'validated'])
                ->selectRaw("
                    COALESCE(SUM(total_amount), 0)                                                          AS total,
                    COALESCE(SUM(CASE WHEN DATE(sale_date) = ?                              THEN total_amount END), 0) AS today,
                    COALESCE(SUM(CASE WHEN DATE(sale_date) BETWEEN ? AND ?                  THEN total_amount END), 0) AS week,
                    COALESCE(SUM(CASE WHEN DATE(sale_date) BETWEEN ? AND ?                  THEN total_amount END), 0) AS month
                ", [
                    $today->toDateString(),
                    $weekStart->toDateString(), $weekEnd->toDateString(),
                    $monthStart->toDateString(), $now->toDateString(),
                ])
                ->first();
        }

        $totalSales   = $canFinancials ? (float)($sales->total ?? 0) : null;
        $todaySales   = (float)($sales->today ?? 0);
        $weekSales    = (float)($sales->week  ?? 0);
        $monthlySales = $canFinancials ? (float)($sales->month ?? 0) : null;

        /*
        |----------------------------------------------------------------------
        | ACHATS
        |----------------------------------------------------------------------
        */
        $purchases = null;
        if ($canPurchases) {
            $purchases = Purchase::whereIn('status', ['confirmed', 'paid', 'validated'])
                ->selectRaw("
                    COALESCE(SUM(total_amount), 0)                                                           AS total,
                    COALESCE(SUM(CASE WHEN DATE(purchase_date) = ?                           THEN total_amount END), 0) AS today,
                    COALESCE(SUM(CASE WHEN DATE(purchase_date) BETWEEN ? AND ?               THEN total_amount END), 0) AS month
                ", [
                    $today->toDateString(),
                    $monthStart->toDateString(), $now->toDateString(),
                ])
                ->first();
        }

        $totalPurchases = $canFinancials ? (float)($purchases->total ?? 0) : null;
        $monthPurchases = $canFinancials ? (float)($purchases->month ?? 0) : null;

        /*
        |----------------------------------------------------------------------
        | DÉPENSES
        | Seules les dépenses avec status = 'approved' sont comptabilisées.
        | Si vous souhaitez exclure certaines catégories, ajoutez un filtre ici.
        |----------------------------------------------------------------------
        */
        $expensesTotal   = 0;
        $monthlyExpenses = 0;
        if ($canExpenses) {
            $expensesTotal = Expense::where('status', 'approved')
                ->sum('amount');

            $monthlyExpenses = Expense::where('status', 'approved')
                ->whereBetween('expense_date', [$monthStart->toDateString(), $now->toDateString()])
                ->sum('amount');
        }

        /*
        |----------------------------------------------------------------------
        | BÉNÉFICE NET
        | Formule : Ventes - Achats - Dépenses
        | Un bénéfice négatif signifie que vos dépenses dépassent vos revenus.
        | Vérifiez vos dépenses approuvées si le montant vous semble anormal.
        |----------------------------------------------------------------------
        */
        $profit = null;
        if ($canFinancials) {
            $profit = ($totalSales ?? 0) - ($totalPurchases ?? 0) - $expensesTotal;
        }

        /*
        |----------------------------------------------------------------------
        | STOCK
        |----------------------------------------------------------------------
        */
        $lowStockProducts   = 0;
        $outOfStockProducts = 0;
        if ($canProducts) {
            $lowStockProducts   = Product::whereColumn('stock_quantity', '<=', 'minimum_stock')
                ->where('is_active', true)
                ->count();
            $outOfStockProducts = Product::where('stock_quantity', '<=', 0)
                ->where('is_active', true)
                ->count();
        }

        /*
        |----------------------------------------------------------------------
        | IMPAYÉS / DÉBITEURS
        |----------------------------------------------------------------------
        */
        $unpaidSales     = $canSales
            ? Sale::where('due_amount', '>', 0)
                ->whereIn('status', ['confirmed', 'validated'])
                ->count()
            : 0;

        $unpaidPurchases = $canPurchases
            ? Purchase::where('due_amount', '>', 0)
                ->whereIn('status', ['confirmed', 'validated'])
                ->count()
            : 0;

        $debtCustomers   = $canCustomers
            ? Customer::where('current_balance', '>', 0)->count()
            : 0;

        $creditSuppliers = $canSuppliers
            ? Supplier::where('current_balance', '>', 0)->count()
            : 0;

        /*
        |----------------------------------------------------------------------
        | CAISSE OUVERTE
        |----------------------------------------------------------------------
        */
        $openCashRegister = null;
        if ($canCash) {
            $cr = CashRegister::where('status', 'open')
                ->first(['id', 'name', 'opening_balance', 'total_in', 'total_out']);
            if ($cr) {
                $openCashRegister = [
                    'id'              => $cr->id,
                    'name'            => $cr->name,
                    'opening_balance' => (float) $cr->opening_balance,
                    'total_in'        => (float) $cr->total_in,
                    'total_out'       => (float) $cr->total_out,
                    'current_balance' =>
                        (float) $cr->opening_balance
                        + (float) $cr->total_in
                        - (float) $cr->total_out,
                ];
            }
        }

        /*
        |----------------------------------------------------------------------
        | UTILISATEURS ACTIFS
        |----------------------------------------------------------------------
        */
        $activeUsers = $canUsers
            ? User::where('is_active', true)->count()
            : 0;

        /*
        |----------------------------------------------------------------------
        | DONNÉES RÉCENTES
        |----------------------------------------------------------------------
        */
        $recentSales = $canSales
            ? Sale::latest()->limit(6)->get(['reference', 'total_amount', 'created_at'])->toArray()
            : [];

        $recentPurchases = $canPurchases
            ? Purchase::latest()->limit(6)->get(['reference', 'total_amount', 'created_at'])->toArray()
            : [];

        /*
        |----------------------------------------------------------------------
        | GRAPHIQUES — uniquement avec accès financiers
        |----------------------------------------------------------------------
        */
        $salesChart = ($canFinancials && $canSales)
            ? Sale::selectRaw('MONTH(sale_date) m, SUM(total_amount) t')
                ->whereYear('sale_date', $year)
                ->whereIn('status', ['confirmed', 'paid', 'validated'])
                ->groupBy('m')
                ->pluck('t', 'm')
                ->toArray()
            : [];

        $purchaseChart = ($canFinancials && $canPurchases)
            ? Purchase::selectRaw('MONTH(purchase_date) m, SUM(total_amount) t')
                ->whereYear('purchase_date', $year)
                ->whereIn('status', ['confirmed', 'paid', 'validated'])
                ->groupBy('m')
                ->pluck('t', 'm')
                ->toArray()
            : [];

        // Graphique ventes pour les caissiers (sans totaux globaux)
        $salesChartCaissier = (!$canFinancials && $canSales)
            ? Sale::selectRaw('MONTH(sale_date) m, SUM(total_amount) t')
                ->whereYear('sale_date', $year)
                ->whereIn('status', ['confirmed', 'paid', 'validated'])
                ->groupBy('m')
                ->pluck('t', 'm')
                ->toArray()
            : [];

        /*
        |----------------------------------------------------------------------
        | DONNÉES ENVOYÉES À LA VUE
        |----------------------------------------------------------------------
        */
        return view('admin.dashboard', [
            // Rôle
            'role'               => $user->roles->first()?->name ?? 'Admin',

            // Accès
            'canFinancials'      => $canFinancials,

            // Ventes
            'totalSales'         => $totalSales,
            'todaySales'         => $todaySales,
            'weekSales'          => $weekSales,
            'monthlySales'       => $monthlySales,

            // Achats
            'totalPurchases'     => $totalPurchases,
            'monthPurchases'     => $monthPurchases,

            // Dépenses
            'totalExpenses'      => $canFinancials ? $expensesTotal    : null,
            'monthlyExpenses'    => $canFinancials ? $monthlyExpenses   : null,

            // Bénéfice net
            // Négatif = vos dépenses dépassent vos ventes.
            // Pour corriger l'affichage, vérifiez la table `expenses`
            // et assurez-vous que seules les dépenses réelles sont approuvées.
            'profit'             => $profit,

            // Stock
            'lowStockProducts'   => $lowStockProducts,
            'outOfStockProducts' => $outOfStockProducts,

            // Impayés / débiteurs
            'unpaidSales'        => $unpaidSales,
            'unpaidPurchases'    => $unpaidPurchases,
            'debtCustomers'      => $debtCustomers,
            'creditSuppliers'    => $creditSuppliers,

            // Caisse
            'openCashRegister'   => $openCashRegister,

            // Utilisateurs
            'activeUsers'        => $activeUsers,

            // Données récentes
            'recentSales'        => $recentSales,
            'recentPurchases'    => $recentPurchases,

            // Graphiques
            'salesChart'         => $salesChart,
            'purchaseChart'      => $purchaseChart,
            'salesChartCaissier' => $salesChartCaissier,
        ]);
    }
}