<x-app-layout>

<div class="max-w-6xl mx-auto space-y-6 py-6">

{{-- HEADER --}}
<div class="flex justify-between items-center">

    <div>
        <h1 class="flex items-center gap-2 text-xl font-semibold text-gray-800">

            <div class="header-icon">
                <x-heroicon-o-chart-bar class="icon"/>
            </div>

            Centre des Rapports

        </h1>

        <p class="text-xs text-gray-500 mt-1 max-w-lg">
            Analyse stratégique des performances financières, commerciales
            et opérationnelles de votre entreprise.
        </p>
    </div>

    <div class="hidden md:flex items-center gap-1 badge-analytics">
        <x-heroicon-o-sparkles class="icon-sm"/>
        MauriERP Analytics
    </div>

</div>


{{-- GRID RAPPORTS --}}
<div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">

{{-- FINANCIER --}}
@include('admin.reports.partials.card',[
'route'=>route('admin.reports.financial'),
'color'=>'indigo',
'icon'=>'chart-bar',
'title'=>'Rapport Financier',
'desc'=>'Bénéfice net, encaissements et performance globale.'
])

{{-- VENTES --}}
@include('admin.reports.partials.card',[
'route'=>route('admin.reports.sales'),
'color'=>'green',
'icon'=>'receipt-percent',
'title'=>'Rapport Ventes',
'desc'=>'Analyse du chiffre d’affaires.'
])

{{-- ACHATS --}}
@include('admin.reports.partials.card',[
'route'=>route('admin.reports.purchases'),
'color'=>'red',
'icon'=>'shopping-cart',
'title'=>'Rapport Achats',
'desc'=>'Suivi fournisseurs.'
])

{{-- DEPENSES --}}
@include('admin.reports.partials.card',[
'route'=>route('admin.reports.expenses'),
'color'=>'orange',
'icon'=>'banknotes',
'title'=>'Rapport Dépenses',
'desc'=>'Impact des dépenses.'
])

{{-- STOCK --}}
@include('admin.reports.partials.card',[
'route'=>route('admin.reports.stock'),
'color'=>'blue',
'icon'=>'archive-box',
'title'=>'Rapport Stock',
'desc'=>'Produits en rupture.'
])

{{-- DASHBOARD GLOBAL --}}
<a href="{{ route('admin.reports.dashboard') }}"
   class="dashboard-card group">

<div class="absolute inset-0 opacity-0 group-hover:opacity-10 bg-white transition"></div>

<div class="flex justify-center mb-3">
<x-heroicon-o-presentation-chart-line class="icon-lg"/>
</div>

<h2 class="font-semibold text-base mb-1">
Dashboard Global ERP
</h2>

<p class="text-xs opacity-90">
Vue consolidée des performances globales.
</p>

</a>

</div>

</div>



<style>

/* ICONS */

.icon{width:18px;height:18px}
.icon-sm{width:14px;height:14px}
.icon-lg{width:22px;height:22px}


/* HEADER */

.header-icon{
padding:8px;
background:#e0e7ff;
color:#4f46e5;
border-radius:10px;
display:flex;
align-items:center;
justify-content:center;
}


/* BADGE */

.badge-analytics{
background:#eef2ff;
color:#4338ca;
padding:5px 10px;
border-radius:999px;
font-size:11px;
font-weight:600;
}


/* DASHBOARD CARD */

.dashboard-card{
position:relative;
overflow:hidden;
background:linear-gradient(135deg,#4f46e5,#312e81);
color:white;
padding:18px;
border-radius:14px;
box-shadow:0 6px 12px rgba(0,0,0,.08);
transition:.25s;
}

.dashboard-card:hover{
transform:translateY(-3px);
box-shadow:0 12px 20px rgba(0,0,0,.12);
}

</style>

</x-app-layout>