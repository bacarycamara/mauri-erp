<x-app-layout>

@can('view cash_transactions')

<div class="max-w-7xl mx-auto space-y-8"
     x-data="cashPage()"
     x-init="init()"
     x-cloak>

{{-- ================= HEADER ================= --}}

<div class="flex flex-col md:flex-row md:justify-between md:items-center gap-6">

<div>
<div class="flex items-center gap-4">

<x-heroicon-o-arrows-right-left class="w-8 h-8 text-indigo-600"/>

<h1 class="text-3xl font-bold text-gray-900">
@if($cashRegister)
Transactions — {{ $cashRegister->name }}
@else
Toutes les Transactions
@endif
</h1>

@if($cashRegister) <span class="inline-flex items-center gap-2 px-3 py-1 text-xs font-semibold rounded-full"
   :class="statusClass"> <x-heroicon-o-check-circle class="w-3 h-3"/> <span x-text="statusLabel"></span> </span>
@endif

</div>

<p class="text-gray-500 text-sm mt-1">
Gestion des mouvements de caisse
</p>
</div>

<div class="flex flex-wrap gap-3">

@if($cashRegister)

@can('export cash_transactions') <a href="{{ route('admin.cash-transactions.pdf',$cashRegister) }}"
class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl shadow hover:bg-indigo-700 transition hover:scale-105"> <x-heroicon-o-document-text class="w-4 h-4"/>
PDF </a>
@endcan

@can('print cash_transactions') <a href="{{ route('admin.cash-transactions.print',$cashRegister) }}"
target="_blank"
class="flex items-center gap-2 px-4 py-2 bg-gray-800 text-white rounded-xl shadow hover:bg-black transition hover:scale-105"> <x-heroicon-o-printer class="w-4 h-4"/>
Imprimer </a>
@endcan

@endif

@can('create cash_transactions')
@if($cashRegister && $cashRegister->isOpen())
<button @click="showModal=true"
class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-xl shadow hover:bg-green-700 transition hover:scale-105"> <x-heroicon-o-plus class="w-4 h-4"/>
Nouvelle Transaction </button>
@endif
@endcan

</div>
</div>

{{-- ================= STATS ================= --}}

<div class="grid md:grid-cols-3 gap-6">

<div class="bg-green-50 border border-green-200 p-6 rounded-2xl shadow">
<p class="text-sm font-semibold text-green-700">Total Entrées</p>
<p class="text-2xl font-bold text-green-900 mt-2"
   x-text="formatCurrency(totalIn)"></p>
</div>

<div class="bg-red-50 border border-red-200 p-6 rounded-2xl shadow">
<p class="text-sm font-semibold text-red-700">Total Sorties</p>
<p class="text-2xl font-bold text-red-900 mt-2"
   x-text="formatCurrency(totalOut)"></p>
</div>

<div class="bg-indigo-700 text-white p-6 rounded-2xl shadow">
<p class="text-sm opacity-80">Solde Actuel</p>
<p class="text-3xl font-bold mt-2"
   x-text="formatCurrency(animatedBalance)"></p>
</div>

</div>

{{-- ================= TABLE ================= --}}

<div class="bg-white rounded-2xl shadow overflow-hidden">
<div class="overflow-x-auto">

<table class="w-full text-sm">

<thead class="bg-gray-100 text-gray-800 font-semibold">
<tr>
@if(!$cashRegister)
<th class="px-6 py-3 text-left">Caisse</th>
@endif
<th class="px-6 py-3 text-left">Référence</th>
<th class="px-6 py-3 text-left">Type</th>
<th class="px-6 py-3 text-left">Montant</th>
<th class="px-6 py-3 text-left">Source</th>
<th class="px-6 py-3 text-left">Description</th>
<th class="px-6 py-3 text-left">Date</th>
<th class="px-6 py-3 text-right">Actions</th>
</tr>
</thead>

<tbody class="divide-y">

@forelse($transactions as $transaction)

<tr class="hover:bg-gray-50">

@if(!$cashRegister)

<td class="px-6 py-4">
{{ $transaction->cashRegister?->name ?? '-' }}
</td>
@endif

<td class="px-6 py-4 font-semibold">
{{ $transaction->reference }}
</td>

<td class="px-6 py-4">
@if($transaction->isIncoming())
<span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs">Entrée</span>
@else
<span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs">Sortie</span>
@endif
</td>

<td class="px-6 py-4 font-bold">
{{ $transaction->formatted_amount }}
</td>

<td class="px-6 py-4">
{{ $transaction->source_label }}
</td>

<td class="px-6 py-4">
{{ $transaction->description ?? '-' }}
</td>

<td class="px-6 py-4">
{{ $transaction->created_at->format('d/m/Y H:i') }}
</td>

<td class="px-6 py-4 text-right">

@can('delete cash_transactions')
@if($transaction->cashRegister?->isOpen())

<form method="POST"
      action="{{ route('admin.cash-transactions.destroy',$transaction) }}">
@csrf
@method('DELETE')

<button onclick="return confirm('Supprimer ?')"
     class="text-red-600 hover:text-red-800"> <x-heroicon-o-trash class="w-5 h-5"/> </button>

</form>
@endif
@endcan

</td>

</tr>

@empty

<tr>
<td colspan="8" class="text-center py-12 text-gray-500">
Aucune transaction trouvée
</td>
</tr>
@endforelse

</tbody>
</table>

</div>
</div>

<div>
{{ $transactions->withQueryString()->links() }}
</div>

</div>

{{-- ================= SCRIPT ================= --}}
@push('scripts')

<script>
function cashPage(){
return{
showModal:false,
totalIn: {{ $totalIn ?? 0 }},
totalOut: {{ $totalOut ?? 0 }},
balance: {{ $cashRegister?->current_balance ?? 0 }},
animatedBalance:0,

statusLabel:"{{ $cashRegister?->isOpen() ? 'Ouverte' : 'Fermée' }}",
statusClass:"{{ $cashRegister?->isOpen() ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}",

init(){ this.animateBalance() },

animateBalance(){
let start=0
let end=parseFloat(this.balance)
let step=end/40

let timer=setInterval(()=>{
start+=step
if(start>=end){
start=end
clearInterval(timer)
}
this.animatedBalance=start
},20)
},

formatCurrency(v){
return new Intl.NumberFormat().format(
Number(v).toFixed(2)
)+' {{ company()?->currency }}'
}
}
}
</script>

@endpush

@endcan

</x-app-layout>
