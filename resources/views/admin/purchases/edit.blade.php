<x-app-layout>

<div 
    x-data="purchaseEdit()"
    x-init="init()"
    class="max-w-7xl mx-auto space-y-10"
>

    {{-- ================= BREADCRUMB ================= --}}
    <nav class="text-sm text-gray-500">
        <a href="{{ route('admin.purchases.index') }}"
           class="hover:text-indigo-600 transition">
            Achats
        </a>
        <span class="mx-2">/</span>
        <span class="text-gray-700 font-medium">
            Modifier
        </span>
    </nav>


    {{-- ================= HEADER ================= --}}
    <div class="flex justify-between items-start">

        <div class="flex items-start gap-4">
            <div class="p-3 bg-indigo-100 rounded-2xl">
                <x-heroicon-o-pencil-square class="w-6 h-6 text-indigo-600"/>
            </div>

            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Modifier Achat
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Référence :
                    <span class="font-semibold text-gray-700">
                        {{ $purchase->reference }}
                    </span>
                </p>
            </div>
        </div>

        <a href="{{ route('admin.purchases.index') }}"
           class="px-4 py-2 rounded-xl border border-gray-300
                  text-gray-600 hover:bg-gray-100 transition">
            Retour
        </a>

    </div>


    <form action="{{ route('admin.purchases.update',$purchase) }}" 
          method="POST"
          class="space-y-10">

        @csrf
        @method('PUT')


        {{-- ================= INFOS ================= --}}
        <div class="bg-white rounded-3xl shadow-sm border p-8 grid md:grid-cols-3 gap-8">

            <div>
                <label class="label">Fournisseur *</label>
                <select name="supplier_id" required class="input-style">
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}"
                            {{ $purchase->supplier_id == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="label">Date</label>
                <input type="date"
                       name="purchase_date"
                       value="{{ $purchase->purchase_date->format('Y-m-d') }}"
                       class="input-style">
            </div>

            <div>
                <label class="label">Notes</label>
                <input type="text"
                       name="notes"
                       value="{{ $purchase->notes }}"
                       class="input-style">
            </div>

        </div>


        {{-- ================= PRODUITS ================= --}}
        <div class="bg-white rounded-3xl shadow-sm border p-8">

            <h2 class="text-lg font-semibold mb-8">
                Lignes Produits
            </h2>

            <div class="overflow-x-auto">

                <table class="w-full text-sm">

                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Produit</th>
                        <th class="px-4 py-3 text-left">Qté</th>
                        <th class="px-4 py-3 text-left">Prix</th>
                        <th class="px-4 py-3 text-left">TVA %</th>
                        <th class="px-4 py-3 text-left">Remise %</th>
                        <th class="px-4 py-3 text-left">Total</th>
                        <th></th>
                    </tr>
                    </thead>

                    <tbody>

                    <template x-for="(item, index) in items" :key="index">

                        <tr class="border-t hover:bg-gray-50 transition">

                            <td class="px-4 py-3">
                                <select :name="'items['+index+'][product_id]'"
                                        x-model="item.product_id"
                                        class="input-style">
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>

                            <td class="px-4 py-3">
                                <input type="number"
                                       step="0.01"
                                       x-model="item.quantity"
                                       @input="calculate(index)"
                                       :name="'items['+index+'][quantity]'"
                                       class="input-style w-24">
                            </td>

                            <td class="px-4 py-3">
                                <input type="number"
                                       step="0.01"
                                       x-model="item.unit_price"
                                       @input="calculate(index)"
                                       :name="'items['+index+'][unit_price]'"
                                       class="input-style w-28">
                            </td>

                            <td class="px-4 py-3">
                                <input type="number"
                                       step="0.01"
                                       x-model="item.vat_rate"
                                       @input="calculate(index)"
                                       :name="'items['+index+'][vat_rate]'"
                                       class="input-style w-20">
                            </td>

                            <td class="px-4 py-3">
                                <input type="number"
                                       step="0.01"
                                       x-model="item.discount_rate"
                                       @input="calculate(index)"
                                       :name="'items['+index+'][discount_rate]'"
                                       class="input-style w-20">
                            </td>

                            <td class="px-4 py-3 font-semibold text-indigo-600">
                                <span x-text="format(item.total)"></span>
                                {{ company()?->currency }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                <button type="button"
                                        @click="removeItem(index)"
                                        class="text-red-500 hover:text-red-700">
                                    ✕
                                </button>
                            </td>

                        </tr>

                    </template>

                    </tbody>

                </table>

            </div>

            <div class="mt-8">
                <button type="button"
                        @click="addItem()"
                        class="px-5 py-2.5 bg-indigo-600 text-white
                               rounded-2xl shadow hover:bg-indigo-700
                               transition hover:scale-105">
                    + Ajouter ligne
                </button>
            </div>

        </div>


        {{-- ================= TOTAL ================= --}}
        <div class="bg-white rounded-3xl shadow-sm border p-8 flex justify-between items-center">

            <div class="text-lg font-semibold text-gray-700">
                Total Général :
            </div>

            <div class="text-3xl font-bold text-indigo-600">
                <span x-text="format(grandTotal)"></span>
                {{ company()?->currency }}
            </div>

        </div>


        {{-- ================= ACTIONS ================= --}}
        <div class="flex justify-end gap-4">

            <a href="{{ route('admin.purchases.index') }}"
               class="px-6 py-3 rounded-2xl border border-gray-300
                      text-gray-600 hover:bg-gray-100 transition">
                Annuler
            </a>

            <button type="submit"
                    class="px-8 py-3 bg-green-600 hover:bg-green-700
                           text-white rounded-2xl shadow-lg
                           hover:shadow-xl transition hover:scale-105">
                Mettre à jour
            </button>

        </div>

    </form>

</div>