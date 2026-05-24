<x-app-layout>

<div class="max-w-7xl mx-auto space-y-8"
     x-data="saleForm()"
     x-init="init()"
     x-transition>

{{-- ======================================================
ERROR ALERT (IMPORTANT )
====================================================== --}}
@if ($errors->any())
<div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-2xl shadow-sm animate-fade-in">
    <div class="flex items-center gap-2 font-semibold mb-2">
        <x-heroicon-o-exclamation-triangle class="w-5 h-5"/>
        Erreur lors de la création de la vente
    </div>

    <ul class="text-sm space-y-1">
        @foreach ($errors->all() as $error)
            <li>• {{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif


{{-- ======================================================
HEADER
====================================================== --}}
<div class="flex justify-between items-center">

    <div>
        <h1 class="text-2xl font-bold text-gray-800">
            Nouvelle Vente
        </h1>
        <p class="text-sm text-gray-500">
            Créer une facture client
        </p>
    </div>

    <a href="{{ route('admin.sales.index') }}"
       class="btn-light">
        Retour
    </a>

</div>


<form method="POST" action="{{ route('admin.sales.store') }}" class="space-y-8">
@csrf

{{-- ======================================================
INFOS
====================================================== --}}
<div class="card grid md:grid-cols-3 gap-6">

<div>
<label class="label">Client</label>
<select name="customer_id" required class="input">
<option value="">Sélectionner</option>

@foreach($customers as $customer)
<option value="{{ $customer->id }}"
    {{ old('customer_id')==$customer->id?'selected':'' }}>
    {{ $customer->name }}
</option>
@endforeach

</select>
</div>

<div>
<label class="label">Date</label>
<input type="date"
       name="sale_date"
       value="{{ old('sale_date',date('Y-m-d')) }}"
       required
       class="input">
</div>

<div>
<label class="label">Notes</label>
<input type="text"
       name="notes"
       value="{{ old('notes') }}"
       class="input">
</div>

</div>


{{-- ======================================================
TABLE PRODUITS
====================================================== --}}
<div class="card p-0 overflow-hidden">

<div class="card-header">
Produits
</div>

<div class="overflow-x-auto">

<table class="table-erp">

<thead>
<tr>
<th>Produit</th>
<th>Qté</th>
<th>Prix</th>
<th>TVA %</th>
<th>Remise %</th>
<th>Total</th>
<th></th>
</tr>
</thead>

<tbody>

<template x-for="(item,index) in items" :key="index">

<tr>

<td>
<select x-model="item.product_id"
        @change="setPrice(index)"
        :name="'items['+index+'][product_id]'"
        required
        class="input">

<option value="">Choisir</option>

@foreach($products as $product)
<option value="{{ $product->id }}"
        data-price="{{ $product->selling_price }}">
    {{ $product->name }}
</option>
@endforeach

</select>
</td>

<td>
<input type="number" step="0.01"
       x-model.number="item.quantity"
       @input="calculate(index)"
       :name="'items['+index+'][quantity]'"
       class="input">
</td>

<td>
<input type="number" step="0.01"
       x-model.number="item.unit_price"
       @input="calculate(index)"
       :name="'items['+index+'][unit_price]'"
       class="input">
</td>

<td>
<input type="number" step="0.01"
       x-model.number="item.vat_rate"
       @input="calculate(index)"
       :name="'items['+index+'][vat_rate]'"
       class="input">
</td>

<td>
<input type="number" step="0.01"
       x-model.number="item.discount_rate"
       @input="calculate(index)"
       :name="'items['+index+'][discount_rate]'"
       class="input">
</td>

<td class="font-semibold text-indigo-600">
<span x-text="item.total.toFixed(2)"></span>
</td>

<td>
<button type="button"
        @click="removeItem(index)"
        class="text-red-500 hover:scale-110 transition">
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
        class="btn-primary">
+ Ajouter ligne
</button>
</div>

</div>


{{-- ======================================================
TOTALS
====================================================== --}}
<div class="card text-right space-y-2">

<div>Sous-total : <b x-text="subtotal.toFixed(2)"></b></div>
<div>TVA : <b x-text="vat.toFixed(2)"></b></div>
<div>Remise : <b x-text="discount.toFixed(2)"></b></div>

<div class="text-xl font-bold text-indigo-600">
Total : <span x-text="total.toFixed(2)"></span>
</div>

</div>


{{-- ======================================================
SUBMIT
====================================================== --}}
<div class="flex justify-end">
<button type="submit" class="btn-success">
Enregistrer la vente
</button>
</div>

</form>

</div>


{{-- ======================================================
ALPINE JS
====================================================== --}}
<script>
function saleForm(){
return{

items:[],
subtotal:0,
vat:0,
discount:0,
total:0,

init(){
this.addItem()
},

addItem(){
this.items.push({
product_id:'',
quantity:1,
unit_price:0,
vat_rate:0,
discount_rate:0,
total:0
})
},

removeItem(i){
this.items.splice(i,1)
this.calculateAll()
},

setPrice(i){
let selects=document.querySelectorAll('select')
let price=selects[i+1]?.selectedOptions[0]?.dataset.price
this.items[i].unit_price=parseFloat(price||0)
this.calculate(i)
},

calculate(i){
let item=this.items[i]

let sub=item.quantity*item.unit_price
let vat=sub*item.vat_rate/100
let discount=sub*item.discount_rate/100

item.total=sub+vat-discount

this.calculateAll()
},

calculateAll(){
this.subtotal=0
this.vat=0
this.discount=0
this.total=0

this.items.forEach(item=>{
let sub=item.quantity*item.unit_price
let v=sub*item.vat_rate/100
let d=sub*item.discount_rate/100

this.subtotal+=sub
this.vat+=v
this.discount+=d
this.total+=item.total
})
}
}
}
</script>


{{-- ======================================================
ERP MINI STYLE SAFE
====================================================== --}}
<style>

.card{
background:white;
border-radius:16px;
padding:24px;
box-shadow:0 1px 2px rgba(0,0,0,.05);
border:1px solid #e5e7eb;
}

.card-header{
padding:20px;
font-weight:600;
border-bottom:1px solid #eee;
}

.label{
font-size:14px;
color:#6b7280;
margin-bottom:4px;
display:block;
}

.input{
width:100%;
border:1px solid #d1d5db;
border-radius:10px;
padding:8px 12px;
}

.input:focus{
outline:none;
border-color:#6366f1;
box-shadow:0 0 0 2px rgba(99,102,241,.2);
}

.table-erp th{
background:#f9fafb;
text-align:left;
padding:12px;
font-size:12px;
text-transform:uppercase;
}

.table-erp td{
padding:10px;
border-top:1px solid #eee;
}

.btn-primary{
background:#4f46e5;
color:white;
padding:8px 16px;
border-radius:10px;
}

.btn-success{
background:#16a34a;
color:white;
padding:10px 20px;
border-radius:12px;
}

.btn-light{
border:1px solid #e5e7eb;
padding:8px 16px;
border-radius:10px;
}

</style>

</x-app-layout>