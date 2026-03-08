<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX — HISTORIQUE GLOBAL STOCK
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = StockMovement::with('product');

        //  filtre produit
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        //  filtre type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        //  recherche référence
        if ($request->filled('search')) {
            $query->where('reference', 'like', '%' . $request->search . '%');
        }

        $movements = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $products = Product::orderBy('name')->get();

        return view('admin.stock_movements.index', compact(
            'movements',
            'products'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE — AJUSTEMENT MANUEL ERP
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => ['required','exists:products,id'],
            'type'       => ['required','in:purchase,sale,adjustment,return'],
            'quantity'   => ['required','numeric','min:0.01'],
            'reference'  => ['nullable','string','max:255'],
            'notes'      => ['nullable','string'],
        ]);

        DB::transaction(function () use ($request) {

            //  lock produit (anti double écriture)
            $product = Product::lockForUpdate()
                ->findOrFail($request->product_id);

            $stockBefore = $product->stock_quantity;
            $quantity    = (float) $request->quantity;

            /*
            |--------------------------------------------------------------------------
            | LOGIQUE ERP STOCK
            |--------------------------------------------------------------------------
            */

            switch ($request->type) {

                case 'purchase':
                case 'return':
                    $stockAfter = $stockBefore + $quantity;
                    break;

                case 'sale':
                    if ($stockBefore < $quantity) {
                        throw new \Exception('Stock insuffisant.');
                    }

                    $stockAfter = $stockBefore - $quantity;
                    break;

                case 'adjustment':
                    // quantité = nouveau stock réel
                    $stockAfter = $quantity;
                    $quantity   = $stockAfter - $stockBefore;
                    break;
            }

            //  update stock produit
            $product->update([
                'stock_quantity' => $stockAfter
            ]);

            // enregistrer mouvement
            StockMovement::create([
                'product_id'   => $product->id,
                'type'         => $request->type,
                'quantity'     => abs($quantity),
                'stock_before' => $stockBefore,
                'stock_after'  => $stockAfter,
                'reference'    => $request->reference,
                'notes'        => $request->notes,
            ]);
        });

        return redirect()
            ->back()
            ->with('success', '✅ Mouvement de stock enregistré avec succès.');
    }

    /*
    |--------------------------------------------------------------------------
    | HISTORIQUE PAR PRODUIT
    |--------------------------------------------------------------------------
    */
    public function product(Product $product)
    {
        $movements = $product->stockMovements()
            ->latest()
            ->paginate(15);

        return view('admin.stock_movements.product', compact(
            'product',
            'movements'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE (optionnel ERP admin)
    |--------------------------------------------------------------------------
    */
    public function destroy(StockMovement $stockMovement)
    {
        $stockMovement->delete();

        return back()->with(
            'success',
            'Mouvement supprimé.'
        );
    }
}