<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use Illuminate\Http\Request;

class SaleItemController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware('permission:create sales')->only(['store']);
        $this->middleware('permission:edit sales')->only(['update']);
        $this->middleware('permission:delete sales')->only(['destroy']);
    }

    /*
    |--------------------------------------------------------------------------
    | STORE (Ajouter produit à une vente)
    |--------------------------------------------------------------------------
    */
    public function store(Request $request, Sale $sale)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|numeric|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        // Vérifier stock disponible
        if ($product->stock_quantity < $request->quantity) {
            return back()->with('error', 'Stock insuffisant.');
        }

        $unitPrice = $product->selling_price;
        $vatRate   = $product->vat_rate ?? 0;
        $discount  = $request->discount_rate ?? 0;

        $subtotal = $unitPrice * $request->quantity;
        $vatAmount = ($subtotal * $vatRate) / 100;
        $discountAmount = ($subtotal * $discount) / 100;
        $total = $subtotal + $vatAmount - $discountAmount;

        SaleItem::create([
            'sale_id'        => $sale->id,
            'product_id'     => $product->id,
            'quantity'       => $request->quantity,
            'unit_price'     => $unitPrice,
            'vat_rate'       => $vatRate,
            'discount_rate'  => $discount,
            'subtotal'       => $subtotal,
            'vat_amount'     => $vatAmount,
            'discount_amount'=> $discountAmount,
            'total'          => $total,
        ]);

        // Décrément stock
        $product->decrement('stock_quantity', $request->quantity);

        // Recalculer totaux vente
        $sale->calculateTotals();

        return back()->with('success', 'Produit ajouté à la vente.');
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, SaleItem $saleItem)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:1',
        ]);

        $product = $saleItem->product;

        // Restaurer ancien stock
        $product->increment('stock_quantity', $saleItem->quantity);

        if ($product->stock_quantity < $request->quantity) {
            return back()->with('error', 'Stock insuffisant.');
        }

        $unitPrice = $saleItem->unit_price;
        $vatRate   = $saleItem->vat_rate;
        $discount  = $saleItem->discount_rate ?? 0;

        $subtotal = $unitPrice * $request->quantity;
        $vatAmount = ($subtotal * $vatRate) / 100;
        $discountAmount = ($subtotal * $discount) / 100;
        $total = $subtotal + $vatAmount - $discountAmount;

        $saleItem->update([
            'quantity'        => $request->quantity,
            'subtotal'        => $subtotal,
            'vat_amount'      => $vatAmount,
            'discount_amount' => $discountAmount,
            'total'           => $total,
        ]);

        // Décrément nouveau stock
        $product->decrement('stock_quantity', $request->quantity);

        // Recalcul vente
        $saleItem->sale->calculateTotals();

        return back()->with('success', 'Ligne mise à jour.');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function destroy(SaleItem $saleItem)
    {
        $product = $saleItem->product;

        // Restaurer stock
        $product->increment('stock_quantity', $saleItem->quantity);

        $sale = $saleItem->sale;

        $saleItem->delete();

        $sale->calculateTotals();

        return back()->with('success', 'Produit retiré de la vente.');
    }
}