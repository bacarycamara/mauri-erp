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
    | INDEX — LISTE GLOBAL + PAR CAISSE
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $cashRegister = null;

        if ($request->filled('cash_register_id')) {
            $cashRegister = CashRegister::find($request->cash_register_id);
        }

        $baseQuery = CashTransaction::with('cashRegister');

        if ($cashRegister) {
            $baseQuery->where('cash_register_id', $cashRegister->id);
        }

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

        $transactions = (clone $baseQuery)->latest()->paginate(20)->withQueryString();

        // ✅ Totaux globaux calculés en DB
        $totalIn  = (clone $baseQuery)->where('type', 'in')->sum('amount');
        $totalOut = (clone $baseQuery)->where('type', 'out')->sum('amount');

        return view('admin.cash.transactions.index', compact(
            'cashRegister',
            'transactions',
            'totalIn',
            'totalOut'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    | ✅ CORRIGÉ : suppression de la double mise à jour caisse.
    |    Le hook created() de CashTransaction gère automatiquement
    |    l'incrémentation de total_in / total_out.
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'cash_register_id' => 'required|exists:cash_registers,id',
            'type'             => 'required|in:in,out',
            'amount'           => 'required|numeric|min:0.01',
            'description'      => 'nullable|string|max:500',
        ]);

        try {
            CashTransaction::create([
                'cash_register_id' => $request->cash_register_id,
                'type'             => $request->type,
                'amount'           => $request->amount,
                'description'      => $request->description,
                'source'           => 'manual',
            ]);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Transaction enregistrée avec succès.');
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    | ✅ CORRIGÉ : suppression de la double mise à jour caisse.
    |    Le hook deleted() de CashTransaction gère automatiquement
    |    le décrément de total_in / total_out.
    |--------------------------------------------------------------------------
    */
    public function destroy(CashTransaction $cashTransaction)
    {
        $cashRegister = $cashTransaction->cashRegister;

        if (!$cashRegister || !$cashRegister->isOpen()) {
            return back()->with('error', 'Impossible de supprimer : caisse fermée.');
        }

        try {
            $cashTransaction->delete();
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

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

        $totalIn  = $transactions->where('type', 'in')->sum('amount');
        $totalOut = $transactions->where('type', 'out')->sum('amount');

        $pdf = Pdf::loadView('admin.cash.transactions.pdf', compact(
            'cashRegister',
            'transactions',
            'totalIn',
            'totalOut'
        ))->setPaper('a4', 'portrait');

        return $pdf->download('rapport-caisse-' . $cashRegister->id . '.pdf');
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

        $totalIn  = $transactions->where('type', 'in')->sum('amount');
        $totalOut = $transactions->where('type', 'out')->sum('amount');

        return view('admin.cash.transactions.print', compact(
            'cashRegister',
            'transactions',
            'totalIn',
            'totalOut'
        ));
    }
}