<x-app-layout>

<div class="max-w-6xl mx-auto space-y-6">

{{-- HEADER --}}
<div class="flex justify-between items-center">

    <div class="flex items-center gap-3">

        <div class="header-icon">
            <x-heroicon-o-chart-bar class="icon"/>
        </div>

        <div>
            <h1 class="page-title">Rapport Financier</h1>
            <p class="page-subtitle">Analyse globale des performances financières</p>
        </div>

    </div>

    <a href="{{ route('admin.reports.index') }}" class="btn-secondary">
        <x-heroicon-o-arrow-left class="icon-sm"/>
        Retour
    </a>

</div>


{{-- FILTRE --}}
<div class="card-sm">

<form method="GET" class="flex flex-wrap gap-4 items-end">

    <div>
        <label class="label">Date début</label>
        <input type="date" name="from" value="{{ $from }}" class="input-erp w-40">
    </div>

    <div>
        <label class="label">Date fin</label>
        <input type="date" name="to" value="{{ $to }}" class="input-erp w-40">
    </div>

    <button class="btn-primary">
        <x-heroicon-o-funnel class="icon-sm"/>
        Filtrer
    </button>

</form>

</div>


{{-- KPI --}}
<div class="grid md:grid-cols-4 gap-4">

<x-kpi
    color="green"
    icon="receipt-percent"
    label="Total Ventes"
    :value="number_format($salesTotal ?? 0,2).' '.company()?->currency" />

<x-kpi
    color="red"
    icon="shopping-cart"
    label="Total Achats"
    :value="number_format($purchaseTotal ?? 0,2).' '.company()?->currency" />

<x-kpi
    color="yellow"
    icon="banknotes"
    label="Dépenses"
    :value="number_format($expenseTotal ?? 0,2).' '.company()?->currency" />

@php $isPositive = ($profitNet ?? 0) >= 0; @endphp

<div class="kpi-profit {{ $isPositive ? 'profit-positive' : 'profit-negative' }}">

    <div class="flex justify-between items-center mb-1">
        <span>Bénéfice Net</span>
        <x-heroicon-o-chart-bar class="icon"/>
    </div>

    <div class="text-lg font-bold">
        {{ number_format($profitNet ?? 0,2) }} {{ company()?->currency }}
    </div>

</div>

</div>


{{-- CASH FLOW --}}
<div class="grid md:grid-cols-2 gap-4">

<div class="card-sm">

    <div class="flex items-center gap-2 mb-2">
        <x-heroicon-o-arrow-trending-up class="icon text-green-600"/>
        <h2 class="font-semibold text-sm">Encaissements</h2>
    </div>

    <p class="amount-green">
        {{ number_format($totalIn ?? 0,2) }} {{ company()?->currency }}
    </p>

</div>


<div class="card-sm">

    <div class="flex items-center gap-2 mb-2">
        <x-heroicon-o-arrow-trending-down class="icon text-red-600"/>
        <h2 class="font-semibold text-sm">Décaissements</h2>
    </div>

    <p class="amount-red">
        {{ number_format(($totalOut ?? 0)+($expenseTotal ?? 0),2) }}
        {{ company()?->currency }}
    </p>

</div>

</div>


{{-- TABLE --}}
<div class="card-sm">

<h2 class="section-title">
<x-heroicon-o-circle-stack class="icon"/>
Résumé Financier
</h2>

<table class="w-full text-sm mt-3">

<tr class="row">
<td>Ventes</td>
<td class="text-green">{{ number_format($salesTotal ?? 0,2) }}</td>
</tr>

<tr class="row">
<td>Achats</td>
<td class="text-red">- {{ number_format($purchaseTotal ?? 0,2) }}</td>
</tr>

<tr class="row">
<td>Dépenses</td>
<td class="text-orange">- {{ number_format($expenseTotal ?? 0,2) }}</td>
</tr>

<tr class="total-row">
<td>Résultat Net</td>
<td class="{{ $isPositive ? 'text-green' : 'text-red' }}">
{{ number_format($profitNet ?? 0,2) }}
</td>
</tr>

</table>

</div>


{{-- GRAPH --}}
<div class="card-sm">

<h2 class="section-title">
<x-heroicon-o-chart-bar class="icon"/>
Évolution Mensuelle ({{ now()->year }})
</h2>

<canvas id="salesChart" height="80"></canvas>

</div>

</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const monthlyData = @json($monthlySales);

const labels = ['Jan','Fév','Mar','Avr','Mai','Juin','Juil','Août','Sep','Oct','Nov','Déc'];

let data = [];

for (let i = 1; i <= 12; i++) {
    data.push(monthlyData[i] ?? 0);
}

new Chart(document.getElementById('salesChart'), {

type: 'bar',

data: {
labels: labels,
datasets: [{
label: 'Ventes',
data: data,
backgroundColor: '#4f46e5',
borderRadius: 6
}]
},

options: {

plugins:{ legend:{ display:false }},

responsive:true,

scales:{
y:{ beginAtZero:true }
}

}

});

</script>


<style>

.icon{width:18px;height:18px}
.icon-sm{width:14px;height:14px}

.header-icon{
padding:10px;
background:#e0e7ff;
color:#4f46e5;
border-radius:10px;
}

.page-title{font-size:18px;font-weight:600}
.page-subtitle{font-size:11px;color:#6b7280}

.card-sm{
background:white;
padding:16px;
border-radius:12px;
border:1px solid #e5e7eb;
}

.input-erp{
border:1px solid #d1d5db;
border-radius:6px;
padding:6px 10px;
font-size:13px;
}

.btn-primary{
display:flex;
align-items:center;
gap:5px;
background:#4f46e5;
color:white;
padding:6px 12px;
border-radius:6px;
font-size:13px;
}

.btn-secondary{
display:flex;
align-items:center;
gap:5px;
background:#f3f4f6;
padding:6px 12px;
border-radius:6px;
font-size:13px;
}

.section-title{
display:flex;
align-items:center;
gap:6px;
font-size:14px;
font-weight:600;
}

.amount-green{font-size:20px;font-weight:700;color:#16a34a}
.amount-red{font-size:20px;font-weight:700;color:#dc2626}

.row{border-bottom:1px solid #f1f5f9}
.row td{padding:8px 0}

.total-row td{
padding-top:10px;
font-size:16px;
font-weight:700;
}

.text-green{color:#16a34a;text-align:right}
.text-red{color:#dc2626;text-align:right}
.text-orange{color:#ea580c;text-align:right}

.kpi-profit{
padding:16px;
border-radius:12px;
color:white;
}

.profit-positive{
background:linear-gradient(90deg,#4f46e5,#3730a3)
}

.profit-negative{
background:linear-gradient(90deg,#dc2626,#991b1b)
}

</style>

</x-app-layout>