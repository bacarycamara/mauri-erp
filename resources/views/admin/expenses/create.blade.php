<x-app-layout>

<div class="max-w-4xl mx-auto space-y-8"
     x-data="expenseForm()"
     x-cloak
     x-init="init()">

    {{-- ================= HEADER ================= --}}
    <div class="flex justify-between items-start">

        <div class="flex items-start gap-3">

            <div class="p-3 bg-red-100 rounded-2xl">
                <x-heroicon-o-receipt-percent class="w-6 h-6 text-red-600"/>
            </div>

            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Nouvelle Dépense
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Enregistrement d'une nouvelle dépense (validation requise)
                </p>
            </div>

        </div>

        <a href="{{ route('admin.expenses.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-100 transition">
            <x-heroicon-o-arrow-left class="w-4 h-4"/>
            Retour
        </a>

    </div>


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


    {{-- ================= FORM CARD ================= --}}
    <form method="POST"
          action="{{ route('admin.expenses.store') }}"
          class="bg-white p-8 rounded-3xl shadow-lg space-y-8 transition hover:shadow-xl"
          @submit="loading = true">

        @csrf

        {{-- ================= ROW 1 ================= --}}
        <div class="grid md:grid-cols-2 gap-6">

            {{-- Catégorie --}}
            <div>
                <label class="block text-sm font-semibold mb-2 flex items-center gap-2">
                    <x-heroicon-o-tag class="w-4 h-4 text-gray-500"/>
                    Catégorie *
                </label>
                <input type="text"
                       name="category"
                       value="{{ old('category') }}"
                       required
                       class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500">
            </div>

            {{-- Date --}}
            <div>
                <label class="block text-sm font-semibold mb-2 flex items-center gap-2">
                    <x-heroicon-o-calendar-days class="w-4 h-4 text-gray-500"/>
                    Date *
                </label>
                <input type="date"
                       name="expense_date"
                       value="{{ old('expense_date', now()->format('Y-m-d')) }}"
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
                       value="{{ old('amount') }}"
                       required
                       class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500">

                <div class="text-sm text-gray-500 mt-2"
                     x-show="amount">
                    Aperçu :
                    <span class="font-semibold text-indigo-600"
                          x-text="formatCurrency(amount)"></span>
                </div>
            </div>

            {{-- ================= MÉTHODE (même que payments) ================= --}}
            <div>
                <label class="block text-sm font-semibold mb-2 flex items-center gap-2">
                    <x-heroicon-o-credit-card class="w-4 h-4 text-gray-500"/>
                    Méthode *
                </label>
                <select name="payment_method"
                        required
                        class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500">

                    <option value="cash"          {{ old('payment_method') == 'cash'          ? 'selected' : '' }}>Espèces</option>

                    <option value="masrvi"        {{ old('payment_method') == 'masrvi'        ? 'selected' : '' }}>MASRVI</option>
                    <option value="bankily"       {{ old('payment_method') == 'bankily'       ? 'selected' : '' }}>BANKILY</option>
                    <option value="sedad"         {{ old('payment_method') == 'sedad'         ? 'selected' : '' }}>SEDAD</option>
                    <option value="click"         {{ old('payment_method') == 'click'         ? 'selected' : '' }}>CLICK</option>

                    <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Virement bancaire</option>
                    <option value="check"         {{ old('payment_method') == 'check'         ? 'selected' : '' }}>Chèque</option>
                    <option value="other"         {{ old('payment_method') == 'other'         ? 'selected' : '' }}>Autre</option>

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

                    <option value="">Sélectionner</option>

                    @foreach($cashRegisters as $cash)
                        <option value="{{ $cash->id }}" {{ old('cash_register_id') == $cash->id ? 'selected' : '' }}>
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
                      class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500">{{ old('notes') }}</textarea>
        </div>


        {{-- STATUS INFO --}}
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded-2xl flex items-center gap-3">
            <x-heroicon-o-clock class="w-5 h-5 text-yellow-600"/>
            Cette dépense sera enregistrée en attente de validation.
        </div>


        {{-- ACTIONS --}}
        <div class="flex justify-end gap-4 pt-4 border-t">

            <a href="{{ route('admin.expenses.index') }}"
               class="px-6 py-2 border rounded-xl text-gray-600 hover:bg-gray-100 transition">
                Annuler
            </a>

            <button type="submit"
                    :disabled="loading"
                    class="px-6 py-2 bg-red-600 text-white rounded-xl
                           hover:bg-red-700 transition shadow-md
                           flex items-center gap-2 disabled:opacity-70">

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

                <span x-text="loading ? 'Enregistrement...' : 'Enregistrer Dépense'"></span>
            </button>

        </div>

    </form>

</div>


{{-- ================= ALPINE ================= --}}
@push('scripts')
<script>
function expenseForm() {
    return {
        amount: 0,
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