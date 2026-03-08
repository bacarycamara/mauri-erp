<x-app-layout>

<div class="max-w-4xl mx-auto space-y-8"
     x-data="expenseEditForm()"
     x-init="init()"
     x-cloak>

    {{-- ================= HEADER ================= --}}
    <div class="flex justify-between items-start">

        <div class="flex items-start gap-3">

            <div class="p-3 bg-indigo-100 rounded-2xl">
                <x-heroicon-o-pencil-square class="w-6 h-6 text-indigo-600"/>
            </div>

            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Modifier Dépense
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Référence : {{ $expense->reference }}
                </p>
            </div>

        </div>

        <a href="{{ route('admin.expenses.show',$expense) }}"
           class="inline-flex items-center gap-2 px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-100 transition">
            <x-heroicon-o-arrow-left class="w-4 h-4"/>
            Retour
        </a>

    </div>


    {{-- ================= STATUS CHECK ================= --}}
    @if(!$expense->isPending())
        <div class="bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded-2xl flex items-center gap-3">
            <x-heroicon-o-lock-closed class="w-5 h-5 text-red-600"/>
            Cette dépense ne peut plus être modifiée.
        </div>
    @endif


    {{-- ================= ERRORS ================= --}}
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-2xl flex gap-3">
            <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-red-500 mt-1"/>
            <ul class="text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- ================= FORM ================= --}}
    @if($expense->isPending())
    <form method="POST"
          action="{{ route('admin.expenses.update',$expense) }}"
          class="bg-white p-8 rounded-3xl shadow-lg space-y-8 hover:shadow-xl transition"
          @submit="loading = true">

        @csrf
        @method('PUT')

        {{-- ================= ROW 1 ================= --}}
        <div class="grid md:grid-cols-2 gap-6">

            <div>
                <label class="block text-sm font-semibold mb-2 flex items-center gap-2">
                    <x-heroicon-o-tag class="w-4 h-4 text-gray-500"/>
                    Catégorie *
                </label>
                <input type="text"
                       name="category"
                       value="{{ old('category',$expense->category) }}"
                       required
                       class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2 flex items-center gap-2">
                    <x-heroicon-o-calendar-days class="w-4 h-4 text-gray-500"/>
                    Date *
                </label>
                <input type="date"
                       name="expense_date"
                       value="{{ old('expense_date',$expense->expense_date->format('Y-m-d')) }}"
                       required
                       class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500">
            </div>

        </div>


        {{-- ================= ROW 2 ================= --}}
        <div class="grid md:grid-cols-3 gap-6">

            {{-- Montant --}}
            <div>
                <label class="block text-sm font-semibold mb-2 flex items-center gap-2">
                    <x-heroicon-o-banknotes class="w-4 h-4 text-gray-500"/>
                    Montant *
                </label>
                <input type="number"
                       step="0.01"
                       min="0.01"
                       name="amount"
                       x-model="amount"
                       value="{{ old('amount',$expense->amount) }}"
                       required
                       class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500">

                <div class="text-sm text-gray-500 mt-2"
                     x-show="amount">
                    Aperçu :
                    <span class="font-semibold text-indigo-600"
                          x-text="formatCurrency(amount)"></span>
                </div>
            </div>

            {{-- Méthode --}}
            <div>
                <label class="block text-sm font-semibold mb-2 flex items-center gap-2">
                    <x-heroicon-o-credit-card class="w-4 h-4 text-gray-500"/>
                    Méthode *
                </label>
                <select name="payment_method"
                        required
                        class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500">

                    <option value="cash" @selected($expense->payment_method=='cash')>Espèces</option>
                    <option value="bank_transfer" @selected($expense->payment_method=='bank_transfer')>Virement</option>
                    <option value="mobile_money" @selected($expense->payment_method=='mobile_money')>Mobile Money</option>
                    <option value="check" @selected($expense->payment_method=='check')>Chèque</option>
                    <option value="other" @selected($expense->payment_method=='other')>Autre</option>
                </select>
            </div>

            {{-- Caisse --}}
            <div>
                <label class="block text-sm font-semibold mb-2 flex items-center gap-2">
                    <x-heroicon-o-building-library class="w-4 h-4 text-gray-500"/>
                    Caisse *
                </label>
                <select name="cash_register_id"
                        required
                        class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500">

                    @foreach($cashRegisters as $cash)
                        <option value="{{ $cash->id }}"
                            @selected($expense->cash_register_id==$cash->id)>
                            {{ $cash->name }}
                            (Solde: {{ number_format($cash->current_balance,2) }}
                            {{ company()?->currency }})
                        </option>
                    @endforeach

                </select>
            </div>

        </div>


        {{-- NOTES --}}
        <div>
            <label class="block text-sm font-semibold mb-2 flex items-center gap-2">
                <x-heroicon-o-document-text class="w-4 h-4 text-gray-500"/>
                Notes
            </label>
            <textarea name="notes"
                      rows="3"
                      class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500">{{ old('notes',$expense->notes) }}</textarea>
        </div>


        {{-- ACTIONS --}}
        <div class="flex justify-end gap-4 pt-4 border-t">

            <a href="{{ route('admin.expenses.index') }}"
               class="px-6 py-2 border rounded-xl text-gray-600 hover:bg-gray-100 transition">
                Annuler
            </a>

            <button type="submit"
                    :disabled="loading"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-xl
                           hover:bg-indigo-700 shadow-md
                           flex items-center gap-2 disabled:opacity-70 transition">

                <svg x-show="loading"
                     class="animate-spin h-4 w-4 text-white"
                     xmlns="http://www.w3.org/2000/svg"
                     fill="none"
                     viewBox="0 0 24 24">
                    <circle class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"></circle>
                    <path class="opacity-75"
                          fill="currentColor"
                          d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>

                <span x-text="loading ? 'Mise à jour...' : 'Mettre à jour'"></span>
            </button>

        </div>

    </form>
    @endif

</div>


{{-- ================= ALPINE ================= --}}
@push('scripts')
<script>
function expenseEditForm() {
    return {
        amount: {{ $expense->amount ?? 0 }},
        loading: false,

        init() {
            this.amount = this.amount || 0;
        },

        formatCurrency(value) {
            if (!value) return '';
            return new Intl.NumberFormat().format(parseFloat(value).toFixed(2))
                   + ' {{ company()?->currency }}';
        }
    }
}
</script>
@endpush

</x-app-layout>