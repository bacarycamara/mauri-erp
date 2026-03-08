<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = Supplier::query();

        /*
        |--------------------------------------------------------------------------
        |  RECHERCHE
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nif', 'like', "%{$search}%")
                  ->orWhere('rc', 'like', "%{$search}%");
            });
        }

        /*
        |--------------------------------------------------------------------------
        |  FILTRE STATUT
        |--------------------------------------------------------------------------
        */
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        /*
        |--------------------------------------------------------------------------
        |  FILTRE DETTE
        |--------------------------------------------------------------------------
        */
        if ($request->filled('debt')) {
            if ($request->debt === 'yes') {
                $query->where('current_balance', '>', 0);
            }

            if ($request->debt === 'no') {
                $query->where('current_balance', '<=', 0);
            }
        }

        /*
        |--------------------------------------------------------------------------
        |  TRI
        |--------------------------------------------------------------------------
        */
        $sort = $request->get('sort', 'latest');

        switch ($sort) {
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;

            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;

            case 'balance_desc':
                $query->orderBy('current_balance', 'desc');
                break;

            default:
                $query->latest();
        }

        /*
        |--------------------------------------------------------------------------
        | STATS
        |--------------------------------------------------------------------------
        */
        $totalSuppliers     = Supplier::count();
        $activeSuppliers    = Supplier::where('is_active', true)->count();
        $suppliersWithDebt  = Supplier::where('current_balance', '>', 0)->count();
        $totalDebtAmount    = Supplier::where('current_balance', '>', 0)->sum('current_balance');

        $suppliers = $query->paginate(15)->withQueryString();

        return view('admin.suppliers.index', compact(
            'suppliers',
            'totalSuppliers',
            'activeSuppliers',
            'suppliersWithDebt',
            'totalDebtAmount'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        return view('admin.suppliers.create');
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'nullable|email|unique:suppliers,email',
            'phone' => 'nullable|string|max:255',
        ]);

        $opening = $request->opening_balance ?? 0;

        Supplier::create([
            'name'            => $request->name,
            'contact_person'  => $request->contact_person,
            'email'           => $request->email,
            'phone'           => $request->phone,
            'nif'             => $request->nif,
            'rc'              => $request->rc,
            'address'         => $request->address,
            'city'            => $request->city,
            'country'         => $request->country ?? 'Mauritanie',
            'opening_balance' => $opening,
            'current_balance' => $opening,
            'is_active'       => $request->has('is_active'),
            'notes'           => $request->notes,
        ]);

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', 'Fournisseur créé avec succès.');
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
    public function edit(Supplier $supplier)
    {
        return view('admin.suppliers.edit', compact('supplier'));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'nullable|email|unique:suppliers,email,' . $supplier->id,
        ]);

        $supplier->update([
            'name'           => $request->name,
            'contact_person' => $request->contact_person,
            'email'          => $request->email,
            'phone'          => $request->phone,
            'nif'            => $request->nif,
            'rc'             => $request->rc,
            'address'        => $request->address,
            'city'           => $request->city,
            'country'        => $request->country,
            'is_active'      => $request->has('is_active'),
            'notes'          => $request->notes,
        ]);

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', 'Fournisseur mis à jour.');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchases()->exists()) {
            return redirect()
                ->route('admin.suppliers.index')
                ->with('error', 'Impossible de supprimer : fournisseur lié à des achats.');
        }

        $supplier->delete();

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', 'Fournisseur supprimé.');
    }
}