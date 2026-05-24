<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\CashRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class SaleController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = Sale::with(['customer', 'items.product']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('reference', 'like', '%' . $request->search . '%')
                  ->orWhereHas('customer', function ($c) use ($request) {
                      $c->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sales = $query->latest()->paginate(15)->withQueryString();

        return view('admin.sales.index', compact('sales'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $products  = Product::where('is_active', 1)->get();

        return view('admin.sales.create', compact('customers', 'products'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id'           => 'required|exists:customers,id',
            'sale_date'             => 'required|date',
            'items'                 => 'required|array|min:1',
            'items.*.product_id'    => 'required|exists:products,id',
            'items.*.quantity'      => 'required|numeric|min:0.01',
            'items.*.unit_price'    => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                if ($product->type === 'physical') {
                    if ($product->stock_quantity < $item['quantity']) {
                        throw ValidationException::withMessages([
                            'stock' => "Stock insuffisant pour le produit : {$product->name}"
                        ]);
                    }
                }
            }

            $sale = Sale::create([
                'customer_id' => $request->customer_id,
                'sale_date'   => $request->sale_date,
                'notes'       => $request->notes,
                'status'      => 'draft',
                'paid_amount' => 0,
            ]);

            foreach ($request->items as $item) {
                SaleItem::create([
                    'sale_id'       => $sale->id,
                    'product_id'    => $item['product_id'],
                    'quantity'      => $item['quantity'],
                    'unit_price'    => $item['unit_price'],
                    'vat_rate'      => $item['vat_rate'] ?? 0,
                    'discount_rate' => $item['discount_rate'] ?? 0,
                ]);
            }

            $sale->calculateTotals();
        });

        DashboardController::clearCache(auth()->user());

        return redirect()
            ->route('admin.sales.index')
            ->with('success', 'Vente créée avec succès.');
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */
    public function show(Sale $sale)
    {
        $sale->load(['customer', 'items.product', 'payments.cashRegister']);

        $totalPaid = $sale->payments->where('status', 'confirmed')->sum('amount');
        $remaining = max(0, $sale->total_amount - $totalPaid);

        return view('admin.sales.show', compact('sale', 'totalPaid', 'remaining'));
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
    public function edit(Sale $sale)
    {
        if ($sale->status !== 'draft') {
            return back()->with('error', 'Impossible de modifier une vente confirmée.');
        }

        $customers = Customer::orderBy('name')->get();
        $products  = Product::where('is_active', 1)->get();

        $sale->load('items.product');

        return view('admin.sales.edit', compact('sale', 'customers', 'products'));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Sale $sale)
    {
        if ($sale->status !== 'draft') {
            return back()->with('error', 'Modification non autorisée.');
        }

        $request->validate([
            'customer_id'           => 'required|exists:customers,id',
            'sale_date'             => 'required|date',
            'items'                 => 'required|array|min:1',
            'items.*.product_id'    => 'required|exists:products,id',
            'items.*.quantity'      => 'required|numeric|min:0.01',
            'items.*.unit_price'    => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $sale) {
            $sale->update([
                'customer_id' => $request->customer_id,
                'sale_date'   => $request->sale_date,
                'notes'       => $request->notes,
            ]);

            $sale->items()->delete();

            foreach ($request->items as $item) {
                SaleItem::create([
                    'sale_id'       => $sale->id,
                    'product_id'    => $item['product_id'],
                    'quantity'      => $item['quantity'],
                    'unit_price'    => $item['unit_price'],
                    'vat_rate'      => $item['vat_rate'] ?? 0,
                    'discount_rate' => $item['discount_rate'] ?? 0,
                ]);
            }

            $sale->calculateTotals();
        });

        DashboardController::clearCache(auth()->user());

        return redirect()
            ->route('admin.sales.index')
            ->with('success', 'Vente mise à jour.');
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIRM
    |--------------------------------------------------------------------------
    */
    public function confirm(Sale $sale)
    {
        try {
            $sale->confirm();
            DashboardController::clearCache(auth()->user());
            return back()->with('success', 'Vente confirmée.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | REGISTER PAYMENT
    |--------------------------------------------------------------------------
    */
    public function registerPayment(Request $request, Sale $sale)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01'
        ]);

        $cashRegister = CashRegister::where('status', 'open')->first();

        if (!$cashRegister) {
            return back()->with('error', 'Aucune caisse ouverte.');
        }

        try {
            $sale->registerPayment($request->amount, $cashRegister);
            DashboardController::clearCache(auth()->user());
            return back()->with('success', 'Paiement enregistré.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CANCEL
    |--------------------------------------------------------------------------
    */
    public function cancel(Sale $sale)
    {
        if (in_array($sale->status, ['paid', 'cancelled'])) {
            return back()->with('error', 'Annulation non autorisée.');
        }

        $sale->update(['status' => 'cancelled']);
        DashboardController::clearCache(auth()->user());

        return back()->with('success', 'Vente annulée.');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function destroy(Sale $sale)
    {
        if ($sale->status !== 'draft') {
            return back()->with('error', 'Suppression non autorisée.');
        }

        $sale->delete();
        DashboardController::clearCache(auth()->user());

        return redirect()
            ->route('admin.sales.index')
            ->with('success', 'Vente supprimée.');
    }

    /*
    |--------------------------------------------------------------------------
    | PDF
    |--------------------------------------------------------------------------
    */
    public function pdf(Sale $sale)
    {
        $sale->load(['customer', 'items.product']);

        $pdf = Pdf::loadView('admin.sales.pdf', compact('sale'))
            ->setPaper('a4', 'portrait');

        $filename = 'FACTURE-' . ($sale->reference ?? $sale->id) . '.pdf';

        Storage::disk('public')->makeDirectory('invoices');
        Storage::disk('public')->put('invoices/' . $filename, $pdf->output());

        return $pdf->stream($filename);
    }
}