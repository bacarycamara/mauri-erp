<x-app-layout>

<div class="max-w-6xl mx-auto space-y-6">

{{-- HEADER --}}
<div class="flex items-center justify-between">

    <div class="flex items-center gap-3">

        <div class="header-icon">
            <x-heroicon-o-cube class="icon"/>
        </div>

        <div>
            <h1 class="page-title">Rapport Stock</h1>
            <p class="page-subtitle">Analyse des niveaux de stock</p>
        </div>

    </div>

    <a href="{{ route('admin.reports.index') }}" class="btn-back">
        <x-heroicon-o-arrow-left class="icon-sm"/>
        Retour
    </a>

</div>


{{-- KPI --}}
@php
$totalProducts = $products->total();
$lowStockCount = $products->filter(fn($p)=>$p->stock_quantity <= $p->minimum_stock)->count();
$healthyStock  = $totalProducts - $lowStockCount;
@endphp

<div class="grid md:grid-cols-3 gap-4">

<div class="kpi-compact">
<div class="kpi-left">

<div class="kpi-icon bg-indigo-100 text-indigo-600">
<x-heroicon-o-squares-2x2 class="icon"/>
</div>

<div>
<p class="kpi-label">Produits</p>
<p class="kpi-value text-indigo-600">{{ $totalProducts }}</p>
</div>

</div>
</div>


<div class="kpi-compact">
<div class="kpi-left">

<div class="kpi-icon bg-green-100 text-green-600">
<x-heroicon-o-check-circle class="icon"/>
</div>

<div>
<p class="kpi-label">Stock OK</p>
<p class="kpi-value text-green-600">{{ $healthyStock }}</p>
</div>

</div>
</div>


<div class="kpi-compact">
<div class="kpi-left">

<div class="kpi-icon bg-red-100 text-red-600">
<x-heroicon-o-exclamation-triangle class="icon"/>
</div>

<div>
<p class="kpi-label">Stock faible</p>
<p class="kpi-value text-red-600">{{ $lowStockCount }}</p>
</div>

</div>
</div>

</div>


{{-- TABLE --}}
<div class="card-sm">

<h2 class="section-title">
<x-heroicon-o-table-cells class="icon"/>
Liste des Produits
</h2>

<div class="overflow-x-auto mt-3">

<table class="w-full text-sm">

<thead class="bg-gray-50 text-gray-600 text-xs uppercase">
<tr>
<th class="px-4 py-2 text-left">Produit</th>
<th class="px-4 py-2 text-right">Stock</th>
<th class="px-4 py-2 text-right">Minimum</th>
<th class="px-4 py-2 text-center">Statut</th>
</tr>
</thead>

<tbody class="divide-y">

@forelse($products as $product)

@php
$isLow = $product->stock_quantity <= $product->minimum_stock;
@endphp

<tr class="hover:bg-gray-50">

<td class="px-4 py-2 font-medium text-gray-700">
{{ $product->name }}
</td>

<td class="px-4 py-2 text-right font-bold {{ $isLow ? 'text-red-600' : 'text-gray-800' }}">
{{ $product->stock_quantity }}
</td>

<td class="px-4 py-2 text-right text-gray-500">
{{ $product->minimum_stock }}
</td>

<td class="px-4 py-2 text-center">

@if($isLow)
<span class="badge-danger">Stock faible</span>
@else
<span class="badge-success">Disponible</span>
@endif

</td>

</tr>

@empty

<tr>
<td colspan="4" class="py-10 text-center text-gray-400">
Aucun produit trouvé
</td>
</tr>

@endforelse

</tbody>

</table>

</div>


{{-- PAGINATION --}}
@if($products->hasPages())

<div class="pagination-wrapper">

{{ $products->links() }}

</div>

@endif


</div>

</div>


<style>

.icon{width:18px;height:18px}
.icon-sm{width:14px;height:14px}

/* HEADER */
.header-icon{
padding:8px;
background:#e0e7ff;
color:#4f46e5;
border-radius:10px;
}

.page-title{
font-size:18px;
font-weight:600;
}

.page-subtitle{
font-size:12px;
color:#6b7280;
}

/* BUTTON */
.btn-back{
display:flex;
align-items:center;
gap:5px;
padding:6px 12px;
border-radius:8px;
background:#f9fafb;
border:1px solid #e5e7eb;
font-size:13px;
}

/* KPI */
.kpi-compact{
background:white;
padding:12px 16px;
border-radius:12px;
border:1px solid #e5e7eb;
display:flex;
align-items:center;
}

.kpi-left{
display:flex;
align-items:center;
gap:10px;
}

.kpi-icon{
padding:6px;
border-radius:8px;
}

.kpi-label{
font-size:11px;
color:#6b7280;
}

.kpi-value{
font-size:16px;
font-weight:700;
}

/* CARD */
.card-sm{
background:white;
padding:16px;
border-radius:12px;
border:1px solid #e5e7eb;
}

/* TITLE */
.section-title{
display:flex;
align-items:center;
gap:6px;
font-weight:600;
font-size:14px;
}

/* BADGES */
.badge-success{
background:#dcfce7;
color:#166534;
padding:3px 8px;
border-radius:999px;
font-size:11px;
font-weight:600;
}

.badge-danger{
background:#fee2e2;
color:#991b1b;
padding:3px 8px;
border-radius:999px;
font-size:11px;
font-weight:600;
}

/* PAGINATION */
.pagination-wrapper{
margin-top:14px;
display:flex;
justify-content:center;
}

</style>

</x-app-layout>