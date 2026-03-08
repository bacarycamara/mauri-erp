<x-app-layout>

@can('view sales')

<div class="max-w-7xl mx-auto space-y-8">

@php
$productsToOrder = 0;

foreach($sales as $sale){
foreach($sale->items as $item){
if(
$item->product &&
$item->product->type === 'physical' &&
$item->product->stock_quantity < $item->quantity
){
$productsToOrder++;
break;
}
}
}
@endphp

{{-- ================= HEADER ================= --}}

<div class="flex items-center justify-between">

```
<div class="flex items-center gap-4">

    <div class="header-icon">
        <x-heroicon-o-receipt-percent class="icon"/>
    </div>

    <div>
        <h1 class="page-title">Ventes</h1>
        <p class="page-subtitle">
            Gestion des ventes clients
        </p>
    </div>

</div>

<div class="flex items-center gap-4">

    @if($productsToOrder>0)
    <div class="stock-alert">
        <x-heroicon-o-exclamation-triangle class="icon-sm"/>
        {{ $productsToOrder }} produit(s) à approvisionner
    </div>
    @endif

    @can('create sales')
    <a href="{{ route('admin.sales.create') }}"
       class="btn-create group">

        <x-heroicon-o-plus class="icon rotate-hover"/>
        Nouvelle vente
    </a>
    @endcan

</div>
```

</div>

{{-- ================= FILTER BAR ================= --}}

<div class="filter-bar">

<form method="GET" class="flex flex-wrap items-center gap-3">

<input name="search"
    value="{{ request('search') }}"
    placeholder="Référence ou client..."
    class="input-erp w-56">

<select name="status" class="input-erp w-44">
<option value="">Statut</option>
@foreach(['draft','confirmed','partial','paid','cancelled'] as $s)
<option value="{{ $s }}" @selected(request('status')==$s)>
{{ ucfirst($s) }}
</option>
@endforeach
</select>

<input type="date"
    name="date"
    value="{{ request('date') }}"
    class="input-erp">

<button class="btn-filter">
<x-heroicon-o-funnel class="icon-sm"/>
Filtrer
</button>

<a href="{{ route('admin.sales.index') }}" class="btn-reset">
Reset
</a>

</form>

</div>

{{-- ================= SALES CARDS ================= --}}

<div class="space-y-3">

@forelse($sales as $sale)

@php
$stockIssue=false;
$productToOrder=null;

foreach($sale->items as $item){
if(
$item->product &&
$item->product->type==='physical' &&
$item->product->stock_quantity < $item->quantity
){
$stockIssue=true;
$productToOrder=$item->product;
break;
}
}
@endphp

<div class="sale-card {{ $stockIssue ? 'danger' : '' }}">

```
{{-- LEFT --}}
<div class="sale-left">

    <div class="sale-avatar">
        <x-heroicon-o-shopping-bag class="icon"/>
    </div>

    <div>
        <p class="sale-ref">{{ $sale->reference }}</p>
        <p class="sale-client">
            {{ $sale->customer->name ?? '-' }}
        </p>
    </div>

</div>


{{-- DATE --}}
<div class="sale-col">
    {{ $sale->sale_date->format('d/m/Y') }}
</div>


{{-- TOTAL --}}
<div class="sale-col price">
    {{ number_format($sale->total_amount,2) }}
    {{ company()?->currency }}
</div>


{{-- PAYMENT --}}
<div class="sale-col">

    @if($sale->due_amount>0)
        <span class="badge-warning">
            {{ number_format($sale->due_amount,2) }}
        </span>
    @else
        <span class="badge-success">
            Soldé
        </span>
    @endif

</div>


{{-- STATUS --}}
<div class="sale-col">
    <span class="badge {{ $sale->status_badge }}">
        {{ ucfirst($sale->status) }}
    </span>
</div>


{{-- STOCK --}}
<div class="sale-col">

    @if($stockIssue)
        <span class="badge-danger pulse">
            <x-heroicon-o-exclamation-circle class="icon-xs"/>
            Rupture
        </span>
    @else
        <span class="badge-success">
            OK
        </span>
    @endif

</div>


{{-- ACTIONS --}}
<div class="sale-actions">

    @can('view sales')
    <a href="{{ route('admin.sales.show',$sale) }}"
       class="action-icon text-indigo-600">
        <x-heroicon-o-eye class="icon-sm"/>
    </a>
    @endcan

    @can('edit sales')
    @if($sale->status=='draft')
    <a href="{{ route('admin.sales.edit',$sale) }}"
       class="action-icon text-blue-600">
        <x-heroicon-o-pencil-square class="icon-sm"/>
    </a>
    @endif
    @endcan

    @can('create purchases')
    @if($stockIssue && $sale->status=='draft')
    <a href="{{ route('admin.purchases.create') }}?product_id={{ $productToOrder->id }}&qty=1"
       class="action-icon text-orange-600">
        <x-heroicon-o-shopping-cart class="icon-sm"/>
    </a>
    @endif
    @endcan

</div>
```

