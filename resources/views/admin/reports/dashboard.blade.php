<x-app-layout>

<div class="max-w-6xl mx-auto space-y-6">

{{-- HEADER --}}
<div class="flex items-center justify-between">

    <div class="flex items-center gap-3">

        <div class="header-icon">
            <x-heroicon-o-presentation-chart-line class="icon"/>
        </div>

        <div>
            <h1 class="page-title">Dashboard Global ERP</h1>
            <p class="page-subtitle">Vue stratégique des performances</p>
        </div>

    </div>

    <a href="{{ route('admin.reports.index') }}" class="btn-back">
        <x-heroicon-o-arrow-left class="icon-sm"/>
        Retour
    </a>

</div>


{{-- KPI --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">

<div class="kpi-card">

<div class="kpi-top">
<span>Ventes</span>
<x-heroicon-o-receipt-percent class="kpi-icon text-green-600"/>
</div>

<div class="kpi-value text-green-600">
{{ number_format($totalSales ?? 0,2) }}
<small>{{ company()?->currency }}</small>
</div>

</div>


<div class="kpi-card">

<div class="kpi-top">
<span>Aujourd’hui</span>
<x-heroicon-o-bolt class="kpi-icon text-indigo-600"/>
</div>

<div class="kpi-value text-indigo-600">
{{ number_format($todaySales ?? 0,2) }}
<small>{{ company()?->currency }}</small>
</div>

</div>


<div class="kpi-card">

<div class="kpi-top">
<span>Achats</span>
<x-heroicon-o-shopping-cart class="kpi-icon text-red-600"/>
</div>

<div class="kpi-value text-red-600">
{{ number_format($totalPurchases ?? 0,2) }}
<small>{{ company()?->currency }}</small>
</div>

</div>


<div class="kpi-card">

<div class="kpi-top">
<span>Dépenses</span>
<x-heroicon-o-banknotes class="kpi-icon text-orange-600"/>
</div>

<div class="kpi-value text-orange-600">
{{ number_format($totalExpenses ?? 0,2) }}
<small>{{ company()?->currency }}</small>
</div>

</div>


<div class="kpi-card">

<div class="kpi-top">
<span>Bénéfice Net</span>
<x-heroicon-o-chart-bar class="kpi-icon text-purple-600"/>
</div>

<div class="kpi-value text-purple-600">
{{ number_format($profitNet ?? 0,2) }}
<small>{{ company()?->currency }}</small>
</div>

</div>

</div>


{{-- INFOS ERP --}}
<div class="grid md:grid-cols-2 gap-4">

<div class="card-sm">

<h2 class="section-title">
<x-heroicon-o-arrows-right-left class="icon"/>
Flux Financier
</h2>

<div class="mt-3 space-y-2 text-sm">

<div class="row-info">
<span>Encaissements</span>
<strong class="text-green-600">
{{ number_format($totalIn ?? 0,2) }} {{ company()?->currency }}
</strong>
</div>

<div class="row-info">
<span>Décaissements</span>
<strong class="text-red-600">
{{ number_format($totalOut ?? 0,2) }} {{ company()?->currency }}
</strong>
</div>

</div>

</div>


<div class="card-sm">

<h2 class="section-title">
<x-heroicon-o-exclamation-triangle class="icon text-yellow-500"/>
Alertes Stock
</h2>

<div class="mt-3 text-lg font-semibold">
{{ $lowStockProducts ?? 0 }}

<span class="text-xs text-gray-500">
produits sous seuil minimum
</span>

</div>

</div>

</div>


{{-- GRAPH --}}
<div class="card-sm">

<h2 class="section-title mb-3">
<x-heroicon-o-chart-bar class="icon text-indigo-600"/>
Évolution du Bénéfice ({{ now()->year }})
</h2>

<canvas id="profitChart" height="70"></canvas>

</div>

</div>



<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const monthlyData = @json($monthlyProfit ?? []);

let values = [];

for (let i = 1; i <= 12; i++) {
values.push(monthlyData[i] ?? 0);
}

new Chart(document.getElementById('profitChart'), {

type:'line',

data:{
labels:[
'Jan','Fév','Mar','Avr','Mai','Juin',
'Juil','Août','Sep','Oct','Nov','Déc'
],

datasets:[{
data:values,
borderColor:'#4f46e5',
backgroundColor:'rgba(79,70,229,0.08)',
borderWidth:2,
fill:true,
tension:0.4,
pointRadius:3
}]
},

options:{
responsive:true,

plugins:{
legend:{display:false}
},

scales:{
y:{beginAtZero:true}
}

}

});

</script>



<style>

.icon{width:18px;height:18px}
.icon-sm{width:14px;height:14px}

/* HEADER */

.header-icon{
padding:8px;
background:#eef2ff;
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
border:1px solid #e5e7eb;
border-radius:8px;
background:#f9fafb;
font-size:13px;
}


/* KPI */

.kpi-card{
background:white;
padding:12px;
border-radius:10px;
border:1px solid #e5e7eb;
height:75px;
display:flex;
flex-direction:column;
justify-content:space-between;
}

.kpi-top{
display:flex;
justify-content:space-between;
align-items:center;
font-size:11px;
color:#6b7280;
}

.kpi-icon{
width:16px;
height:16px;
}

.kpi-value{
font-size:16px;
font-weight:700;
}

.kpi-value small{
font-size:10px;
color:#6b7280;
margin-left:3px;
}


/* CARDS */

.card-sm{
background:white;
padding:16px;
border-radius:12px;
border:1px solid #e5e7eb;
}


/* TITLES */

.section-title{
display:flex;
align-items:center;
gap:6px;
font-weight:600;
font-size:14px;
}


/* ROW */

.row-info{
display:flex;
justify-content:space-between;
}

</style>

</x-app-layout>