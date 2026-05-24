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

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name',  'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nif',   'like', "%{$search}%")
                  ->orWhere('rc',    'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        if ($request->filled('debt')) {
            if ($request->debt === 'yes') {
                $query->where('current_balance', '>', 0);
            } elseif ($request->debt === 'no') {
                $query->where('current_balance', '<=', 0);
            }
        }

        // ✅ Whitelist des valeurs de tri
        $allowedSorts = ['latest', 'name_asc', 'name_desc', 'balance_desc'];
        $sort = in_array($request->get('sort'), $allowedSorts)
            ? $request->get('sort')
            : 'latest';

        match ($sort) {
            'name_asc'     => $query->orderBy('name', 'asc'),
            'name_desc'    => $query->orderBy('name', 'desc'),
            'balance_desc' => $query->orderBy('current_balance', 'desc'),
            default        => $query->latest(),
        };

        $totalSuppliers    = Supplier::count();
        $activeSuppliers   = Supplier::where('is_active', true)->count();
        $suppliersWithDebt = Supplier::where('current_balance', '>', 0)->count();
        $totalDebtAmount   = Supplier::where('current_balance', '>', 0)->sum('current_balance');

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
            'name'            => 'required|string|max:255',
            'email'           => 'nullable|email|unique:suppliers,email',
            'phone'           => 'nullable|string|max:30',
            'opening_balance' => 'nullable|numeric|min:0',
        ]);

        $opening = (float) ($request->opening_balance ?? 0);

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
            'is_active'       => $request->boolean('is_active'),
            'notes'           => $request->notes,
        ]);

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', 'Fournisseur créé avec succès.');
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */
    public function show(Supplier $supplier)
    {
        $supplier->load(['purchases' => function ($q) {
            $q->latest()->limit(10);
        }]);

        $stats = [
            'total_purchases' => $supplier->purchases()->sum('total_amount'),
            'total_paid'      => $supplier->purchases()->sum('paid_amount'),
            'total_due'       => $supplier->purchases()->sum('due_amount'),
            'purchases_count' => $supplier->purchases()->count(),
        ];

        return view('admin.suppliers.show', compact('supplier', 'stats'));
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
            'name'            => 'required|string|max:255',
            'email'           => 'nullable|email|unique:suppliers,email,' . $supplier->id,
            'phone'           => 'nullable|string|max:30',
            'opening_balance' => 'nullable|numeric|min:0',
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
            'country'        => $request->country ?? $supplier->country ?? 'Mauritanie',
            'is_active'      => $request->boolean('is_active'),
            'notes'          => $request->notes,
        ]);

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', 'Fournisseur mis à jour.');
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
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

    /*
    |--------------------------------------------------------------------------
    | EXPORT CSV
    |--------------------------------------------------------------------------
    */
    public function export(Request $request)
    {
        $suppliers = Supplier::latest()->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="fournisseurs_' . now()->format('Ymd_His') . '.csv"',
        ];

        $callback = function () use ($suppliers) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // BOM UTF-8

            fputcsv($handle, [
                'Nom', 'Contact', 'Téléphone', 'Email',
                'NIF', 'RC', 'Ville', 'Solde', 'Statut',
            ], ';');

            foreach ($suppliers as $s) {
                fputcsv($handle, [
                    $s->name,
                    $s->contact_person ?? '',
                    $s->phone ?? '',
                    $s->email ?? '',
                    $s->nif ?? '',
                    $s->rc ?? '',
                    $s->city ?? '',
                    number_format($s->current_balance, 2, '.', ''),
                    $s->is_active ? 'Actif' : 'Inactif',
                ], ';');
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}