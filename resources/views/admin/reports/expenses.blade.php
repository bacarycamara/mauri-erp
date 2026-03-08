<x-app-layout>

<div class="max-w-7xl mx-auto py-6 space-y-10">

{{-- ================= HEADER ================= --}}
<div class="flex justify-between items-center">

<div class="flex items-center gap-3">

<svg xmlns="http://www.w3.org/2000/svg"
class="w-8 h-8 text-red-600"
fill="none"
viewBox="0 0 24 24"
stroke="currentColor">

<path stroke-linecap="round"
stroke-linejoin="round"
stroke-width="2"
d="M3 10h18M7 15h1m4 0h5M3 6h18a2 2 0 012 2v8a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2z"/>

</svg>

<div>
<h1 class="text-3xl font-bold text-gray-800">
Rapport des Dépenses
</h1>

<p class="text-sm text-gray-500">
Période : {{ $from }} → {{ $to }}
</p>
</div>

</div>

<a href="{{ route('admin.reports.index') }}"
class="flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200">

← Retour

</a>

</div>


{{-- ================= FILTRE ================= --}}
<div class="bg-white p-6 rounded-2xl shadow border">

<form method="GET" class="flex flex-wrap gap-6 items-end">

<div>
<label class="block text-sm text-gray-600 mb-1">
Du
</label>

<input type="date"
name="from"
value="{{ $from }}"
class="border rounded-lg px-3 py-2">
</div>


<div>
<label class="block text-sm text-gray-600 mb-1">
Au
</label>

<input type="date"
name="to"
value="{{ $to }}"
class="border rounded-lg px-3 py-2">
</div>


<button
class="px-5 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700">

Filtrer

</button>

</form>

</div>



{{-- ================= TOTAL ================= --}}
<div class="bg-gradient-to-r from-red-600 to-red-800 text-white p-6 rounded-2xl shadow flex justify-between">

<div>

<h2 class="text-lg font-semibold">
Total des dépenses
</h2>

<div class="text-3xl font-bold mt-2">

{{ number_format($total ?? 0,2) }}
{{ company()?->currency }}

</div>

</div>

</div>



{{-- ================= TABLE ================= --}}
<div class="bg-white rounded-2xl shadow overflow-hidden border">

<div class="overflow-x-auto">

<table class="w-full text-sm">

<thead class="bg-gray-50 text-gray-600 uppercase text-xs">

<tr>

<th class="px-6 py-3 text-left">
Référence
</th>

<th class="px-6 py-3 text-left">
Catégorie
</th>

<th class="px-6 py-3 text-left">
Date
</th>

<th class="px-6 py-3 text-right">
Montant
</th>

<th class="px-6 py-3 text-left">
Statut
</th>

</tr>

</thead>


<tbody class="divide-y divide-gray-100">

@forelse($expenses as $expense)

<tr>

<td class="px-6 py-4 font-semibold">
{{ $expense->reference ?? '-' }}
</td>

<td class="px-6 py-4">
{{ $expense->category ?? '-' }}
</td>

<td class="px-6 py-4">
{{ \Carbon\Carbon::parse($expense->expense_date)->format('d/m/Y') }}
</td>

<td class="px-6 py-4 text-right font-semibold text-red-600">

{{ number_format($expense->amount,2) }}
{{ company()?->currency }}

</td>


<td class="px-6 py-4">

@php
$colors = [
'draft' => 'bg-gray-100 text-gray-700',
'pending' => 'bg-yellow-100 text-yellow-700',
'approved' => 'bg-green-100 text-green-700',
'rejected' => 'bg-red-100 text-red-700',
];
@endphp

<span class="px-3 py-1 rounded-full text-xs font-semibold {{ $colors[$expense->status] ?? 'bg-gray-100 text-gray-700' }}">

{{ ucfirst($expense->status) }}

</span>

</td>

</tr>

@empty

<tr>

<td colspan="5" class="px-6 py-10 text-center text-gray-500">

Aucune dépense trouvée pour cette période.

</td>

</tr>

@endforelse

</tbody>

</table>

</div>

</div>



{{-- ================= PAGINATION ================= --}}
<div>

{{ $expenses->links() }}

</div>


</div>

</x-app-layout>