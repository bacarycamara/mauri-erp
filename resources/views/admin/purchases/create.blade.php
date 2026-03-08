<x-app-layout>

<div
    x-data="purchaseForm(
        @js($selectedProduct ?? null),
        {{ request('qty',1) }}
    )"
    x-init="init()"
    class="max-w-7xl mx-auto space-y-10">

{{-- ================= BREADCRUMB ================= --}}
<nav class="text-sm text-gray-500">
    <a href="{{ route('admin.purchases.index') }}"
       class="hover:text-indigo-600 transition">
        Achats
    </a>
    <span class="mx-2">/</span>
    <span class="text-gray-700 font-medium">
        Nouvel achat
    </span>
</nav>


{{-- ================= HEADER ================= --}}
<div class="flex justify-between items-start">

    <div class="flex items-start gap-4">
        <div class="p-3 bg-indigo-100 rounded-2xl">
            <x-heroicon-o-shopping-cart class="w-6 h-6 text-indigo-600"/>
        </div>

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Nouvel Achat
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Création d’un approvisionnement fournisseur
            </p>
        </div>
    </div>

    <a href="{{ route('admin.purchases.index') }}"
       class="px-4 py-2 rounded-xl border border-gray-300
              text-gray-600 hover:bg-gray-100 transition">
        Retour
    </a>

</div>


<form action="{{ route('admin.purchases.store') }}"
      method="POST"
      class="space-y-10">

@csrf


{{-- ================= INFOS ================= --}}
<div class="card grid md:grid-cols-3 gap-8">

<div>
<label class="label">Fournisseur *</label>
<select name="supplier_id" required class="input-style">
<option value="">Sélectionner</option>
@foreach($suppliers as $supplier)
<option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
@endforeach
</select>
</div>

<div>
<label class="label">Date</label>
<input type="date"
       name="purchase_date"
       value="{{ now()->format('Y-m-d') }}"
       class="input-style">
</div>

<div>
<label class="label">Notes</label>
<input type="text" name="notes" class="input-style">
</div>

</div>


{{-- ================= PRODUITS ================= --}}
<div class="card">

<h2 class="text-lg font-semibold mb-8">Produits</h2>

<div class="overflow-x-auto">

<table class="w-full text-sm">

<thead class="bg-gray-50 text-gray-600 uppercase text-xs">
<tr>
<th class="px-4 py-3 text-left">Produit</th>
<th class="px-4 py-3">Qté</th>
<th class="px-4 py-3">Prix</th>
<th class="px-4 py-3">TVA %</th>
<th class="px-4 py-3">Remise %</th>
<th class="px-4 py-3">Total</th>
<th></th>
</tr>
</thead>

<tbody>

<template x-for="(item,index) in items" :key="index">

<tr class="border-t hover:bg-gray-50 transition">

<td class="px-4 py-3">
<select
    :name="'items['+index+'][product_id]'"
    x-model="item.product_id"
    @change="setProductPrice($event,index)"
    class="input-style">

<option value="">Produit</option>

@foreach($products as $product)
<option value="{{ $product->id }}"
        data-price="{{ $product->purchase_price }}">
    {{ $product->name }}
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
class="input-style w-24">
</td>

<td class="px-4 py-3">
<input type="number"
step="0.01"
x-model.number="item.unit_price"
@input="calculate(index)"
:name="'items['+index+'][unit_price]'"
class="input-style w-28">
</td>

<td class="px-4 py-3">
<input type="number"
step="0.01"
x-model.number="item.vat_rate"
@input="calculate(index)"
:name="'items['+index+'][vat_rate]'"
class="input-style w-20">
</td>

<td class="px-4 py-3">
<input type="number"
step="0.01"
x-model.number="item.discount_rate"
@input="calculate(index)"
:name="'items['+index+'][discount_rate]'"
class="input-style w-20">
</td>

<td class="px-4 py-3 font-semibold text-indigo-600">
<span x-text="format(item.total)"></span>
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
<button type="button" @click="addItem()" class="btn-primary">
+ Ajouter ligne
</button>
</div>

</div>


{{-- ================= TOTAL ================= --}}
<div class="card flex justify-between items-center">

<div class="text-lg font-semibold text-gray-700">
Total Général :
</div>

<div class="text-3xl font-bold text-indigo-600">
<span x-text="format(grandTotal)"></span>
{{ company()?->currency }}
</div>

</div>


<div class="text-right">
<button type="submit" class="btn-success">
Enregistrer l'achat
</button>
</div>

</form>

</div>


{{-- ================= ALPINE ERP ENGINE ================= --}}
<script>
function purchaseForm(selectedProduct, suggestedQty){

return{

items:[],
grandTotal:0,

init(){

    //  ouverture depuis bouton APPROVISIONNER
    if(selectedProduct){
        this.items.push({
            product_id:selectedProduct.id,
            quantity:suggestedQty || 1,
            unit_price:selectedProduct.purchase_price ?? 0,
            vat_rate:0,
            discount_rate:0,
            total:0
        });

        this.calculate(0);
    }else{
        this.addItem();
    }
},

addItem(){
    this.items.push({
        product_id:'',
        quantity:1,
        unit_price:0,
        vat_rate:0,
        discount_rate:0,
        total:0
    });
},

removeItem(index){
    this.items.splice(index,1);
    this.calculateAll();
},

//  FIX PRO : auto price when product changes
setProductPrice(event,index){

    let price = event.target.selectedOptions[0].dataset.price ?? 0;
    this.items[index].unit_price = parseFloat(price);

    this.calculate(index);
},

calculate(index){

    let i=this.items[index];

    let subtotal=i.quantity*i.unit_price;
    let vat=subtotal*i.vat_rate/100;
    let discount=subtotal*i.discount_rate/100;

    i.total=subtotal+vat-discount;

    this.calculateAll();
},

calculateAll(){
    this.grandTotal=this.items.reduce((s,i)=>s+(parseFloat(i.total)||0),0);
},

format(v){
    return new Intl.NumberFormat().format(parseFloat(v).toFixed(2));
}

}
}
</script>


<style>
.card{@apply bg-white rounded-3xl shadow-sm border p-8;}
.label{@apply block text-sm font-medium text-gray-600 mb-2;}
.input-style{@apply w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500 transition;}
.btn-primary{@apply px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl;}
.btn-success{@apply px-8 py-3 bg-green-600 hover:bg-green-700 text-white rounded-2xl shadow;}
</style>

</x-app-layout>