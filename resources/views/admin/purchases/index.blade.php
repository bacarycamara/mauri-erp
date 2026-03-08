<x-app-layout>

@can('view purchases')

<div class="max-w-7xl mx-auto space-y-8">

{{-- =====================================================
HEADER
===================================================== --}}

<div class="flex items-center justify-between">

```
<div class="flex items-center gap-4">

    <div class="header-icon">
        <x-heroicon-o-shopping-cart class="icon"/>
    </div>

    <div>
        <h1 class="page-title">Achats</h1>
        <p class="page-subtitle">
            Gestion des achats fournisseurs
        </p>
    </div>

</div>

@can('create purchases')
<a href="{{ route('admin.purchases.create') }}"
   class="btn-create group">

    <x-heroicon-o-plus class="icon rotate-hover"/>
    Nouvel achat
</a>
@endcan
```

</div>

{{-- =====================================================
KPI
===================================================== --}}

<div class="grid grid-cols-2 md:grid-cols-4 gap-5">

<x-kpi color="indigo" icon="shopping-cart" label="Total" :value="$purchases->total()" />
<x-kpi color="blue" icon="check-circle" label="Confirmés" :value="\App\Models\Purchase::confirmed()->count()" />
<x-kpi color="green" icon="banknotes" label="Payés" :value="\App\Models\Purchase::paid()->count()" />
<x-kpi color="yellow" icon="clock" label="Attente" :value="\App\Models\Purchase::pending()->count()" />

</div>

{{-- =====================================================
FILTER BAR
===================================================== --}}

<div class="filter-bar">

<form method="GET" class="flex flex-wrap items-center gap-3">

<input name="search"
    value="{{ request('search') }}"
    placeholder="Référence..."
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

<a href="{{ route('admin.purchases.index') }}" class="btn-reset">
Reset
</a>

</form>

</div>

{{-- =====================================================
CARD TABLE PRO
===================================================== --}}

<div class="space-y-3">

@forelse($purchases as $purchase)

<div class="purchase-card">

```
<div class="purchase-left">

    <div class="purchase-avatar">
        <x-heroicon-o-shopping-cart class="icon"/>
    </div>

    <div>
        <p class="purchase-ref">{{ $purchase->reference }}</p>
        <p class="purchase-supplier">
            {{ $purchase->supplier->name }}
        </p>
    </div>

</div>

<div class="purchase-col">
    {{ $purchase->purchase_date->format('d/m/Y') }}
</div>

<div class="purchase-col price">
    {{ $purchase->formatted_total }}
</div>

<div class="purchase-col">
    @if($purchase->due_amount>0)
        <span class="badge-warning">
            {{ $purchase->formatted_due }}
        </span>
    @else
        <span class="badge-success">Soldé</span>
    @endif
</div>

<div class="purchase-col">
    <span class="badge {{ $purchase->status_badge }}">
        {{ ucfirst($purchase->status) }}
    </span>
</div>

<div class="purchase-actions">

    {{-- VOIR --}}
    @can('view purchases')
    <a href="{{ route('admin.purchases.show',$purchase) }}"
       class="action-icon text-indigo-600">
        <x-heroicon-o-eye class="icon-sm"/>
    </a>
    @endcan

    {{-- CONFIRMER --}}
    @can('confirm purchases')
    @if($purchase->status=='draft')
    <form method="POST"
          action="{{ route('admin.purchases.confirm',$purchase) }}">
        @csrf
        <button class="action-icon text-blue-600">
            <x-heroicon-o-check class="icon-sm"/>
        </button>
    </form>
    @endif
    @endcan

    {{-- PAIEMENT --}}
    @can('create payments')
    @if($purchase->due_amount>0 && $purchase->status!='draft')
    <a href="{{ route('admin.payments.create',['purchase_id'=>$purchase->id]) }}"
       class="action-icon text-green-600">
        <x-heroicon-o-banknotes class="icon-sm"/>
    </a>
    @endif
    @endcan

</div>
```

</div>

@empty

<div class="empty-card">
<x-heroicon-o-face-frown class="w-8 h-8 mx-auto mb-2"/>
Aucun achat trouvé
</div>

@endforelse

</div>

<div class="pt-4">
{{ $purchases->withQueryString()->links() }}
</div>

</div>

@endcan

{{-- =====================================================
MAURIERP PRO CSS (SAFE VERSION)
===================================================== --}}

<style>

/* ICONS */
.icon{ width:20px;height:20px; }
.icon-sm{ width:16px;height:16px; }

.rotate-hover{ transition:.3s; }
.btn-create:hover .rotate-hover{ transform:rotate(90deg); }

/* HEADER */
.header-icon{
padding:12px;
background:#e0e7ff;
color:#4f46e5;
border-radius:12px;
transition:.3s;
}
.header-icon:hover{
transform:scale(1.08) rotate(6deg);
}

.page-title{ font-size:20px;font-weight:600;color:#1f2937; }
.page-subtitle{ font-size:12px;color:#6b7280; }

/* FILTER */
.filter-bar{
background:white;
border:1px solid #e5e7eb;
border-radius:12px;
padding:16px 20px;
box-shadow:0 1px 2px rgba(0,0,0,.05);
}

.input-erp{
border:1px solid #d1d5db;
border-radius:8px;
padding:8px 12px;
font-size:14px;
}
.input-erp:focus{
outline:none;
border-color:#6366f1;
box-shadow:0 0 0 2px rgba(99,102,241,.25);
}

/* BUTTONS */
.btn-create{
display:flex;
align-items:center;
gap:8px;
background:#4f46e5;
color:white;
padding:10px 18px;
border-radius:12px;
font-size:14px;
transition:.2s;
}
.btn-create:hover{
background:#4338ca;
transform:translateY(-1px);
}

.btn-filter{
display:flex;
align-items:center;
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

/* CARD TABLE */
.purchase-card{
display:flex;
align-items:center;
justify-content:space-between;
background:white;
border:1px solid #e5e7eb;
border-radius:18px;
padding:16px 24px;
box-shadow:0 1px 2px rgba(0,0,0,.05);
transition:.25s;
}

.purchase-card:hover{
transform:translateY(-2px);
box-shadow:0 10px 20px rgba(0,0,0,.06);
background:#eef2ff40;
}

.purchase-left{
display:flex;
align-items:center;
gap:16px;
width:280px;
}

.purchase-avatar{
width:40px;height:40px;
display:flex;
align-items:center;
justify-content:center;
background:#e0e7ff;
color:#4f46e5;
border-radius:12px;
}

.purchase-ref{ font-weight:600;color:#1f2937; }
.purchase-supplier{ font-size:12px;color:#6b7280; }

.purchase-col{
width:160px;
font-size:14px;
color:#4b5563;
}

.price{ color:#4f46e5;font-weight:600; }

.purchase-actions{ display:flex;gap:8px; }

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
