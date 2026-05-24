<x-app-layout>

@can('create purchases')

@php
    $currency = company()?->currency ?? '';
@endphp

<div x-data="purchaseForm(
        @js($selectedProduct ?? null),
        {{ (int) request('qty', 1) }}
     )"
     x-init="init()"
     class="max-w-7xl mx-auto space-y-8">

    {{-- ================= BREADCRUMB ================= --}}
    <nav class="text-sm text-gray-500 flex items-center gap-2">
        <a href="{{ route('admin.purchases.index') }}"
           class="hover:text-indigo-600 transition">Achats</a>
        <x-heroicon-o-chevron-right class="w-4 h-4 text-gray-400"/>
        <span class="text-gray-700 font-medium">Nouvel achat</span>
    </nav>


    {{-- ================= HEADER ================= --}}
    <div class="flex justify-between items-start flex-wrap gap-4">
        <div class="flex items-start gap-4">
            <div class="p-3 bg-indigo-100 rounded-2xl">
                <x-heroicon-o-shopping-cart class="w-6 h-6 text-indigo-600"/>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Nouvel Achat</h1>
                <p class="text-sm text-gray-500 mt-1">Création d'un approvisionnement fournisseur</p>
            </div>
        </div>
        <a href="{{ route('admin.purchases.index') }}"
           class="px-4 py-2 rounded-xl border border-gray-300 text-gray-600 hover:bg-gray-100 transition text-sm">
            Retour
        </a>
    </div>


    {{-- ================= ERREURS ================= --}}
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-2xl text-sm">
        <p class="font-semibold mb-1">Veuillez corriger les erreurs :</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif


    {{-- ================= FORM ================= --}}
    <form action="{{ route('admin.purchases.store') }}"
          method="POST"
          x-ref="form"
          @submit="submitting = true"
          class="space-y-8">
        @csrf


        {{-- ================= INFOS GÉNÉRALES ================= --}}
        <div class="bg-white rounded-3xl shadow-sm border p-8">
            <div class="grid md:grid-cols-3 gap-6">

                <div>
                    <label for="supplier_id"
                           class="block text-sm font-medium text-gray-600 mb-2">
                        Fournisseur <span class="text-red-500">*</span>
                    </label>
                    <select id="supplier_id"
                            name="supplier_id"
                            required
                            class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500
                                   @error('supplier_id') border-red-400 @enderror">
                        <option value="">Sélectionner</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}"
                                @selected(old('supplier_id') == $supplier->id)>
                            {{ e($supplier->name) }}
                        </option>
                        @endforeach
                    </select>
                    @error('supplier_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="purchase_date"
                           class="block text-sm font-medium text-gray-600 mb-2">
                        Date
                    </label>
                    <input type="date"
                           id="purchase_date"
                           name="purchase_date"
                           value="{{ old('purchase_date', now()->format('Y-m-d')) }}"
                           max="{{ now()->format('Y-m-d') }}"
                           class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="notes"
                           class="block text-sm font-medium text-gray-600 mb-2">
                        Notes
                    </label>
                    <input type="text"
                           id="notes"
                           name="notes"
                           value="{{ old('notes') }}"
                           maxlength="500"
                           class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500">
                </div>

            </div>
        </div>


        {{-- ================= LIGNES PRODUITS ================= --}}
        <div class="bg-white rounded-3xl shadow-sm border p-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-6">Produits</h2>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Produit</th>
                        <th class="px-4 py-3 text-center">Qté</th>
                        <th class="px-4 py-3 text-center">Prix</th>
                        <th class="px-4 py-3 text-center">TVA %</th>
                        <th class="px-4 py-3 text-center">Remise %</th>
                        <th class="px-4 py-3 text-center">Total</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                    </thead>
                    <tbody>

                    <template x-for="(item, index) in items" :key="index">
                    <tr class="border-t hover:bg-gray-50 transition">

                        <td class="px-4 py-3">
                            <select :name="'items['+index+'][product_id]'"
                                    x-model="item.product_id"
                                    @change="setProductPrice($event, index)"
                                    class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500 text-sm">
                                <option value="">Sélectionner</option>
                                @foreach($products as $product)
                                <option value="{{ $product->id }}"
                                        data-price="{{ (float) $product->purchase_price }}">
                                    {{ e($product->name) }}
                                </option>
                                @endforeach
                            </select>
                        </td>

                        <td class="px-4 py-3">
                            <input type="number"
                                   min="1"
                                   step="0.01"
                                   x-model.number="item.quantity"
                                   @input="calculate(index)"
                                   :name="'items['+index+'][quantity]'"
                                   class="w-24 rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500 text-sm text-center">
                        </td>

                        <td class="px-4 py-3">
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   x-model.number="item.unit_price"
                                   @input="calculate(index)"
                                   :name="'items['+index+'][unit_price]'"
                                   class="w-28 rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500 text-sm text-center">
                        </td>

                        <td class="px-4 py-3">
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   max="100"
                                   x-model.number="item.vat_rate"
                                   @input="calculate(index)"
                                   :name="'items['+index+'][vat_rate]'"
                                   class="w-20 rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500 text-sm text-center">
                        </td>

                        <td class="px-4 py-3">
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   max="100"
                                   x-model.number="item.discount_rate"
                                   @input="calculate(index)"
                                   :name="'items['+index+'][discount_rate]'"
                                   class="w-20 rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500 text-sm text-center">
                        </td>

                        <td class="px-4 py-3 text-center font-semibold text-indigo-600">
                            <span x-text="format(item.total)"></span>
                        </td>

                        <td class="px-4 py-3 text-right">
                            <button type="button"
                                    @click="removeItem(index)"
                                    class="text-red-400 hover:text-red-600 transition p-1 rounded-lg hover:bg-red-50">
                                <x-heroicon-o-x-mark class="w-4 h-4"/>
                            </button>
                        </td>

                    </tr>
                    </template>

                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                <button type="button"
                        @click="addItem()"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600
                               hover:bg-indigo-700 text-white rounded-2xl text-sm transition">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Ajouter ligne
                </button>
            </div>
        </div>


        {{-- ================= TOTAL ================= --}}
        <div class="bg-white rounded-3xl shadow-sm border p-6 flex justify-between items-center">
            <span class="text-lg font-semibold text-gray-700">Total Général :</span>
            <div class="text-3xl font-bold text-indigo-600">
                <span x-text="format(grandTotal)"></span>
                {{ $currency }}
            </div>
        </div>


        {{-- ================= SUBMIT ================= --}}
        {{-- ✅ FIX : le bouton est dans le même scope Alpine (purchaseForm) --}}
        <div class="flex justify-end">
            <button type="submit"
                    :disabled="submitting || items.length === 0"
                    class="px-8 py-3 bg-green-600 hover:bg-green-700 text-white rounded-2xl
                           shadow transition disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="!submitting">Enregistrer l'achat</span>
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
function purchaseForm(selectedProduct, suggestedQty) {
    return {
        items:      [],
        grandTotal: 0,
        submitting: false, // ✅ déplacé ici dans le bon scope

        init() {
            if (selectedProduct) {
                this.items.push({
                    product_id:    selectedProduct.id,
                    quantity:      suggestedQty || 1,
                    unit_price:    parseFloat(selectedProduct.purchase_price) || 0,
                    vat_rate:      0,
                    discount_rate: 0,
                    total:         0
                });
                this.calculate(0);
            } else {
                this.addItem();
            }
        },

        addItem() {
            this.items.push({
                product_id:    '',
                quantity:      1,
                unit_price:    0,
                vat_rate:      0,
                discount_rate: 0,
                total:         0
            });
        },

        removeItem(index) {
            this.items.splice(index, 1);
            this.calculateAll();
        },

        setProductPrice(event, index) {
            const price = parseFloat(event.target.selectedOptions[0]?.dataset.price) || 0;
            this.items[index].unit_price = price;
            this.calculate(index);
        },

        calculate(index) {
            const i        = this.items[index];
            const qty      = parseFloat(i.quantity)      || 0;
            const price    = parseFloat(i.unit_price)    || 0;
            const vat      = parseFloat(i.vat_rate)      || 0;
            const discount = parseFloat(i.discount_rate) || 0;

            const subtotal    = qty * price;
            const vatAmount   = subtotal * vat      / 100;
            const discountAmt = subtotal * discount / 100;

            i.total = parseFloat((subtotal + vatAmount - discountAmt).toFixed(2));

            this.calculateAll();
        },

        calculateAll() {
            this.grandTotal = this.items.reduce(
                (sum, i) => sum + (parseFloat(i.total) || 0), 0
            );
        },

        format(v) {
            return new Intl.NumberFormat('fr-FR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(parseFloat(v) || 0);
        }
    }
}
</script>
@endpush

</x-app-layout>