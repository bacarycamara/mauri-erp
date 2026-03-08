<x-app-layout>

@can('create payments')

<div x-data="paymentForm()"
     x-init="init()"
     class="max-w-5xl mx-auto space-y-8"
     x-cloak>

```
{{-- ================= HEADER ================= --}}
<div class="flex items-start gap-3">
    <div class="p-3 bg-indigo-100 rounded-2xl">
        <x-heroicon-o-banknotes class="w-6 h-6 text-indigo-600"/>
    </div>

    <div>
        <h1 class="text-2xl font-bold text-gray-900">
            Nouveau Paiement
        </h1>
        <p class="text-sm text-gray-500 mt-1">
            Paiement client (entrée) ou fournisseur (sortie)
        </p>
    </div>
</div>

{{-- ALERTS --}}
@if(session('error'))
    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-xl text-red-700 text-sm">
        {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-xl">
        <ul class="text-red-700 text-sm space-y-1">
            @foreach ($errors->all() as $error)
                <li>• {{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


{{-- ================= FORM ================= --}}
<form action="{{ route('admin.payments.store') }}"
      method="POST"
      @submit="validateBeforeSubmit"
      class="bg-white p-8 rounded-2xl shadow space-y-8">
    @csrf


    {{-- ================= TYPE ================= --}}
    <div class="grid md:grid-cols-2 gap-6">

        {{-- SALE --}}
        <div>
            <label class="block text-sm font-medium mb-2">Vente (Client)</label>

            <select name="sale_id"
                    x-model="sale_id"
                    @change="selectSale($event)"
                    :disabled="purchase_id !== ''"
                    class="w-full rounded-xl border-gray-300 focus:ring-indigo-500">

                <option value="">-- Sélectionner vente --</option>

                @foreach($sales as $sale)
                    <option value="{{ $sale->id }}"
                            data-due="{{ $sale->getRealDueAmount() }}"
                            data-name="{{ $sale->customer->name ?? 'Client' }}">
                        {{ $sale->reference }} -
                        {{ $sale->customer->name ?? 'Client' }}
                        (Dû: {{ number_format($sale->getRealDueAmount(),2) }} {{ company()?->currency }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- PURCHASE --}}
        <div>
            <label class="block text-sm font-medium mb-2">Achat (Fournisseur)</label>

            <select name="purchase_id"
                    x-model="purchase_id"
                    @change="selectPurchase($event)"
                    :disabled="sale_id !== ''"
                    class="w-full rounded-xl border-gray-300 focus:ring-indigo-500">

                <option value="">-- Sélectionner achat --</option>

                @foreach($purchases as $purchase)
                    <option value="{{ $purchase->id }}"
                            data-due="{{ $purchase->getRealDueAmount() }}"
                            data-name="{{ $purchase->supplier->name ?? 'Fournisseur' }}">
                        {{ $purchase->reference }} -
                        {{ $purchase->supplier->name ?? 'Fournisseur' }}
                        (Dû: {{ number_format($purchase->getRealDueAmount(),2) }} {{ company()?->currency }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>


    {{-- INFO --}}
    <div x-show="selectedName"
         x-transition
         class="bg-indigo-50 border-l-4 border-indigo-400 p-4 rounded-xl">

        <p class="font-semibold text-indigo-800" x-text="selectedName"></p>

        <p class="text-sm text-indigo-600 mt-1">
            Montant dû :
            <strong x-text="formatCurrency(due_amount)"></strong>
        </p>
    </div>


    {{-- DETAILS --}}
    <div class="grid md:grid-cols-3 gap-6">

        {{-- AMOUNT --}}
        <div>
            <label class="block text-sm font-medium mb-2">Montant</label>

            <input type="number"
                   name="amount"
                   step="0.01"
                   x-model.number="amount"
                   :max="due_amount"
                   min="0.01"
                   required
                   class="w-full rounded-xl border-gray-300 focus:ring-indigo-500">
        </div>

        {{-- METHOD --}}
        <div>
            <label class="block text-sm font-medium mb-2">Méthode</label>

            <select name="payment_provider"
                    required
                    class="w-full rounded-xl border-gray-300 focus:ring-indigo-500">

                <option value="cash">Espèces</option>

                <option value="masrvi">MASRVI</option>
                <option value="bankily">BANKILY</option>
                <option value="sedad">SEDAD</option>
                <option value="click">CLICK</option>

                <option value="bank_transfer">Virement bancaire</option>
                <option value="check">Chèque</option>
                <option value="other">Autre</option>

            </select>
        </div>

        {{-- DATE --}}
        <div>
            <label class="block text-sm font-medium mb-2">Date</label>

            <input type="date"
                   name="payment_date"
                   value="{{ now()->format('Y-m-d') }}"
                   required
                   class="w-full rounded-xl border-gray-300 focus:ring-indigo-500">
        </div>

    </div>


    {{-- CAISSE --}}
    <div>
        <label class="block text-sm font-medium mb-2">Caisse</label>

        <select name="cash_register_id"
                required
                class="w-full rounded-xl border-gray-300 focus:ring-indigo-500">
            @foreach($cashRegisters as $cash)
                <option value="{{ $cash->id }}">
                    {{ $cash->name }}
                    (Solde: {{ number_format($cash->current_balance,2) }} {{ company()?->currency }})
                </option>
            @endforeach
        </select>
    </div>


    {{-- NOTES --}}
    <div>
        <label class="block text-sm font-medium mb-2">Notes</label>
        <textarea name="notes"
                  rows="3"
                  class="w-full rounded-xl border-gray-300 focus:ring-indigo-500"></textarea>
    </div>


    {{-- SUBMIT --}}
    <div class="flex justify-end">
        <button type="submit"
                class="px-6 py-3 bg-indigo-600 text-white rounded-xl
                       hover:bg-indigo-700 transition shadow">
            Enregistrer Paiement
        </button>
    </div>

</form>
```

</div>

@endcan

{{-- ================= ALPINE (INCHANGÉ) ================= --}}
@push('scripts')

<script>
function paymentForm() {
    return {

        sale_id: '{{ request("sale_id") ?? "" }}',
        purchase_id: '{{ request("purchase_id") ?? "" }}',

        due_amount: 0,
        selectedName: '',
        amount: 0,

        init() {
            if (this.sale_id) this.autoSelect('sale_id');
            if (this.purchase_id) this.autoSelect('purchase_id');
        },

        autoSelect(field) {
            const select = document.querySelector(`select[name="${field}"]`);
            if (!select) return;

            const option = [...select.options]
                .find(o => o.value == this[field]);

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
            if (!option) return;

            this.due_amount = parseFloat(option.dataset.due || 0);
            this.selectedName = option.dataset.name || '';
            this.amount = this.due_amount;
        },

        validateBeforeSubmit(e) {

            if (!this.sale_id && !this.purchase_id) {
                alert("Veuillez sélectionner une vente ou un achat.");
                e.preventDefault();
                return;
            }

            if (this.amount <= 0) {
                alert("Montant invalide.");
                e.preventDefault();
                return;
            }

            if (this.amount > this.due_amount) {
                alert("Le montant dépasse le montant dû.");
                e.preventDefault();
            }
        },

        formatCurrency(value) {
            return new Intl.NumberFormat().format(value)
                + ' {{ company()?->currency }}';
        }
    }
}
</script>

@endpush

</x-app-layout>
