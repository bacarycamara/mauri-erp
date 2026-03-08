<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use App\Models\CashTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class CashRegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTE DES CAISSES
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $registers = CashRegister::with(['openedBy','closedBy'])
            ->latest()
            ->paginate(20);

        $current = CashRegister::current();

        return view('admin.cash.index', compact('registers','current'));
    }

/*
|--------------------------------------------------------------------------
| DETAIL CAISSE
|--------------------------------------------------------------------------
*/
public function show(CashRegister $cashRegister)
{
    return redirect()->route('admin.cash-transactions.index', [
        'cash_register_id' => $cashRegister->id
    ]);
}

    /*
    |--------------------------------------------------------------------------
    | OUVRIR CAISSE (ERP SAFE)
    |--------------------------------------------------------------------------
    */
    public function open(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'opening_balance' => 'required|numeric|min:0'
        ]);

        try {

            DB::transaction(function () use ($request) {

                //  empêcher plusieurs caisses ouvertes
                if (CashRegister::whereNull('closed_at')->exists()) {
                    throw new \Exception('Une caisse est déjà ouverte.');
                }

                CashRegister::openRegister(
                    round($request->opening_balance, 2),
                    Auth::id(),
                    $request->name
                );
            });

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Caisse ouverte avec succès.');
    }


    /*
    |--------------------------------------------------------------------------
    | FERMER CAISSE (RECALCUL ERP RÉEL)
    |--------------------------------------------------------------------------
    */
   public function close(CashRegister $cashRegister)
{
    //  Vérifier si déjà fermée
    if (!$cashRegister->isOpen()) {
        return back()->with('error', 'Cette caisse est déjà fermée.');
    }

    try {

        DB::transaction(function () use ($cashRegister) {

            //  Verrouillage des transactions liées
            $transactions = CashTransaction::where(
                    'cash_register_id',
                    $cashRegister->id
                )
                ->lockForUpdate()
                ->get();

            //  Calcul totaux
            $totalIn = $transactions
                ->where('type', 'in')
                ->sum('amount');

            $totalOut = $transactions
                ->where('type', 'out')
                ->sum('amount');

            //  Calcul solde final
            $closingBalance =
                $cashRegister->opening_balance
                + $totalIn
                - $totalOut;

            //  Mise à jour caisse (CORRECTION ICI)
            $cashRegister->update([
                'closing_balance' => round($closingBalance, 2),
                'closed_at'       => now(),
                'closed_by'       => Auth::id(),
                'status'          => 'closed', // IMPORTANT (corrige ton bug)
            ]);
        });

    } catch (\Exception $e) {
        return back()->with('error', $e->getMessage());
    }

    return back()->with('success', 'Caisse fermée avec succès.');
}


    /*
    |--------------------------------------------------------------------------
    | SUPPRESSION CAISSE (PROTECTION ERP)
    |--------------------------------------------------------------------------
    */
    public function destroy(CashRegister $cashRegister)
    {
        if (!$cashRegister->canBeDeleted()) {
            return back()->with(
                'error',
                'Impossible de supprimer : la caisse contient des transactions.'
            );
        }

        $cashRegister->delete();

        return back()->with('success', 'Caisse supprimée avec succès.');
    }


    /*
    |--------------------------------------------------------------------------
    | RAPPORT PDF CAISSE
    |--------------------------------------------------------------------------
    */
    public function report(CashRegister $cashRegister)
    {
        $transactions = CashTransaction::where(
                'cash_register_id',
                $cashRegister->id
            )
            ->latest()
            ->get();

        $totalIn  = $transactions->where('type','in')->sum('amount');
        $totalOut = $transactions->where('type','out')->sum('amount');

        $summary = [
            'opening_balance' => $cashRegister->opening_balance,
            'total_in'        => $totalIn,
            'total_out'       => $totalOut,
            'closing_balance' =>
                $cashRegister->opening_balance + $totalIn - $totalOut,
            'transactions'    => $transactions->count(),
        ];

        $pdf = Pdf::loadView('admin.cash.pdf', compact(
            'cashRegister',
            'transactions',
            'summary'
        ))->setPaper('a4','portrait');

        return $pdf->download(
            'rapport-caisse-'.$cashRegister->id.'.pdf'
        );
    }
}