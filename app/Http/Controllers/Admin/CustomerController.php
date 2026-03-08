<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = Customer::query()
            ->withCount('sales');

        // Recherche intelligente
        $query->search($request->search);

        // Filtre statut actif
        if ($request->status === 'active') {
            $query->active();
        }

        if ($request->status === 'inactive') {
            $query->inactive();
        }

        // Filtre débiteurs
        if ($request->debt === 'yes') {
            $query->debtors();
        }

        $customers = $query->latest()->paginate(15);

        // Statistiques globales
        $stats = [
            'total_clients' => Customer::count(),
            'active_clients' => Customer::active()->count(),
            'debtors' => Customer::debtors()->count(),
            'total_debt' => Customer::sum('current_balance'),
        ];

        return view('admin.customers.index', compact('customers', 'stats'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        return view('admin.customers.create');
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'nullable|email|unique:customers,email',
            'phone'  => 'nullable|string|max:255',
            'opening_balance' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {

            Customer::create([
                'name'             => $request->name,
                'contact_person'   => $request->contact_person,
                'email'            => $request->email,
                'phone'            => $request->phone,
                'nif'              => $request->nif,
                'rc'               => $request->rc,
                'address'          => $request->address,
                'city'             => $request->city,
                'country'          => $request->country ?? 'Mauritanie',
                'opening_balance'  => $request->opening_balance ?? 0,
                'current_balance'  => $request->opening_balance ?? 0,
                'is_active'        => $request->has('is_active'),
                'notes'            => $request->notes,
            ]);
        });

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Client créé avec succès.');
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */
    public function show(Customer $customer)
    {
        $customer->load(['sales' => function ($query) {
            $query->latest();
        }]);

        // Stats client
        $stats = [
            'total_sales' => $customer->total_sales,
            'total_paid'  => $customer->total_paid,
            'total_due'   => $customer->total_due,
            'sales_count' => $customer->sales_count,
        ];

        return view('admin.customers.show', compact('customer', 'stats'));
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
        ]);

        DB::transaction(function () use ($request, $customer) {

            $customer->update([
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

            // Sécurité ERP : recalcul dette
            $customer->recalculateBalance();
        });

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Client mis à jour.');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function destroy(Customer $customer)
    {
        if (!$customer->canBeDeleted()) {
            return redirect()
                ->route('admin.customers.index')
                ->with('error', 'Impossible de supprimer : client lié à des ventes.');
        }

        $customer->delete();

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Client supprimé.');
    }
}