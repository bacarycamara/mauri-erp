<x-app-layout>

@can('create payments')

<div x-data="paymentForm()"
     x-init="init()"
     class="max-w-5xl mx-auto space-y-8">

    {{-- ================= HEADER ================= --}}
    <div class="flex items-start gap-3">
        <div class="p-3 bg-indigo-100 rounded-2xl">
            <x-heroicon-o-banknotes class="w-6 h-6 text-indigo-600"/>
        </div>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Nouveau Paiement</h1>
            <p class="text-sm text-gray-500 mt-1">Paiement client (entrée) ou fournisseur (sortie)</p>
        </div>
    </div>

    {{-- ================= ALERTS ================= --}}
    @if(session('error'))
    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-xl text-red-700 text-sm">
        {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-xl">
        <ul class="text-red-700 text-sm space-y-1">
            @foreach($errors->all() as $error)
                <li>• {{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- ================= FORM ================= --}}
    <form action="{{ route('admin.payments.store') }}"
          method="POST"
          @submit.prevent="validateBeforeSubmit($event)"
          class="bg-white p-8 rounded-2xl shadow space-y-8">
        @csrf

        {{-- ================= VENTE / ACHAT ================= --}}
        <div class="grid md:grid-cols-2 gap-6">

            {{-- VENTE --}}
            <div>
                <label for="sale_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Vente (Client)
                </label>
                <select id="sale_id"
                        name="sale_id"
                        x-model="sale_id"
                        @change="selectSale($event)"
                        :disabled="purchase_id !== ''"
                        class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500
                               disabled:opacity-50 disabled:cursor-not-allowed">
                    <option value="">-- Sélectionner une vente --</option>
                    @foreach($sales as $sale)
                    <option value="{{ $sale->id }}"
                            data-due="{{ $sale->getRealDueAmount() }}"
                            data-name="{{ e($sale->customer->name ?? 'Client') }}"
                            @selected(old('sale_id', $selectedSaleId) == $sale->id)>
                        {{ $sale->reference }} — {{ e($sale->customer->name ?? 'Client') }}
                        (Dû : {{ number_format($sale->getRealDueAmount(), 2) }} {{ company()?->currency }})
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- ACHAT --}}
            <div>
                <label for="purchase_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Achat (Fournisseur)
                </label>
                <select id="purchase_id"
                        name="purchase_id"
                        x-model="purchase_id"
                        @change="selectPurchase($event)"
                        :disabled="sale_id !== ''"
                        class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500
                               disabled:opacity-50 disabled:cursor-not-allowed">
                    <option value="">-- Sélectionner un achat --</option>
                    @foreach($purchases as $purchase)
                    <option value="{{ $purchase->id }}"
                            data-due="{{ $purchase->getRealDueAmount() }}"
                            data-name="{{ e($purchase->supplier->name ?? 'Fournisseur') }}"
                            @selected(old('purchase_id', $selectedPurchaseId) == $purchase->id)>
                        {{ $purchase->reference }} — {{ e($purchase->supplier->name ?? 'Fournisseur') }}
                        (Dû : {{ number_format($purchase->getRealDueAmount(), 2) }} {{ company()?->currency }})
                    </option>
                    @endforeach
                </select>
            </div>

        </div>

        {{-- INFO SÉLECTION --}}
        <div x-show="selectedName"
             x-transition
             x-cloak
             class="bg-indigo-50 border-l-4 border-indigo-400 p-4 rounded-xl">
            <p class="font-semibold text-indigo-800" x-text="selectedName"></p>
            <p class="text-sm text-indigo-600 mt-1">
                Montant dû : <strong x-text="formatCurrency(due_amount)"></strong>
            </p>
        </div>

        {{-- ================= DÉTAILS ================= --}}
        <div class="grid md:grid-cols-3 gap-6">

            {{-- MONTANT --}}
            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                    Montant <span class="text-red-500">*</span>
                </label>
                <input type="number"
                       id="amount"
                       name="amount"
                       step="0.01"
                       min="0.01"
                       x-model.number="amount"
                       :max="due_amount > 0 ? due_amount : undefined"
                       value="{{ old('amount') }}"
                       required
                       class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                {{-- ✅ Avertissement si montant dépasse le dû --}}
                <p x-show="due_amount > 0 && amount > due_amount"
                   x-cloak
                   class="text-red-500 text-xs mt-1">
                    ⚠ Le montant dépasse le montant dû.
                </p>
            </div>

            {{-- MÉTHODE --}}
            <div>
                <label for="payment_provider" class="block text-sm font-medium text-gray-700 mb-2">
                    Méthode <span class="text-red-500">*</span>
                </label>
                <select id="payment_provider"
                        name="payment_provider"
                        required
                        class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="cash"          @selected(old('payment_provider') === 'cash')>Espèces</option>
                    <option value="masrvi"         @selected(old('payment_provider') === 'masrvi')>MASRVI</option>
                    <option value="bankily"        @selected(old('payment_provider') === 'bankily')>BANKILY</option>
                    <option value="sedad"          @selected(old('payment_provider') === 'sedad')>SEDAD</option>
                    <option value="click"          @selected(old('payment_provider') === 'click')>CLICK</option>
                    <option value="bank_transfer"  @selected(old('payment_provider') === 'bank_transfer')>Virement bancaire</option>
                    <option value="check"          @selected(old('payment_provider') === 'check')>Chèque</option>
                    <option value="other"          @selected(old('payment_provider') === 'other')>Autre</option>
                </select>
            </div>

            {{-- DATE --}}
            <div>
                <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-2">
                    Date <span class="text-red-500">*</span>
                </label>
                <input type="date"
                       id="payment_date"
                       name="payment_date"
                       value="{{ old('payment_date', now()->format('Y-m-d')) }}"
                       max="{{ now()->format('Y-m-d') }}"
                       required
                       class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

        </div>

        {{-- ================= CAISSE ================= --}}
        <div>
            <label for="cash_register_id" class="block text-sm font-medium text-gray-700 mb-2">
                Caisse <span class="text-red-500">*</span>
            </label>
            <select id="cash_register_id"
                    name="cash_register_id"
                    required
                    class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                @foreach($cashRegisters as $cash)
                <option value="{{ $cash->id }}" @selected(old('cash_register_id') == $cash->id)>
                    {{ e($cash->name) }}
                    (Solde : {{ number_format($cash->current_balance, 2) }} {{ company()?->currency }})
                </option>
                @endforeach
            </select>
        </div>

        {{-- ================= NOTES ================= --}}
        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
            <textarea id="notes"
                      name="notes"
                      rows="3"
                      maxlength="500"
                      class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes') }}</textarea>
        </div>

        {{-- ================= ACTIONS ================= --}}
        <div class="flex justify-between items-center pt-2">
            <a href="{{ route('admin.payments.index') }}"
               class="px-5 py-2 border rounded-xl text-gray-600 hover:bg-gray-100 transition">
                Annuler
            </a>
            <button type="submit"
                    class="px-6 py-3 bg-indigo-600 text-white rounded-xl
                           hover:bg-indigo-700 transition shadow
                           disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="submitting">
                <span x-show="!submitting">Enregistrer Paiement</span>
                <span x-show="submitting" x-cloak>Enregistrement...</span>
            </button>
        </div>

    </form>