</div>

@empty

<div class="empty-card">
<x-heroicon-o-face-frown class="w-8 h-8 mx-auto mb-2"/>
Aucune vente trouvée
</div>

@endforelse

</div>

<div class="pt-4">
{{ $sales->withQueryString()->links() }}
</div>

</div>

@endcan

{{-- ================= ERP PRO STYLE ================= --}}

<style>

/* ICONS */
.icon{width:20px;height:20px;}
.icon-sm{width:16px;height:16px;}
.icon-xs{width:14px;height:14px;}

/* HEADER */
.header-icon{
padding:12px;
background:#e0e7ff;
color:#4f46e5;
border-radius:12px;
}

.page-title{font-size:20px;font-weight:600;}
.page-subtitle{font-size:12px;color:#6b7280;}

/* FILTER */
.filter-bar{
background:white;
border:1px solid #e5e7eb;
border-radius:12px;
padding:16px;
}

.input-erp{
border:1px solid #d1d5db;
border-radius:8px;
padding:8px 12px;
}

.btn-create{
display:flex;
align-items:center;
gap:8px;
background:#4f46e5;
color:white;
padding:10px 18px;
border-radius:12px;
transition:.2s;
}

.btn-create:hover{background:#4338ca;}

.btn-filter{
display:flex;
gap:6px;
background:#4f46e5;
color:white;
padding:8px 14px;
border-radius:8px;
}

.btn-reset{
padding:8px 14px;
border:1px solid #e5e7eb;
border-radius:8px;
}

/* CARD */
.sale-card{
display:flex;
align-items:center;
justify-content:space-between;
background:white;
border:1px solid #e5e7eb;
border-radius:18px;
padding:16px 24px;
transition:.25s;
}

.sale-card:hover{
transform:translateY(-2px);
box-shadow:0 10px 20px rgba(0,0,0,.06);
background:#eef2ff40;
}

.sale-card.danger{
background:#fff1f2;
}

/* LEFT */
.sale-left{
display:flex;
align-items:center;
gap:16px;
width:280px;
}

.sale-avatar{
width:40px;height:40px;
display:flex;
align-items:center;
justify-content:center;
background:#e0e7ff;
color:#4f46e5;
border-radius:12px;
}

.sale-ref{font-weight:600;}
.sale-client{font-size:12px;color:#6b7280;}

.sale-col{
width:160px;
font-size:14px;
}

.price{color:#4f46e5;font-weight:600;}

.sale-actions{display:flex;gap:8px;}

.action-icon{
padding:8px;
border-radius:8px;
transition:.2s;
}

.action-icon:hover{
background:#f3f4f6;
transform:scale(1.1);
}

/* BADGES */
.badge-success{
background:#dcfce7;
color:#15803d;
padding:3px 8px;
border-radius:999px;
font-size:12px;
}

.badge-warning{
background:#fef3c7;
color:#b45309;
padding:3px 8px;
border-radius:999px;
font-size:12px;
}

.badge-danger{
background:#fee2e2;
color:#b91c1c;
padding:3px 8px;
border-radius:999px;
font-size:12px;
display:flex;
align-items:center;
gap:4px;
}

/* ALERT */
.stock-alert{
background:#fff7ed;
color:#c2410c;
padding:8px 14px;
border-radius:12px;
border:1px solid #fed7aa;
display:flex;
gap:6px;
font-weight:600;
}

/* PULSE */
.pulse{
animation:pulseDanger 1.5s infinite;
}

@keyframes pulseDanger{
0%{box-shadow:0 0 0 0 rgba(220,38,38,.4);}
70%{box-shadow:0 0 0 8px rgba(220,38,38,0);}
100%{box-shadow:0 0 0 0 rgba(220,38,38,0);}
}

/* EMPTY */
.empty-card{
text-align:center;
padding:60px;
background:white;
border:1px solid #e5e7eb;
border-radius:18px;
color:#9ca3af;
}

</style>

</x-app-layout>
