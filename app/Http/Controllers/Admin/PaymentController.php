<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\CashRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;


class PaymentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = Payment::with([
            'sale.customer',
            'purchase.supplier',
            'cashRegister'
        ]);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('payment_number', 'like', "%{$request->search}%")
                  ->orWhere('reference', 'like', "%{$request->search}%")
                  ->orWhereHas('sale.customer',
                        fn($c) => $c->where('name','like',"%{$request->search}%"))
                  ->orWhereHas('purchase.supplier',
                        fn($s) => $s->where('name','like',"%{$request->search}%"));
            });
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->method) {
            $query->where('payment_method', $request->method);
        }

        if ($request->from && $request->to) {
            $query->whereBetween('payment_date', [$request->from, $request->to]);
        }

        $payments = $query->latest()->paginate(20)->withQueryString();

        return view('admin.payments.index', compact('payments'));
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */
    public function show(Payment $payment)
    {
        $payment->load([
            'sale.customer',
            'purchase.supplier',
            'cashRegister'
        ]);

        return view('admin.payments.show', compact('payment'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
  public function create(Request $request)
{
    /*
    |--------------------------------------------------------------------------
    | CAISSES OUVERTES
    |--------------------------------------------------------------------------
    */
    $cashRegisters = CashRegister::open()
        ->orderBy('name')
        ->get();

    if ($cashRegisters->isEmpty()) {
        return redirect()
            ->route('admin.cash-registers.index')
            ->with('error', 'Aucune caisse ouverte.');
    }

    /*
    |--------------------------------------------------------------------------
    | VENTES AVEC RESTE À PAYER (ERP REAL DUE)
    |--------------------------------------------------------------------------
    */
    $sales = Sale::with('customer')
        ->whereIn('status', ['confirmed','partial'])
        ->get()
        ->filter(fn ($sale) => $sale->getRealDueAmount() > 0)
        ->values(); // reset keys (important pour Blade)

    /*
    |--------------------------------------------------------------------------
    | ACHATS AVEC RESTE À PAYER (ERP REAL DUE)
    |--------------------------------------------------------------------------
    */
    $purchases = Purchase::with('supplier')
        ->whereIn('status', ['confirmed','partial'])
        ->get()
        ->filter(fn ($purchase) => $purchase->getRealDueAmount() > 0)
        ->values();

    /*
    |--------------------------------------------------------------------------
    | PRÉSELECTION AUTOMATIQUE (URL PARAMS)
    | ex: /payments/create?purchase_id=3
    |--------------------------------------------------------------------------
    */
    $selectedSaleId     = $request->sale_id;
    $selectedPurchaseId = $request->purchase_id;

    /*
    |--------------------------------------------------------------------------
    | VIEW
    |--------------------------------------------------------------------------
    */
    return view('admin.payments.create', [
        'sales'              => $sales,
        'purchases'          => $purchases,
        'cashRegisters'      => $cashRegisters,
        'selectedSaleId'     => $selectedSaleId,
        'selectedPurchaseId' => $selectedPurchaseId,
    ]);
}

    /*
    |--------------------------------------------------------------------------
    | STORE (ERP SAFE)
    |--------------------------------------------------------------------------
    */
public function store(Request $request)
{
    $request->validate([
        'amount'            => 'required|numeric|min:0.01',
        'payment_provider'  => 'required|string',
        'payment_date'      => 'required|date',
        'cash_register_id'  => 'required|exists:cash_registers,id',
    ]);

    if (!$request->sale_id && !$request->purchase_id) {
        return back()->with('error','Sélectionnez une vente ou un achat.');
    }

    try {

        $payment = DB::transaction(function () use ($request) {

            $cashRegister = CashRegister::lockForUpdate()
                ->findOrFail($request->cash_register_id);

            if (!$cashRegister->isOpen()) {
                throw new \Exception('Caisse fermée.');
            }

            $type = $request->sale_id ? 'in' : 'out';

            /*
            |--------------------------------------------------------------------------
            | Sécurité décaissement
            |--------------------------------------------------------------------------
            */
            if (
                $type === 'out' &&
                $request->amount > $cashRegister->current_balance
            ) {
                throw new \Exception('Solde insuffisant.');
            }

            /*
            |--------------------------------------------------------------------------
            | Création paiement
            | Toute la logique financière reste dans le MODEL
            |--------------------------------------------------------------------------
            */
            $payment = Payment::create([
                'sale_id'          => $request->sale_id,
                'purchase_id'      => $request->purchase_id,
                'cash_register_id' => $cashRegister->id,
                'type'             => $type,
                'amount'           => $request->amount,

                // Provider uniquement → mapping auto dans Model
                'payment_provider' => $request->payment_provider,

                'status'           => 'confirmed',
                'payment_date'     => $request->payment_date,
                'notes'            => $request->notes,
            ]);

            return $payment;
        });

    } catch (\Exception $e) {

        return back()->with('error', $e->getMessage());
    }

    /*
    |--------------------------------------------------------------------------
    |  REDIRECTION VERS TICKET THERMIQUE
    |--------------------------------------------------------------------------
    */

    return redirect()
        ->route('admin.payments.printer', $payment)
        ->with('success','Paiement enregistré.');
}

    /*
    |--------------------------------------------------------------------------
    | CANCEL
    |--------------------------------------------------------------------------
    */
    public function cancel(Payment $payment)
    {
        if (!$payment->canBeCancelled()) {
            return back()->with('error','Paiement non annulable.');
        }

        DB::transaction(function () use ($payment) {
            $payment->update(['status' => 'cancelled']);
        });

        return back()->with('success','Paiement annulé.');
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */
    public function destroy(Payment $payment)
    {
        if ($payment->isConfirmed()) {
            return back()->with('error','Annulez d’abord le paiement.');
        }

        $payment->delete();

        return redirect()
            ->route('admin.payments.index')
            ->with('success','Paiement supprimé.');
    }

    /*
    |--------------------------------------------------------------------------
    | PDF
    |--------------------------------------------------------------------------
    */
public function pdf(Payment $payment)
{
    /*
    |--------------------------------------------------------------------------
    | LOAD RELATIONS
    |--------------------------------------------------------------------------
    */
    $payment->load([
        'sale.customer',
        'sale.items.product',
        'purchase.supplier',
        'cashRegister',
        'user'
    ]);

    /*
    |--------------------------------------------------------------------------
    | GENERATE PDF
    |--------------------------------------------------------------------------
    */
    $pdf = Pdf::loadView('admin.payments.pdf', [
        'payment' => $payment
    ])->setPaper('a4','portrait');

    /*
    |--------------------------------------------------------------------------
    | FILE NAME
    |--------------------------------------------------------------------------
    */
    $filename = 'PAIEMENT-' . ($payment->payment_number ?? $payment->id) . '.pdf';

    /*
    |--------------------------------------------------------------------------
    | SAVE PDF
    |--------------------------------------------------------------------------
    */
    Storage::disk('public')->put(
        'payments/'.$filename,
        $pdf->output()
    );

    /*
    |--------------------------------------------------------------------------
    | STREAM PDF
    |--------------------------------------------------------------------------
    */
    return $pdf->stream($filename);
}
    /*
    |--------------------------------------------------------------------------
    | PRINT
    |--------------------------------------------------------------------------
    */
   public function print(Payment $payment)
{
    /*
    |--------------------------------------------------------------------------
    | LOAD RELATIONS
    |--------------------------------------------------------------------------
    */
    $payment->load([
        'sale.customer',
        'sale.items.product',
        'purchase.supplier',
        'cashRegister',
        'user'
    ]);

    /*
    |--------------------------------------------------------------------------
    | GENERATE PDF
    |--------------------------------------------------------------------------
    */
    $pdf = Pdf::loadView(
        'admin.payments.print',
        compact('payment')
    )->setPaper('a4','portrait');

    /*
    |--------------------------------------------------------------------------
    | FILE NAME
    |--------------------------------------------------------------------------
    */
    $filename = 'RECU-' . ($payment->payment_number ?? $payment->id) . '.pdf';

    /*
    |--------------------------------------------------------------------------
    | CREATE DIRECTORY
    |--------------------------------------------------------------------------
    */
    Storage::disk('public')->makeDirectory('receipts');

    /*
    |--------------------------------------------------------------------------
    | SAVE PDF
    |--------------------------------------------------------------------------
    */
    Storage::disk('public')->put(
        'receipts/'.$filename,
        $pdf->output()
    );

    /*
    |--------------------------------------------------------------------------
    | DISPLAY
    |--------------------------------------------------------------------------
    */
    return $pdf->stream($filename);
}

    public function ajaxStore(Request $request)
{
    $request->validate([
        'amount' => 'required|numeric|min:0.01',
        'payment_method' => 'required',
        'payment_date' => 'required|date',
        'cash_register_id' => 'required|exists:cash_registers,id',
    ]);

    try {

        $payment = \App\Models\Payment::create([
            'sale_id'          => $request->sale_id,
            'purchase_id'      => $request->purchase_id,
            'cash_register_id' => $request->cash_register_id,
            'type'             => $request->sale_id ? 'in' : 'out',
            'amount'           => $request->amount,
            'payment_method'   => $request->payment_method,
            'payment_date'     => $request->payment_date,
            'status'           => 'confirmed',
        ]);

        $sale = $payment->sale;

        return response()->json([
            'success' => true,
            'paid'    => $sale?->paid_amount,
            'due'     => $sale?->due_amount,
            'status'  => $sale?->status,
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 422);
    }
}

/*
|--------------------------------------------------------------------------
| THERMAL PRINTER (80mm RECEIPT)
|--------------------------------------------------------------------------
*/
public function printer(Payment $payment)
{
    /*
    |--------------------------------------------------------------------------
    | LOAD RELATIONS
    |--------------------------------------------------------------------------
    */
    $payment->load([
        'sale.customer',
        'sale.items.product',
        'purchase.supplier',
        'cashRegister',
        'user'
    ]);

    /*
    |--------------------------------------------------------------------------
    | GENERATE THERMAL PDF
    |--------------------------------------------------------------------------
    */
    $pdf = Pdf::loadView(
        'admin.payments.thermal',
        compact('payment')
    )->setPaper([0,0,226,800]); // format ticket thermique 80mm

    /*
    |--------------------------------------------------------------------------
    | FILE NAME
    |--------------------------------------------------------------------------
    */
    $filename = 'TICKET-' . ($payment->payment_number ?? $payment->id) . '.pdf';

    /*
    |--------------------------------------------------------------------------
    | SAVE FILE
    |--------------------------------------------------------------------------
    */
    Storage::disk('public')->put(
        'tickets/'.$filename,
        $pdf->output()
    );

    /*
    |--------------------------------------------------------------------------
    | DISPLAY PDF
    |--------------------------------------------------------------------------
    */
    return $pdf->stream($filename);
}

}