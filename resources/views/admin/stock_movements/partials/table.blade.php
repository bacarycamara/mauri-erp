<div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">

{{-- ================= TABLE ================= --}}
<div class="overflow-x-auto">

<table class="w-full text-sm">

<thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wider">
<tr>
    <th class="px-6 py-4 text-left">Produit</th>
    <th class="px-4 py-4">Type</th>
    <th class="px-4 py-4">Quantité</th>
    <th class="px-4 py-4">Avant</th>
    <th class="px-4 py-4">Après</th>
    <th class="px-4 py-4">Référence</th>
    <th class="px-6 py-4 text-right">Date</th>
</tr>
</thead>

<tbody class="divide-y divide-gray-100">

@forelse($movements as $m)

<tr class="hover:bg-indigo-50/40 transition duration-200">

{{-- PRODUIT --}}
<td class="px-6 py-4">
    <div class="flex items-center gap-3">

        <div class="p-2 bg-indigo-50 text-indigo-600 rounded-xl">
            <x-heroicon-o-cube class="w-4 h-4"/>
        </div>

        <span class="font-medium text-gray-800">
            {{ $m->product->name }}
        </span>

    </div>
</td>


{{-- TYPE --}}
<td class="text-center">

    @if($m->type=='purchase')
        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
            <x-heroicon-o-arrow-down-circle class="w-4 h-4"/>
            Entrée
        </span>

    @elseif($m->type=='sale')
        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
            <x-heroicon-o-arrow-up-circle class="w-4 h-4"/>
            Sortie
        </span>

    @else
        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
            <x-heroicon-o-adjustments-horizontal class="w-4 h-4"/>
            Ajustement
        </span>
    @endif

</td>


{{-- QUANTITE --}}
<td class="text-center font-semibold text-gray-800">
    {{ number_format($m->quantity,2) }}
</td>


{{-- STOCK AVANT --}}
<td class="text-center text-gray-600">
    {{ $m->stock_before }}
</td>


{{-- STOCK APRES --}}
<td class="text-center font-bold text-indigo-600">
    {{ $m->stock_after }}
</td>


{{-- REFERENCE --}}
<td class="text-center text-gray-500">
    {{ $m->reference ?? '—' }}
</td>


{{-- DATE --}}
<td class="px-6 text-right text-gray-500 whitespace-nowrap">
    {{ $m->created_at->format('d/m/Y') }}
    <div class="text-xs text-gray-400">
        {{ $m->created_at->format('H:i') }}
    </div>
</td>

</tr>

@empty

<tr>
<td colspan="7" class="py-16 text-center text-gray-400">

    <div class="flex flex-col items-center gap-3">

        <div class="p-4 bg-gray-100 rounded-full">
            <x-heroicon-o-inbox class="w-6 h-6"/>
        </div>

        <p class="font-medium">Aucun mouvement enregistré</p>
        <p class="text-xs">Les mouvements de stock apparaîtront ici</p>

    </div>

</td>
</tr>

@endforelse

</tbody>

</table>

</div>


{{-- ================= PAGINATION ================= --}}
@if($movements->hasPages())
<div class="px-6 py-4 border-t bg-gray-50">
    {{ $movements->links() }}
</div>
@endif

</div>