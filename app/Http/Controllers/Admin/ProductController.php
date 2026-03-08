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

        // charger seulement les colonnes nécessaires
        ->select([
            'id',
            'name',
            'sku',
            'selling_price',
            'stock_quantity',
            'category_id',
            'is_active',
            'created_at'
        ])

        ->with('category:id,name');

    /*
    |--------------------------------------------------------------------------
    | RECHERCHE
    |--------------------------------------------------------------------------
    */

    if ($request->filled('search')) {

        $search = $request->search;

        $query->where(function ($q) use ($search) {

            $q->where('name', 'like', $search.'%')
              ->orWhere('sku', 'like', $search.'%')
              ->orWhere('barcode', 'like', $search.'%');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | FILTRE CATEGORIE
    |--------------------------------------------------------------------------
    */

    if ($request->filled('category')) {
        $query->where('category_id', $request->category);
    }

    /*
    |--------------------------------------------------------------------------
    | FILTRE STATUT
    |--------------------------------------------------------------------------
    */

    if ($request->status !== null && $request->status !== '') {
        $query->where('is_active', $request->status);
    }

    /*
    |--------------------------------------------------------------------------
    | PAGINATION
    |--------------------------------------------------------------------------
    */

    $products = $query
        ->orderByDesc('created_at')
        ->paginate(10)
        ->withQueryString();

    /*
    |--------------------------------------------------------------------------
    | CATEGORIES (CACHE)
    |--------------------------------------------------------------------------
    */

    $categories = Category::cached();

    return view('admin.products.index', compact('products','categories'));
}

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        //  cache catégories
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
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'selling_price'  => 'required|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'category_id'    => 'nullable|exists:categories,id',
            'photo'          => 'nullable|image|max:2048',
        ]);

        $data = $request->except('sku'); // SKU généré automatiquement par le modèle

        // Upload image
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')
                ->store('products', 'public');
        }

        Product::create($data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produit créé avec succès.');
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
    public function edit(Product $product)
    {
        //  cache catégories
        $categories = Category::cached();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'selling_price'  => 'required|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'category_id'    => 'nullable|exists:categories,id',
            'photo'          => 'nullable|image|max:2048',
        ]);

        $data = $request->except('sku');

        // Upload nouvelle image
        if ($request->hasFile('photo')) {

            if ($product->photo && Storage::disk('public')->exists($product->photo)) {
                Storage::disk('public')->delete($product->photo);
            }

            $data['photo'] = $request->file('photo')
                ->store('products', 'public');
        }

        $product->update($data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produit mis à jour.');
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
}