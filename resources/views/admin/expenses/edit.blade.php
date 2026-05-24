<x-app-layout>

@can('edit expenses')

@php
    $currency = company()?->currency ?? '';
@endphp

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
                <h1 class="text-2xl font-bold text-gray-900">Modifier Dépense</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Référence : {{ $expense->reference }}
                </p>
            </div>
        </div>
        <a href="{{ route('admin.expenses.show', $expense) }}"
           class="inline-flex items-center gap-2 px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-100 transition">
            <x-heroicon-o-arrow-left class="w-4 h-4"/>
            Retour
        </a>
    </div>


    {{-- ================= STATUS CHECK ================= --}}
    @if($expense->status !== 'pending')
    <div class="bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded-2xl flex items-center gap-3">
        <x-heroicon-o-lock-closed class="w-5 h-5 text-red-600 flex-shrink-0"/>
        Cette dépense ne peut plus être modifiée (statut : {{ $expense->status }}).
    </div>
    @endif


    {{-- ================= ERREURS ================= --}}
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-2xl flex gap-3">
        <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-red-500 mt-1 flex-shrink-0"/>
        <ul class="text-sm space-y-1">
            @foreach($errors->all() as $error)
                <li>• {{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif


    {{-- ================= FORM (seulement si pending) ================= --}}
    @if($expense->status === 'pending')
    <form method="POST"
          action="{{ route('admin.expenses.update', $expense) }}"
          class="bg-white p-8 rounded-3xl shadow-lg space-y-8 hover:shadow-xl transition"
          @submit.prevent="loading = true; $el.submit()">
        @csrf
        @method('PUT')


        {{-- ROW 1 : Catégorie + Date --}}
        <div class="grid md:grid-cols-2 gap-6">

            <div>
                <label for="category"
                       class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                    <x-heroicon-o-tag class="w-4 h-4 text-gray-500"/>
                    Catégorie <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="category"
                       name="category"
                       value="{{ old('category', $expense->category) }}"
                       required
                       maxlength="100"
                       class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500
                              @error('category') border-red-400 @enderror">
                @error('category')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="expense_date"
                       class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                    <x-heroicon-o-calendar-days class="w-4 h-4 text-gray-500"/>
                    Date <span class="text-red-500">*</span>
                </label>
                <input type="date"
                       id="expense_date"
                       name="expense_date"
                       value="{{ old('expense_date', $expense->expense_date?->format('Y-m-d')) }}"
                       max="{{ now()->format('Y-m-d') }}"
                       required
                       class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500
                              @error('expense_date') border-red-400 @enderror">
                @error('expense_date')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

        </div>


        {{-- ROW 2 : Montant + Méthode + Caisse --}}
        <div class="grid md:grid-cols-3 gap-6">

            <div>
                <label for="amount"
                       class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                    <x-heroicon-o-banknotes class="w-4 h-4 text-gray-500"/>
                    Montant <span class="text-red-500">*</span>
                </label>
                <input type="number"
                       id="amount"
                       name="amount"
                       step="0.01"
                       min="0.01"
                       x-model.number="amount"
                       value="{{ old('amount', $expense->amount) }}"
                       required
                       class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500
                              @error('amount') border-red-400 @enderror">
                <div class="text-sm text-gray-500 mt-2" x-show="amount > 0" x-cloak>
                    Aperçu :
                    <span class="font-semibold text-indigo-600"
                          x-text="formatCurrency(amount)"></span>
                </div>
                @error('amount')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="payment_method"
                       class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                    <x-heroicon-o-credit-card class="w-4 h-4 text-gray-500"/>
                    Méthode <span class="text-red-500">*</span>
                </label>
                <select id="payment_method"
                        name="payment_method"
                        required
                        class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500">
                    @php
                        $pm = old('payment_method', $expense->payment_method);
                    @endphp
                    <option value="cash"          @selected($pm === 'cash')>Espèces</option>
                    <option value="masrvi"         @selected($pm === 'masrvi')>MASRVI</option>
                    <option value="bankily"        @selected($pm === 'bankily')>BANKILY</option>
                    <option value="sedad"          @selected($pm === 'sedad')>SEDAD</option>
                    <option value="click"          @selected($pm === 'click')>CLICK</option>
                    <option value="bank_transfer"  @selected($pm === 'bank_transfer')>Virement bancaire</option>
                    <option value="mobile_money"   @selected($pm === 'mobile_money')>Mobile Money</option>
                    <option value="check"          @selected($pm === 'check')>Chèque</option>
                    <option value="other"          @selected($pm === 'other')>Autre</option>
                </select>
            </div>

            <div>
                <label for="cash_register_id"
                       class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                    <x-heroicon-o-building-library class="w-4 h-4 text-gray-500"/>
                    Caisse <span class="text-red-500">*</span>
                </label>
                <select id="cash_register_id"
                        name="cash_register_id"
                        required
                        class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500
                               @error('cash_register_id') border-red-400 @enderror">
                    @foreach($cashRegisters as $cash)
                    <option value="{{ $cash->id }}"
                            @selected(old('cash_register_id', $expense->cash_register_id) == $cash->id)>
                        {{ e($cash->name) }}
                        (Solde : {{ number_format($cash->current_balance ?? 0, 2) }} {{ $currency }})
                    </option>
                    @endforeach
                </select>
                @error('cash_register_id')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

        </div>


        {{-- NOTES --}}
        <div>
            <label for="notes"
                   class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                <x-heroicon-o-document-text class="w-4 h-4 text-gray-500"/>
                Notes
            </label>
            <textarea id="notes"
                      name="notes"
                      rows="3"
                      maxlength="1000"
                      class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500">{{ old('notes', $expense->notes) }}</textarea>
        </div>


        {{-- ACTIONS --}}
        <div class="flex justify-between items-center pt-4 border-t flex-wrap gap-4">

            @can('delete expenses')
            <form action="{{ route('admin.expenses.destroy', $expense) }}"
                  method="POST"
                  onsubmit="return confirm('Supprimer définitivement cette dépense ?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2 rounded-xl
                               border border-red-300 text-red-600 hover:bg-red-50 transition">
                    <x-heroicon-o-trash class="w-4 h-4"/>
                    Supprimer
                </button>
            </form>
            @endcan

            @cannot('delete expenses')
            <span></span>
            @endcannot

            <div class="flex gap-4">
                <a href="{{ route('admin.expenses.index') }}"
                   class="px-6 py-2 border rounded-xl text-gray-600 hover:bg-gray-100 transition">
                    Annuler
                </a>

                <button type="submit"
                        :disabled="loading"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-xl
                               hover:bg-indigo-700 shadow-md flex items-center gap-2
                               disabled:opacity-70 disabled:cursor-not-allowed transition">

                    <svg x-show="loading"
                         x-cloak
                         class="animate-spin h-4 w-4 text-white"
                         xmlns="http://www.w3.org/2000/svg"
                         fill="none"
                         viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>

                    <span x-text="loading ? 'Mise à jour...' : 'Mettre à jour'"></span>

                </button>
            </div>

        </div>

    </form>
    @endif

</div>

@else
<div class="flex flex-col items-center justify-center py-24 text-gray-400">
    <x-heroicon-o-lock-closed class="w-12 h-12 mb-4 text-gray-300"/>
    <p class="text-lg font-medium">Accès non autorisé</p>
</div>
@endcan


@push('scripts')
<script>
function expenseEditForm() {
    return {
        // ✅ cast float + old() pour restauration après erreur
        amount:  {{ (float) old('amount', $expense->amount ?? 0) }},
        loading: false,

        init() {},

        formatCurrency(value) {
            if (!value || value <= 0) return '';
            return new Intl.NumberFormat('fr-FR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(parseFloat(value)) + ' {{ $currency }}';
        }
    }
}
</script>
@endpush

</x-app-layout>