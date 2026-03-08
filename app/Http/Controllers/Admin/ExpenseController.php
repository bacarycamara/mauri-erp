<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\CashRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX (Recherche + Filtres pro)
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = Expense::with(['cashRegister', 'approvedBy']);

        // Recherche
        $query->search($request->search);

        // Filtre statut
        $query->status($request->status);

        // Filtre date
        $query->betweenDates($request->from, $request->to);

        // Filtre caisse
        if ($request->cash_register_id) {
            $query->where('cash_register_id', $request->cash_register_id);
        }

        $expenses = $query->latest()->paginate(20)->withQueryString();

        $cashRegisters = CashRegister::where('status', 'open')->get();

        return view('admin.expenses.index', compact('expenses', 'cashRegisters'));
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $cashRegisters = CashRegister::where('status', 'open')->get();

        if ($cashRegisters->isEmpty()) {
            return redirect()
                ->route('admin.cash-registers.index')
                ->with('error', 'Aucune caisse ouverte.');
        }

        return view('admin.expenses.create', compact('cashRegisters'));
    }


    /*
    |--------------------------------------------------------------------------
    | STORE (Toujours en pending)
    |--------------------------------------------------------------------------
    */

public function store(Request $request)
{
    $request->validate([
        'category'         => 'required|string|max:255',
        'expense_date'     => 'required|date',
        'amount'           => 'required|numeric|min:0.01',
        'cash_register_id' => 'required|exists:cash_registers,id',
        'payment_method'   => 'required|string',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Conversion vers ENUM accepté par la base
    |--------------------------------------------------------------------------
    */

    $method = match ($request->payment_method) {

        'cash' => 'cash',

        'masrvi',
        'bankily',
        'sedad',
        'click' => 'mobile_money',

        'bank_transfer' => 'bank_transfer',

        'check' => 'check',

        default => 'other',
    };

    Expense::create([
        'category'         => $request->category,
        'expense_date'     => $request->expense_date,
        'amount'           => $request->amount,
        'payment_method'   => $method,
        'cash_register_id' => $request->cash_register_id,
        'status'           => 'pending',
        'notes'            => $request->notes,
    ]);

    return redirect()
        ->route('admin.expenses.index')
        ->with('success', 'Dépense enregistrée en attente de validation.');
}

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(Expense $expense)
    {
        $expense->load(['cashRegister', 'approvedBy']);

        return view('admin.expenses.show', compact('expense'));
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT (Seulement si pending)
    |--------------------------------------------------------------------------
    */

    public function edit(Expense $expense)
    {
        if (!$expense->isPending()) {
            return back()->with('error', 'Seules les dépenses en attente peuvent être modifiées.');
        }

        $cashRegisters = CashRegister::where('status', 'open')->get();

        return view('admin.expenses.edit', compact('expense', 'cashRegisters'));
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

 public function update(Request $request, Expense $expense)
{
    if (!$expense->isPending()) {
        return back()->with('error', 'Impossible de modifier une dépense validée.');
    }

    $request->validate([
        'category'         => 'required|string|max:255',
        'expense_date'     => 'required|date',
        'amount'           => 'required|numeric|min:0.01',
        'cash_register_id' => 'required|exists:cash_registers,id',
        'payment_method'   => 'required|string',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Conversion vers ENUM accepté par la base
    |--------------------------------------------------------------------------
    */

    $method = match ($request->payment_method) {

        'cash' => 'cash',

        'masrvi',
        'bankily',
        'sedad',
        'click' => 'mobile_money',

        'bank_transfer' => 'bank_transfer',

        'check' => 'check',

        default => 'other',
    };

    DB::transaction(function () use ($request, $expense, $method) {

        $expense->update([
            'category'         => $request->category,
            'expense_date'     => $request->expense_date,
            'amount'           => $request->amount,
            'payment_method'   => $method,
            'cash_register_id' => $request->cash_register_id,
            'notes'            => $request->notes,
        ]);

    });

    return redirect()
        ->route('admin.expenses.index')
        ->with('success', 'Dépense mise à jour.');
}


    /*
    |--------------------------------------------------------------------------
    | APPROVE
    |--------------------------------------------------------------------------
    */

    public function approve(Expense $expense)
    {
        if (!$expense->isPending()) {
            return back()->with('error', 'Cette dépense ne peut pas être approuvée.');
        }

        DB::transaction(function () use ($expense) {
            $expense->update([
                'status' => 'approved'
            ]);
        });

        return back()->with('success', 'Dépense approuvée.');
    }


    /*
    |--------------------------------------------------------------------------
    | CANCEL
    |--------------------------------------------------------------------------
    */

    public function cancel(Expense $expense)
    {
        if ($expense->isCancelled()) {
            return back()->with('error', 'Dépense déjà annulée.');
        }

        DB::transaction(function () use ($expense) {
            $expense->update([
                'status' => 'cancelled'
            ]);
        });

        return back()->with('success', 'Dépense annulée.');
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE (SoftDelete seulement si pending)
    |--------------------------------------------------------------------------
    */

    public function destroy(Expense $expense)
    {
        if (!$expense->isPending()) {
            return back()->with('error', 'Seules les dépenses en attente peuvent être supprimées.');
        }

        $expense->delete();

        return back()->with('success', 'Dépense supprimée.');
    }
}