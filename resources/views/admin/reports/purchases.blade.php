<x-app-layout>

<div class="max-w-7xl mx-auto py-6 space-y-8"
     x-data
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0">

{{-- =====================================================
HEADER
===================================================== --}}
<div class="flex items-center justify-between">

    <div class="flex items-center gap-4">

        <div class="header-icon">
            <x-heroicon-o-shopping-cart class="w-6 h-6"/>
        </div>

        <div>
            <h1 class="page-title">Rapport des Achats</h1>
            <p class="page-subtitle">
                Analyse des achats fournisseurs sur la période sélectionnée
            </p>
        </div>

    </div>

    <a href="{{ route('admin.reports.index') }}" class="btn-back">
        <x-heroicon-o-arrow-left class="icon-sm"/>
        Retour
    </a>

</div>


{{-- =====================================================
FILTRE
===================================================== --}}
<div class="card">

<form method="GET" class="flex flex-wrap items-end gap-4">

    <div>
        <label class="filter-label">Du</label>
        <input type="date" name="from" value="{{ $from }}" class="input-filter">
    </div>

    <div>
        <label class="filter-label">Au</label>
        <input type="date" name="to" value="{{ $to }}" class="input-filter">
    </div>

    <button class="btn-primary">
        <x-heroicon-o-funnel class="icon-sm"/>
        Filtrer
    </button>

</form>

</div>


{{-- =====================================================
KPI TOTAL
===================================================== --}}
<div class="kpi-total">

    <div>
        <p class="kpi-total-label">Total des Achats</p>

        <p class="kpi-total-value text-red-600">
            {{ number_format($total ?? 0,2) }}
            {{ company()?->currency }}
        </p>
    </div>

    <div class="kpi-total-icon">
        <x-heroicon-o-shopping-cart class="w-7 h-7"/>
    </div>

</div>


{{-- =====================================================
TABLE
===================================================== --}}
<div class="card overflow-hidden">

<div class="overflow-x-auto">

<table class="w-full text-sm">

<thead class="table-head">
<tr>
    <th>Référence</th>
    <th>Fournisseur</th>
    <th>Date</th>
    <th class="text-right">Montant</th>
    <th>Statut</th>
</tr>
</thead>

<tbody>

@forelse($purchases as $purchase)

<tr class="table-row">

<td class="font-semibold">
    {{ $purchase->reference }}
</td>

<td>
    {{ $purchase->supplier->name ?? '-' }}
</td>

<td>
    {{ $purchase->purchase_date->format('d/m/Y') }}
</td>

<td class="text-right font-semibold text-gray-800">
    {{ number_format($purchase->total_amount,2) }}
    {{ company()?->currency }}
</td>

<td>

@php
$colors = [
'draft'=>'badge-gray',
'confirmed'=>'badge-blue',
'partial'=>'badge-yellow',
'paid'=>'badge-green',
'cancelled'=>'badge-red',
];
@endphp

<span class="badge {{ $colors[$purchase->status] ?? 'badge-gray' }}">
    {{ ucfirst($purchase->status) }}
</span>

</td>

</tr>

@empty

<tr>
<td colspan="5" class="empty-state">

<x-heroicon-o-circle-stack class="w-8 h-8 text-gray-300"/>
<p>Aucun achat trouvé pour cette période</p>

</td>
</tr>

@endforelse

</tbody>
</table>

</div>
</div>


{{-- PAGINATION --}}
<div>
{{ $purchases->links() }}
</div>

</div>



{{-- =====================================================
STYLE MAURIERP PRO
===================================================== --}}
<style>

/* HEADER */
.header-icon{
padding:10px;
background:#fee2e2;
color:#dc2626;
border-radius:12px;
}

.page-title{
font-size:22px;
font-weight:600;
color:#1f2937;
}

.page-subtitle{
font-size:13px;
color:#6b7280;
}

/* BUTTON */
.btn-back{
display:flex;
align-items:center;
gap:6px;
padding:8px 14px;
border-radius:10px;
border:1px solid #e5e7eb;
background:white;
transition:.2s;
}
.btn-back:hover{ background:#f9fafb }

/* CARD */
.card{
background:white;
padding:22px;
border-radius:16px;
border:1px solid #e5e7eb;
box-shadow:0 1px 2px rgba(0,0,0,.05);
}

/* FILTER */
.filter-label{
font-size:12px;
color:#6b7280;
margin-bottom:4px;
display:block;
}

.input-filter{
border:1px solid #d1d5db;
border-radius:8px;
padding:8px 12px;
}

.btn-primary{
display:flex;
align-items:center;
gap:6px;
background:#4f46e5;
color:white;
padding:8px 16px;
border-radius:10px;
}

/* KPI */
.kpi-total{
display:flex;
justify-content:space-between;
align-items:center;
background:white;
border:1px solid #e5e7eb;
border-radius:16px;
padding:20px;
}

.kpi-total-label{
font-size:13px;
color:#6b7280;
}

.kpi-total-value{
font-size:26px;
font-weight:700;
}

.kpi-total-icon{
background:#fee2e2;
color:#dc2626;
padding:12px;
border-radius:12px;
}

/* TABLE */
.table-head th{
text-align:left;
padding:14px 20px;
font-size:12px;
text-transform:uppercase;
color:#6b7280;
background:#f9fafb;
}

.table-row td{
padding:16px 20px;
border-top:1px solid #f1f5f9;
}

.table-row:hover{
background:#fafafa;
}

/* BADGES */
.badge{
padding:4px 10px;
border-radius:999px;
font-size:12px;
font-weight:600;
}

.badge-green{background:#dcfce7;color:#15803d}
.badge-red{background:#fee2e2;color:#b91c1c}
.badge-blue{background:#dbeafe;color:#1d4ed8}
.badge-yellow{background:#fef9c3;color:#a16207}
.badge-gray{background:#f3f4f6;color:#374151}

/* EMPTY */
.empty-state{
text-align:center;
padding:50px;
color:#9ca3af;
}

.icon-sm{width:16px;height:16px}

</style>

</x-app-layout>