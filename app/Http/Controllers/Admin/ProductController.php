<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = Product::query()
            ->select([
                'id', 'name', 'sku', 'selling_price', 'photo',
                'stock_quantity', 'minimum_stock', 'category_id', 'is_active', 'created_at'
            ])
            ->with('category:id,name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search.'%')
                  ->orWhere('sku', 'like', $search.'%')
                  ->orWhere('barcode', 'like', $search.'%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->status !== null && $request->status !== '') {
            $query->where('is_active', $request->status);
        }

        $products   = $query->orderByDesc('created_at')->paginate(10)->withQueryString();
        $categories = Category::cached();

        return view('admin.products.index', compact('products', 'categories'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $categories = Category::cached();
        return view('admin.products.create', compact('categories'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'selling_price'  => 'required|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'vat_rate'       => 'nullable|numeric|min:0',
            'profit_margin'  => 'nullable|numeric',
            'category_id'    => 'nullable|exists:categories,id',
            'type'           => 'nullable|in:physical,service',
            'unit'           => 'nullable|string|max:50',
            'description'    => 'nullable|string',
            'stock_quantity' => 'nullable|numeric|min:0',
            'minimum_stock'  => 'nullable|numeric|min:0',
            'is_active'      => 'nullable|boolean',
            'photo'          => 'nullable|image|max:2048',
        ]);

        $data = $request->except(['sku', '_token', '_method']);

        // ✅ Forcer 0 si purchase_price est vide
        $data['purchase_price'] = $data['purchase_price'] ?? 0;

        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('products', 'public');
        } else {
            unset($data['photo']);
        }

        Product::create($data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', '✅ Produit créé avec succès.');
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
    public function edit(Product $product)
    {
        $categories = Category::cached();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE ✅ CORRIGÉ
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'selling_price'  => 'required|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'vat_rate'       => 'nullable|numeric|min:0',
            'profit_margin'  => 'nullable|numeric',
            'category_id'    => 'nullable|exists:categories,id',
            'type'           => 'nullable|in:physical,service',
            'unit'           => 'nullable|string|max:50',
            'description'    => 'nullable|string',
            'stock_quantity' => 'nullable|numeric|min:0',
            'minimum_stock'  => 'nullable|numeric|min:0',
            'is_active'      => 'nullable|boolean',
            'photo'          => 'nullable|image|max:2048',
        ]);

        $data = $request->except(['sku', '_token', '_method']);

        // ✅ FIX : Forcer 0 si purchase_price est null ou vide
        $data['purchase_price'] = $data['purchase_price'] ?? 0;

        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('photo')) {
            if ($product->photo && Storage::disk('public')->exists($product->photo)) {
                Storage::disk('public')->delete($product->photo);
            }
            $data['photo'] = $request->file('photo')->store('products', 'public');
        } else {
            unset($data['photo']);
        }

        $product->update($data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', '✅ Produit mis à jour avec succès.');
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */
    public function destroy(Product $product)
    {
        if ($product->photo && Storage::disk('public')->exists($product->photo)) {
            Storage::disk('public')->delete($product->photo);
        }

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produit supprimé.');
    }

    /*
|--------------------------------------------------------------------------
| SHOW
|--------------------------------------------------------------------------
*/
public function show(Product $product)
{
    return view('admin.products.show', compact('product'));
}
}