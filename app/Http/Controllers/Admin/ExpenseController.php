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
    | INDEX
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = Expense::with(['cashRegister', 'approvedBy']);

        $query->search($request->search);
        $query->status($request->status);
        $query->betweenDates($request->from, $request->to);

        if ($request->cash_register_id) {
            $query->where('cash_register_id', $request->cash_register_id);
        }

        $expenses      = $query->latest()->paginate(20)->withQueryString();
        $cashRegisters = CashRegister::where('status', 'open')->get();

        // ✅ Totaux globaux calculés en DB — pas sur la page courante seulement
        $totalPending   = Expense::where('status', 'pending')->sum('amount');
        $totalApproved  = Expense::where('status', 'approved')->sum('amount');
        $totalCancelled = Expense::where('status', 'cancelled')->sum('amount');
        $totalGlobal    = Expense::sum('amount');

        return view('admin.expenses.index', compact(
            'expenses',
            'cashRegisters',
            'totalPending',
            'totalApproved',
            'totalCancelled',
            'totalGlobal'
        ));
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
    | STORE
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'category'         => 'required|string|max:255',
            'expense_date'     => 'required|date|before_or_equal:today',
            'amount'           => 'required|numeric|min:0.01',
            'cash_register_id' => 'required|exists:cash_registers,id',
            'payment_method'   => 'required|string',
        ]);

        $method = match ($request->payment_method) {
            'cash'                                => 'cash',
            'masrvi', 'bankily', 'sedad', 'click' => 'mobile_money',
            'bank_transfer'                       => 'bank_transfer',
            'check'                               => 'check',
            default                               => 'other',
        };

        try {
            Expense::create([
                'category'         => $request->category,
                'expense_date'     => $request->expense_date,
                'amount'           => $request->amount,
                'payment_method'   => $method,
                'cash_register_id' => $request->cash_register_id,
                'status'           => 'pending',
                'notes'            => $request->notes,
            ]);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        DashboardController::clearCache(auth()->user());

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
    | EDIT
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
            'expense_date'     => 'required|date|before_or_equal:today',
            'amount'           => 'required|numeric|min:0.01',
            'cash_register_id' => 'required|exists:cash_registers,id',
            'payment_method'   => 'required|string',
        ]);

        $method = match ($request->payment_method) {
            'cash'                                => 'cash',
            'masrvi', 'bankily', 'sedad', 'click' => 'mobile_money',
            'bank_transfer'                       => 'bank_transfer',
            'check'                               => 'check',
            default                               => 'other',
        };

        try {
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
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        DashboardController::clearCache(auth()->user());

        return redirect()
            ->route('admin.expenses.index')
            ->with('success', 'Dépense mise à jour.');
    }

    /*
    |--------------------------------------------------------------------------
    | APPROVE
    | ✅ CORRIGÉ : try/catch pour afficher l'erreur à l'utilisateur
    |    au lieu d'une page 500.
    |--------------------------------------------------------------------------
    */
    public function approve(Expense $expense)
    {
        if (!$expense->isPending()) {
            return back()->with('error', 'Cette dépense ne peut pas être approuvée.');
        }

        try {
            DB::transaction(function () use ($expense) {
                $expense->update(['status' => 'approved']);
            });
        } catch (\Exception $e) {
            // ✅ L'exception de processApproval() (solde insuffisant, caisse fermée)
            // est renvoyée à l'utilisateur comme message d'erreur flash
            return back()->with('error', $e->getMessage());
        }

        DashboardController::clearCache(auth()->user());

        return back()->with('success', 'Dépense approuvée avec succès.');
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

        try {
            DB::transaction(function () use ($expense) {
                $expense->update(['status' => 'cancelled']);
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        DashboardController::clearCache(auth()->user());

        return back()->with('success', 'Dépense annulée.');
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */
    public function destroy(Expense $expense)
    {
        if (!$expense->isPending()) {
            return back()->with('error', 'Seules les dépenses en attente peuvent être supprimées.');
        }

        $expense->delete();
        DashboardController::clearCache(auth()->user());

        return back()->with('success', 'Dépense supprimée.');
    }

    /*
    |--------------------------------------------------------------------------
    | EXPORT
    |--------------------------------------------------------------------------
    */
    public function export(Request $request)
    {
        $query = Expense::with(['cashRegister', 'approvedBy']);

        $query->search($request->search);
        $query->status($request->status);
        $query->betweenDates($request->from, $request->to);

        $expenses = $query->latest()->get();

        $currency = company()?->currency ?? '';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="depenses_' . now()->format('Ymd_His') . '.csv"',
        ];

        $callback = function () use ($expenses, $currency) {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 pour Excel
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Référence', 'Catégorie', 'Montant (' . $currency . ')',
                'Méthode', 'Caisse', 'Statut', 'Date', 'Approuvé par',
            ], ';');

            foreach ($expenses as $expense) {
                fputcsv($handle, [
                    $expense->reference,
                    $expense->category,
                    number_format($expense->amount, 2, '.', ''),
                    $expense->payment_method,
                    $expense->cashRegister?->name ?? '-',
                    $expense->status,
                    $expense->expense_date?->format('d/m/Y') ?? '-',
                    $expense->approvedBy?->name ?? '-',
                ], ';');
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}