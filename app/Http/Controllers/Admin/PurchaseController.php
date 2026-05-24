<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PurchaseController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $purchases = Purchase::query()
            ->with('supplier:id,name')
            ->when($request->search, fn ($q) =>
                $q->where('reference', 'like', "%{$request->search}%")
            )
            ->when($request->status, fn ($q) =>
                $q->where('status', $request->status)
            )
            ->when($request->supplier, fn ($q) =>
                $q->where('supplier_id', $request->supplier)
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.purchases.index', compact('purchases'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function create(Request $request)
    {
        $suppliers = Supplier::active()->select('id', 'name')->orderBy('name')->get();
        $products  = Product::active()->select('id', 'name', 'purchase_price')->orderBy('name')->get();

        $selectedProduct = null;
        $prefillQty      = 1;

        if ($request->filled('product_id')) {
            $selectedProduct = Product::active()
                ->select('id', 'name', 'purchase_price')
                ->find($request->product_id);

            if ($request->filled('qty')) {
                $prefillQty = (float) $request->qty;
            }
        }

        return view('admin.purchases.create', [
            'suppliers'       => $suppliers,
            'products'        => $products,
            'selectedProduct' => $selectedProduct,
            'prefillQty'      => $prefillQty,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id'        => ['required', 'exists:suppliers,id'],
            'purchase_date'      => ['required', 'date'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity'   => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($data, $request) {

            $purchase = Purchase::create([
                'supplier_id'   => $data['supplier_id'],
                'purchase_date' => $data['purchase_date'],
                'notes'         => $request->notes,
            ]);

            $items = [];
            foreach ($data['items'] as $item) {
                $items[] = new PurchaseItem([
                    'product_id'    => $item['product_id'],
                    'quantity'      => round($item['quantity'], 2),
                    'unit_price'    => round($item['unit_price'], 2),
                    'vat_rate'      => $item['vat_rate'] ?? 0,
                    'discount_rate' => $item['discount_rate'] ?? 0,
                ]);
            }

            $purchase->items()->saveMany($items);
            $purchase->calculateTotals();
        });

        DashboardController::clearCache(auth()->user());

        return redirect()
            ->route('admin.purchases.index')
            ->with('success', 'Achat créé avec succès.');
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */
    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier:id,name', 'items.product:id,name']);

        return view('admin.purchases.show', compact('purchase'));
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIRM
    |--------------------------------------------------------------------------
    */
    public function confirm(Purchase $purchase)
    {
        DB::transaction(function () use ($purchase) {
            $purchase = Purchase::lockForUpdate()->find($purchase->id);

            if ($purchase->status !== 'draft') {
                return;
            }

            $purchase->confirm();
        });

        DashboardController::clearCache(auth()->user());

        return back()->with('success', 'Achat confirmé.');
    }

    /*
    |--------------------------------------------------------------------------
    | REGISTER PAYMENT
    |--------------------------------------------------------------------------
    */
    public function registerPayment(Request $request, Purchase $purchase)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01']
        ]);

        DB::transaction(function () use ($purchase, $data) {
            $purchase = Purchase::lockForUpdate()->find($purchase->id);

            if (!in_array($purchase->status, ['confirmed', 'partial'])) {
                throw new \Exception("L'achat doit être confirmé avant paiement.");
            }

            $purchase->registerPayment(round($data['amount'], 2));
        });

        DashboardController::clearCache(auth()->user());

        return back()->with('success', 'Paiement enregistré.');
    }

    /*
    |--------------------------------------------------------------------------
    | CANCEL
    |--------------------------------------------------------------------------
    */
    public function cancel(Purchase $purchase)
    {
        if (in_array($purchase->status, ['confirmed', 'paid'])) {
            return back()->with('error', 'Impossible d\'annuler un achat confirmé ou payé.');
        }

        $purchase->update(['status' => 'cancelled']);
        DashboardController::clearCache(auth()->user());

        return back()->with('success', 'Achat annulé.');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function destroy(Purchase $purchase)
    {
        if (in_array($purchase->status, ['confirmed', 'paid'])) {
            return back()->with('error', 'Impossible de supprimer un achat validé.');
        }

        $purchase->delete();
        DashboardController::clearCache(auth()->user());

        return redirect()
            ->route('admin.purchases.index')
            ->with('success', 'Achat supprimé.');
    }

    /*
    |--------------------------------------------------------------------------
    | PDF EXPORT
    |--------------------------------------------------------------------------
    */
    public function downloadPdf(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.product']);

        $pdf = Pdf::loadView('admin.purchases.pdf', compact('purchase'))
            ->setPaper('a4', 'portrait');

        $filename = 'ACHAT-' . ($purchase->reference ?? $purchase->id) . '.pdf';

        Storage::disk('public')->makeDirectory('purchases');
        Storage::disk('public')->put('purchases/' . $filename, $pdf->output());

        return $pdf->download($filename);
    }
}