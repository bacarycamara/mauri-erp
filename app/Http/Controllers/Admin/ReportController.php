<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\CashRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | CENTRE DES RAPPORTS
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        return view('admin.reports.index');
    }

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD GLOBAL ERP
    |--------------------------------------------------------------------------
    */
public function dashboard()
{
    /*
    |--------------------------------------------------------------------------
    | SALES QUERY
    |--------------------------------------------------------------------------
    */

    $salesQuery = Sale::whereIn('status', [
        'confirmed',
        'partial',
        'paid'
    ]);

    $totalSales = (clone $salesQuery)->sum('total_amount');


    /*
    |--------------------------------------------------------------------------
    | SALES TODAY
    |--------------------------------------------------------------------------
    */

    $todaySales = (clone $salesQuery)
        ->whereDate('sale_date', today())
        ->sum('total_amount');


    /*
    |--------------------------------------------------------------------------
    | PURCHASE QUERY
    |--------------------------------------------------------------------------
    */

    $purchaseQuery = Purchase::whereIn('status', [
        'confirmed',
        'partial',
        'paid'
    ]);

    $totalPurchases = (clone $purchaseQuery)->sum('total_amount');


    /*
    |--------------------------------------------------------------------------
    | EXPENSE QUERY
    |--------------------------------------------------------------------------
    */

    $expenseQuery = Expense::where('status', 'approved');

    $totalExpenses = (clone $expenseQuery)->sum('amount');


    /*
    |--------------------------------------------------------------------------
    | CASH FLOW
    |--------------------------------------------------------------------------
    */

    $paymentQuery = Payment::where('status', 'confirmed');

    $totalIn = (clone $paymentQuery)
        ->where('type', 'in')
        ->sum('amount');

    $totalOut = (clone $paymentQuery)
        ->where('type', 'out')
        ->sum('amount');


    /*
    |--------------------------------------------------------------------------
    | PROFITS
    |--------------------------------------------------------------------------
    */

    $profitBrut = $totalSales - $totalPurchases;

    $profitNet = $totalSales - ($totalPurchases + $totalExpenses);


    /*
    |--------------------------------------------------------------------------
    | LOW STOCK ALERT
    |--------------------------------------------------------------------------
    */

    $lowStockProducts = Product::where('is_active', true)
        ->whereColumn('stock_quantity', '<=', 'minimum_stock')
        ->count();


    /*
    |--------------------------------------------------------------------------
    | MONTHLY SALES
    |--------------------------------------------------------------------------
    */

    $monthlySales = Sale::selectRaw("
            MONTH(sale_date) as month,
            SUM(total_amount) as total
        ")
        ->whereYear('sale_date', now()->year)
        ->whereIn('status', ['confirmed','partial','paid'])
        ->groupBy('month')
        ->pluck('total','month')
        ->toArray();


    /*
    |--------------------------------------------------------------------------
    | MONTHLY PURCHASES
    |--------------------------------------------------------------------------
    */

    $monthlyPurchases = Purchase::selectRaw("
            MONTH(purchase_date) as month,
            SUM(total_amount) as total
        ")
        ->whereYear('purchase_date', now()->year)
        ->whereIn('status', ['confirmed','partial','paid'])
        ->groupBy('month')
        ->pluck('total','month')
        ->toArray();


    /*
    |--------------------------------------------------------------------------
    | MONTHLY EXPENSES
    |--------------------------------------------------------------------------
    */

    $monthlyExpenses = Expense::selectRaw("
            MONTH(expense_date) as month,
            SUM(amount) as total
        ")
        ->whereYear('expense_date', now()->year)
        ->where('status', 'approved')
        ->groupBy('month')
        ->pluck('total','month')
        ->toArray();


    /*
    |--------------------------------------------------------------------------
    | MONTHLY PROFIT CALCULATION
    |--------------------------------------------------------------------------
    */

    $monthlyProfit = [];

    for ($i = 1; $i <= 12; $i++) {

        $sales = $monthlySales[$i] ?? 0;
        $purchases = $monthlyPurchases[$i] ?? 0;
        $expenses = $monthlyExpenses[$i] ?? 0;

        $monthlyProfit[$i] = $sales - ($purchases + $expenses);
    }


    /*
    |--------------------------------------------------------------------------
    | RETURN VIEW
    |--------------------------------------------------------------------------
    */

    return view('admin.reports.dashboard', compact(
        'totalSales',
        'todaySales',
        'totalPurchases',
        'totalExpenses',
        'totalIn',
        'totalOut',
        'profitBrut',
        'profitNet',
        'lowStockProducts',
        'monthlyProfit'
    ));
}
    /*
    |--------------------------------------------------------------------------
    | RAPPORT FINANCIER GLOBAL
    |--------------------------------------------------------------------------
    */
public function financial(Request $request)
{
    /*
    |--------------------------------------------------------------------------
    | DATE RANGE
    |--------------------------------------------------------------------------
    */

    [$from, $to] = $this->getDateRange($request);


    /*
    |--------------------------------------------------------------------------
    | SALES QUERY
    |--------------------------------------------------------------------------
    */

    $salesQuery = Sale::whereIn('status', [
        'confirmed',
        'partial',
        'paid'
    ])->whereBetween('sale_date', [$from, $to]);


    /*
    |--------------------------------------------------------------------------
    | PURCHASE QUERY
    |--------------------------------------------------------------------------
    */

    $purchaseQuery = Purchase::whereIn('status', [
        'confirmed',
        'partial',
        'paid'
    ])->whereBetween('purchase_date', [$from, $to]);


    /*
    |--------------------------------------------------------------------------
    | EXPENSE QUERY
    |--------------------------------------------------------------------------
    */

    $expenseQuery = Expense::approved()
        ->whereBetween('expense_date', [$from, $to]);


    /*
    |--------------------------------------------------------------------------
    | PAYMENT QUERY
    |--------------------------------------------------------------------------
    */

    $paymentQuery = Payment::confirmed()
        ->whereBetween('payment_date', [$from, $to]);


    /*
    |--------------------------------------------------------------------------
    | KPI CALCULATIONS
    |--------------------------------------------------------------------------
    */

    $salesTotal = (clone $salesQuery)->sum('total_amount');

    $purchaseTotal = (clone $purchaseQuery)->sum('total_amount');

    $expenseTotal = (clone $expenseQuery)->sum('amount');


    /*
    |--------------------------------------------------------------------------
    | CASH FLOW
    |--------------------------------------------------------------------------
    */

    $totalIn = (clone $paymentQuery)
        ->where('type', 'in')
        ->sum('amount');

    $totalOut = (clone $paymentQuery)
        ->where('type', 'out')
        ->sum('amount');


    /*
    |--------------------------------------------------------------------------
    | PROFITS
    |--------------------------------------------------------------------------
    */

    $profitBrut = $salesTotal - $purchaseTotal;

    $profitNet = $salesTotal - ($purchaseTotal + $expenseTotal);


    /*
    |--------------------------------------------------------------------------
    | MONTHLY SALES GRAPH
    |--------------------------------------------------------------------------
    */

    $monthlySales = Sale::selectRaw("
            MONTH(sale_date) as month,
            SUM(total_amount) as total
        ")
        ->whereYear('sale_date', Carbon::parse($from)->year)
        ->whereIn('status', ['confirmed','partial','paid'])
        ->groupBy('month')
        ->pluck('total', 'month')
        ->toArray();


    /*
    |--------------------------------------------------------------------------
    | RETURN VIEW
    |--------------------------------------------------------------------------
    */

    return view('admin.reports.financial', compact(
        'from',
        'to',
        'salesTotal',
        'purchaseTotal',
        'expenseTotal',
        'totalIn',
        'totalOut',
        'profitBrut',
        'profitNet',
        'monthlySales'
    ));
}

    /*
    |--------------------------------------------------------------------------
    | RAPPORT VENTES
    |--------------------------------------------------------------------------
    */
  public function sales(Request $request)
{
    [$from, $to] = $this->getDateRange($request);

    $query = Sale::whereIn('status', [
        'confirmed',
        'partial',
        'paid'
    ])->whereBetween('sale_date', [$from,$to]);

    $sales = $query->latest()->paginate(20);
    $total = $query->sum('total_amount');

    return view('admin.reports.sales',
        compact('sales','total','from','to')
    );
}

    /*
    |--------------------------------------------------------------------------
    | RAPPORT ACHATS
    |--------------------------------------------------------------------------
    */
    public function purchases(Request $request)
    {
        [$from, $to] = $this->getDateRange($request);

        $query = Purchase::whereIn('status',['confirmed','partial','paid'])
            ->whereBetween('purchase_date', [$from,$to]);

        $purchases = $query->latest()->paginate(20);
        $total = $query->sum('total_amount');

        return view('admin.reports.purchases',
            compact('purchases','total','from','to')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RAPPORT DEPENSES
    |--------------------------------------------------------------------------
    */
    public function expenses(Request $request)
    {
        [$from, $to] = $this->getDateRange($request);

        $query = Expense::approved()
            ->whereBetween('expense_date', [$from,$to]);

        $expenses = $query->latest()->paginate(20);
        $total = $query->sum('amount');

        return view('admin.reports.expenses',
            compact('expenses','total','from','to')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RAPPORT STOCK
    |--------------------------------------------------------------------------
    */
   public function stock()
{
    $products = Product::with('category')
        ->orderBy('stock_quantity','asc')
        ->paginate(10); //  pagination

    return view('admin.reports.stock', compact('products'));
}

    /*
    |--------------------------------------------------------------------------
    | RAPPORT CAISSES
    |--------------------------------------------------------------------------
    */
    public function cashRegisters()
    {
        $registers = CashRegister::withCount('transactions')
            ->latest()
            ->get();

        return view('admin.reports.cash-registers', compact('registers'));
    }

    /*
    |--------------------------------------------------------------------------
    | TOP PRODUITS (OPTIMISÉ)
    |--------------------------------------------------------------------------
    */
    public function topProducts()
    {
        $products = DB::table('sale_items')
            ->join('products','products.id','=','sale_items.product_id')
            ->select(
                'products.name',
                DB::raw('SUM(quantity) as total_qty')
            )
            ->groupBy('products.name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        return view('admin.reports.top-products', compact('products'));
    }

    /*
    |--------------------------------------------------------------------------
    | FILTRE DATE CENTRALISÉ ERP
    |--------------------------------------------------------------------------
    */
  private function getDateRange(Request $request)
{
    // si filtre utilisateur
    if ($request->filled('from') && $request->filled('to')) {
        return [$request->from, $request->to];
    }

    // sinon → prendre toute l'année courante
    return [
        now()->startOfYear()->toDateString(),
        now()->endOfYear()->toDateString(),
    ];
}
}