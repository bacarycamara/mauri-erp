<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use App\Models\CashTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class CashTransactionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTE GLOBAL + PAR CAISSE (UNIFIÉ)
    |--------------------------------------------------------------------------
    */
 public function index(Request $request)
{
    /*
    |--------------------------------------------------------------------------
    | RECUPERATION CAISSE (OPTIONNELLE)
    |--------------------------------------------------------------------------
    */
    $cashRegister = null;

    if ($request->filled('cash_register_id')) {
        $cashRegister = CashRegister::find($request->cash_register_id);
    }

    $baseQuery = CashTransaction::with('cashRegister');

    if ($cashRegister) {
        $baseQuery->where('cash_register_id', $cashRegister->id);
    }

    /*
    |--------------------------------------------------------------------------
    | FILTRES
    |--------------------------------------------------------------------------
    */
    if ($request->filled('search')) {
        $baseQuery->where(function ($q) use ($request) {
            $q->where('reference', 'like', '%' . $request->search . '%')
              ->orWhere('description', 'like', '%' . $request->search . '%');
        });
    }

    if ($request->filled('date_from')) {
        $baseQuery->whereDate('created_at', '>=', $request->date_from);
    }

    if ($request->filled('date_to')) {
        $baseQuery->whereDate('created_at', '<=', $request->date_to);
    }

    if ($request->filled('type')) {
        $baseQuery->where('type', $request->type);
    }

    /*
    |--------------------------------------------------------------------------
    | DATA
    |--------------------------------------------------------------------------
    */
    $transactions = (clone $baseQuery)
        ->latest()
        ->paginate(20)
        ->withQueryString();

    $totalIn  = (clone $baseQuery)->where('type','in')->sum('amount');
    $totalOut = (clone $baseQuery)->where('type','out')->sum('amount');

    return view('admin.cash.transactions.index', compact(
        'cashRegister',
        'transactions',
        'totalIn',
        'totalOut'
    ));
}

    /*
    |--------------------------------------------------------------------------
    | STORE (AVEC MISE À JOUR CAISSE)
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'cash_register_id' => 'required|exists:cash_registers,id',
            'type'             => 'required|in:in,out',
            'amount'           => 'required|numeric|min:0.01',
            'description'      => 'nullable|string|max:255'
        ]);

        try {

            DB::transaction(function () use ($request) {

                $cashRegister = CashRegister::lockForUpdate()
                    ->findOrFail($request->cash_register_id);

                if (!$cashRegister->isOpen()) {
                    throw new \Exception('La caisse est fermée.');
                }

                if (
                    $request->type === 'out' &&
                    $cashRegister->current_balance < $request->amount
                ) {
                    throw new \Exception('Solde insuffisant.');
                }

                $transaction = CashTransaction::create([
                    'cash_register_id' => $cashRegister->id,
                    'type'             => $request->type,
                    'amount'           => $request->amount,
                    'description'      => $request->description,
                    'source'           => 'manual'
                ]);

                // Mise à jour des totaux caisse
                if ($request->type === 'in') {
                    $cashRegister->total_in += $request->amount;
                } else {
                    $cashRegister->total_out += $request->amount;
                }

                $cashRegister->save();
            });

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Transaction enregistrée avec succès.');
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE (RECALCUL CAISSE)
    |--------------------------------------------------------------------------
    */
    public function destroy(CashTransaction $cashTransaction)
    {
        $cashRegister = $cashTransaction->cashRegister;

        if (!$cashRegister || !$cashRegister->isOpen()) {
            return back()->with('error','Impossible de supprimer, caisse fermée.');
        }

        DB::transaction(function () use ($cashTransaction, $cashRegister) {

            if ($cashTransaction->type === 'in') {
                $cashRegister->total_in -= $cashTransaction->amount;
            } else {
                $cashRegister->total_out -= $cashTransaction->amount;
            }

            $cashRegister->save();
            $cashTransaction->delete();
        });

        return back()->with('success', 'Transaction supprimée.');
    }


    /*
    |--------------------------------------------------------------------------
    | PDF
    |--------------------------------------------------------------------------
    */
    public function pdf(CashRegister $cashRegister)
    {
        $transactions = CashTransaction::where('cash_register_id', $cashRegister->id)
            ->latest()
            ->get();

        $totalIn  = $transactions->where('type','in')->sum('amount');
        $totalOut = $transactions->where('type','out')->sum('amount');

        $pdf = Pdf::loadView('admin.cash.transactions.pdf', compact(
            'cashRegister',
            'transactions',
            'totalIn',
            'totalOut'
        ))->setPaper('a4','portrait');

        return $pdf->download('rapport-caisse-'.$cashRegister->id.'.pdf');
    }


    /*
    |--------------------------------------------------------------------------
    | PRINT
    |--------------------------------------------------------------------------
    */
    public function print(CashRegister $cashRegister)
    {
        $transactions = CashTransaction::where('cash_register_id', $cashRegister->id)
            ->latest()
            ->get();

        $totalIn  = $transactions->where('type','in')->sum('amount');
        $totalOut = $transactions->where('type','out')->sum('amount');

        return view('admin.cash.transactions.print', compact(
            'cashRegister',
            'transactions',
            'totalIn',
            'totalOut'
        ));
    }
}