</div>

@else
<div class="flex flex-col items-center justify-center py-24 text-gray-400">
    <x-heroicon-o-lock-closed class="w-12 h-12 mb-4 text-gray-300"/>
    <p class="text-lg font-medium">Accès non autorisé</p>
</div>
@endcan

@push('scripts')
<script>
function paymentForm() {
    return {
        sale_id:      '{{ old("sale_id", $selectedSaleId ?? "") }}',
        purchase_id:  '{{ old("purchase_id", $selectedPurchaseId ?? "") }}',
        due_amount:   0,
        selectedName: '',
        amount:       0,
        submitting:   false,

        init() {
            // ✅ Pré-sélection si sale_id ou purchase_id passé en query string
            if (this.sale_id)     this.autoSelect('sale_id');
            if (this.purchase_id) this.autoSelect('purchase_id');
        },

        autoSelect(field) {
            const select = document.querySelector(`select[name="${field}"]`);
            if (!select) return;
            const option = [...select.options].find(o => o.value == this[field]);
            if (option) this.applyOption(option);
        },

        selectSale(e) {
            this.purchase_id = '';
            this.applyOption(e.target.selectedOptions[0]);
        },

        selectPurchase(e) {
            this.sale_id = '';
            this.applyOption(e.target.selectedOptions[0]);
        },

        applyOption(option) {
            if (!option || !option.value) {
                this.due_amount   = 0;
                this.selectedName = '';
                this.amount       = 0;
                return;
            }
            this.due_amount   = parseFloat(option.dataset.due  || 0);
            this.selectedName = option.dataset.name || '';
            this.amount       = this.due_amount;
        },

        validateBeforeSubmit(e) {
            // ✅ Vérifications côté client avant soumission
            if (!this.sale_id && !this.purchase_id) {
                alert('Veuillez sélectionner une vente ou un achat.');
                return;
            }
            if (!this.amount || this.amount <= 0) {
                alert('Le montant doit être supérieur à 0.');
                return;
            }
            if (this.due_amount > 0 && this.amount > this.due_amount) {
                if (!confirm('Le montant dépasse le montant dû. Continuer ?')) return;
            }
            // ✅ Empêche double soumission
            this.submitting = true;
            e.target.submit();
        },

        formatCurrency(value) {
            return new Intl.NumberFormat('fr-FR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value) + ' {{ company()?->currency }}';
        }
    }
}
</script>
@endpush

</x-app-layout>