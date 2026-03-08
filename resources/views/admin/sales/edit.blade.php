<x-app-layout>

<div class="max-w-7xl mx-auto space-y-8"
     x-data="saleEditForm()"
     x-init="init()"
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0">

    {{-- HEADER --}}
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                Modifier Vente
            </h1>
            <p class="text-sm text-gray-500">
                Référence : {{ $sale->reference }}
            </p>
        </div>

        <a href="{{ route('admin.sales.index') }}"
           class="px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-100 transition">
            Retour
        </a>
    </div>

    @if($sale->status !== 'draft')
        <div class="bg-red-50 text-red-700 border border-red-200 p-4 rounded-xl">
            Impossible de modifier une vente confirmée ou payée.
        </div>
    @endif

    <form method="POST"
          action="{{ route('admin.sales.update',$sale) }}"
          class="space-y-8">
        @csrf
        @method('PUT')

        {{-- INFOS --}}
        <div class="bg-white p-6 rounded-2xl shadow grid md:grid-cols-3 gap-6">

            <div>
                <label class="text-sm text-gray-600">Client</label>
                <select name="customer_id"
                        class="w-full rounded-xl border-gray-300 focus:ring-indigo-500">
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}"
                            {{ $sale->customer_id == $customer->id ? 'selected':'' }}>
                            {{ $customer->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm text-gray-600">Date</label>
                <input type="date"
                       name="sale_date"
                       value="{{ $sale->sale_date->format('Y-m-d') }}"
                       class="w-full rounded-xl border-gray-300 focus:ring-indigo-500">
            </div>

            <div>
                <label class="text-sm text-gray-600">Notes</label>
                <input type="text"
                       name="notes"
                       value="{{ $sale->notes }}"
                       class="w-full rounded-xl border-gray-300 focus:ring-indigo-500">
            </div>

        </div>

        {{-- PRODUITS --}}
        <div class="bg-white rounded-2xl shadow overflow-hidden">

            <div class="p-6 border-b font-semibold text-gray-700">
                Produits
            </div>

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
                    <template x-for="(item,index) in items" :key="index">
                        <tr class="border-b hover:bg-gray-50 transition">

                            <td class="px-4 py-2">
                                <select x-model="item.product_id"
                                        @change="setPrice(index)"
                                        :name="'items['+index+'][product_id]'"
                                        class="w-full rounded-lg border-gray-300">
                                    <option value="">Choisir</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}"
                                                data-price="{{ $product->selling_price }}">
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>

                            <td class="px-4 py-2">
                                <input type="number"
                                       x-model.number="item.quantity"
                                       @input="calculate(index)"
                                       :name="'items['+index+'][quantity]'"
                                       class="w-full rounded-lg border-gray-300">
                            </td>

                            <td class="px-4 py-2">
                                <input type="number"
                                       x-model.number="item.unit_price"
                                       @input="calculate(index)"
                                       :name="'items['+index+'][unit_price]'"
                                       class="w-full rounded-lg border-gray-300">
                            </td>

                            <td class="px-4 py-2">
                                <input type="number"
                                       x-model.number="item.vat_rate"
                                       @input="calculate(index)"
                                       :name="'items['+index+'][vat_rate]'"
                                       class="w-full rounded-lg border-gray-300">
                            </td>

                            <td class="px-4 py-2">
                                <input type="number"
                                       x-model.number="item.discount_rate"
                                       @input="calculate(index)"
                                       :name="'items['+index+'][discount_rate]'"
                                       class="w-full rounded-lg border-gray-300">
                            </td>

                            <td class="px-4 py-2 font-semibold text-indigo-600">
                                <span x-text="item.total.toFixed(2)"></span>
                            </td>

                            <td class="px-4 py-2">
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

            <div class="p-4">
                <button type="button"
                        @click="addItem()"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition">
                    + Ajouter ligne
                </button>
            </div>

        </div>

        {{-- TOTALS --}}
        <div class="bg-white p-6 rounded-2xl shadow text-right space-y-2">

            <div>Sous-total : <span x-text="subtotal.toFixed(2)"></span></div>
            <div>TVA : <span x-text="vat.toFixed(2)"></span></div>
            <div>Remise : <span x-text="discount.toFixed(2)"></span></div>

            <div class="text-xl font-bold">
                Total : <span x-text="total.toFixed(2)"></span>
            </div>

        </div>

        @if($sale->status === 'draft')
        <div class="flex justify-end">
            <button type="submit"
                    class="px-6 py-3 bg-green-600 text-white rounded-2xl shadow hover:bg-green-700 transition">
                Mettre à jour
            </button>
        </div>
        @endif

    </form>

</div>


{{-- ALPINE --}}
<script>
function saleEditForm() {
    return {
        items: @json($sale->items->map(function($item){
            return [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'vat_rate' => $item->vat_rate,
                'discount_rate' => $item->discount_rate,
                'total' => $item->total,
            ];
        })),

        subtotal: 0,
        vat: 0,
        discount: 0,
        total: 0,

        init() {
            this.calculateAll();
        },

        addItem() {
            this.items.push({
                product_id:'',
                quantity:1,
                unit_price:0,
                vat_rate:0,
                discount_rate:0,
                total:0
            });
        },

        removeItem(index) {
            this.items.splice(index,1);
            this.calculateAll();
        },

        setPrice(index) {
            let select = document.querySelectorAll('select')[index+1];
            let price = select.options[select.selectedIndex].dataset.price;
            this.items[index].unit_price = parseFloat(price || 0);
            this.calculate(index);
        },

        calculate(index) {
            let item = this.items[index];

            let sub = item.quantity * item.unit_price;
            let v = sub * item.vat_rate / 100;
            let d = sub * item.discount_rate / 100;

            item.total = sub + v - d;

            this.calculateAll();
        },

        calculateAll() {
            this.subtotal = 0;
            this.vat = 0;
            this.discount = 0;
            this.total = 0;

            this.items.forEach(item=>{
                let sub = item.quantity * item.unit_price;
                let v = sub * item.vat_rate / 100;
                let d = sub * item.discount_rate / 100;

                this.subtotal += sub;
                this.vat += v;
                this.discount += d;
                this.total += item.total;
            });
        }
    }
}
</script>

</x-app-layout>