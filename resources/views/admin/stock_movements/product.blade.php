<x-app-layout>

<div class="max-w-7xl mx-auto space-y-8">

{{-- =====================================================
HEADER
===================================================== --}}
<div class="flex items-center justify-between">

    <div class="flex items-center gap-4">

        <div class="header-icon">
            <x-heroicon-o-cube class="icon"/>
        </div>

        <div>
            <h1 class="page-title">
                {{ $product->name }}
            </h1>

            <p class="page-subtitle">
                Historique des mouvements de stock
            </p>
        </div>

    </div>

    <a href="{{ route('admin.stock-movements.index') }}"
       class="btn-reset flex items-center gap-2">

        <x-heroicon-o-arrow-left class="icon-sm"/>
        Retour
    </a>

</div>


{{-- =====================================================
KPI STOCK
===================================================== --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-5">

<x-kpi
    color="indigo"
    icon="cube"
    label="Stock actuel"
    :value="$product->stock_quantity"
/>

<x-kpi
    color="yellow"
    icon="exclamation-triangle"
    label="Stock minimum"
    :value="$product->minimum_stock"
/>

<x-kpi
    color="{{ $product->stock_status == 'rupture' ? 'red' : ($product->stock_status == 'faible' ? 'yellow' : 'green') }}"
    icon="signal"
    label="Statut"
    :value="ucfirst($product->stock_status)"
/>

</div>


{{-- =====================================================
TABLE HISTORIQUE
===================================================== --}}
<div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">

<div class="overflow-x-auto">

<table class="w-full text-sm">

<thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wider">
<tr>
    <th class="px-6 py-4 text-left">Type</th>
    <th class="px-4 py-4 text-center">Quantité</th>
    <th class="px-4 py-4 text-center">Avant</th>
    <th class="px-4 py-4 text-center">Après</th>
    <th class="px-4 py-4 text-center">Référence</th>
    <th class="px-6 py-4 text-right">Date</th>
</tr>
</thead>

<tbody class="divide-y divide-gray-100">

@forelse($movements as $m)

<tr class="hover:bg-indigo-50/40 transition">

{{-- TYPE --}}
<td class="px-6 py-4">

@if($m->type=='purchase')
<span class="badge-success flex items-center gap-1 w-fit">
<x-heroicon-o-arrow-down-circle class="w-4 h-4"/>
Entrée
</span>

@elseif($m->type=='sale')
<span class="badge-danger flex items-center gap-1 w-fit">
<x-heroicon-o-arrow-up-circle class="w-4 h-4"/>
Sortie
</span>

@else
<span class="badge-warning flex items-center gap-1 w-fit">
<x-heroicon-o-adjustments-horizontal class="w-4 h-4"/>
Ajustement
</span>
@endif

</td>


<td class="text-center font-semibold">
{{ number_format($m->quantity,2) }}
</td>

<td class="text-center text-gray-600">
{{ $m->stock_before }}
</td>

<td class="text-center font-bold text-indigo-600">
{{ $m->stock_after }}
</td>

<td class="text-center text-gray-500">
{{ $m->reference ?? '—' }}
</td>

<td class="px-6 text-right text-gray-500 whitespace-nowrap">
{{ $m->created_at->format('d/m/Y') }}
<div class="text-xs text-gray-400">
{{ $m->created_at->format('H:i') }}
</div>
</td>

</tr>

@empty

<tr>
<td colspan="6" class="py-16 text-center text-gray-400">

<div class="flex flex-col items-center gap-3">

<div class="p-4 bg-gray-100 rounded-full">
<x-heroicon-o-inbox class="w-6 h-6"/>
</div>

<p class="font-medium">Aucun mouvement trouvé</p>
<p class="text-xs">Ce produit n'a encore aucun mouvement</p>

</div>

</td>
</tr>

@endforelse

</tbody>

</table>

</div>

@if($movements->hasPages())
<div class="px-6 py-4 border-t bg-gray-50">
{{ $movements->links() }}
</div>
@endif

</div>

</div>


{{-- =====================================================
STYLE SAFE MAURIERP
===================================================== --}}
<style>

.icon{ width:20px;height:20px; }
.icon-sm{ width:16px;height:16px; }

.header-icon{
padding:12px;
background:#e0e7ff;
color:#4f46e5;
border-radius:12px;
}

.page-title{
font-size:20px;
font-weight:600;
color:#1f2937;
}

.page-subtitle{
font-size:12px;
color:#6b7280;
}

.btn-reset{
padding:8px 14px;
border:1px solid #e5e7eb;
border-radius:10px;
transition:.2s;
}
.btn-reset:hover{
background:#f3f4f6;
}

.badge-success{
background:#dcfce7;
color:#15803d;
padding:4px 10px;
border-radius:999px;
font-size:12px;
}

.badge-danger{
background:#fee2e2;
color:#b91c1c;
padding:4px 10px;
border-radius:999px;
font-size:12px;
}

.badge-warning{
background:#fef3c7;
color:#b45309;
padding:4px 10px;
border-radius:999px;
font-size:12px;
}

</style>

</x-app-layout>