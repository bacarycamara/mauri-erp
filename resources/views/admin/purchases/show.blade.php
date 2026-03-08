<x-app-layout>

@can('view purchases')

<div class="max-w-7xl mx-auto space-y-8 fade-in"
     x-data="{ openPayment:false }"
     x-cloak>

{{-- ================= HEADER HERO ================= --}}

<div class="erp-hero">

```
<div class="flex items-center gap-4">

    <div class="hero-icon">
        <x-heroicon-o-receipt-percent class="icon-lg"/>
    </div>

    <div>
        <h1 class="hero-title">
            Achat {{ $purchase->reference }}
        </h1>

        <p class="hero-sub">
            Gestion et suivi financier de l'achat fournisseur
        </p>
    </div>

</div>

<div class="flex gap-3 flex-wrap">

    <a href="{{ route('admin.purchases.index') }}" class="btn-light">
        <x-heroicon-o-arrow-left class="icon-sm"/> Retour
    </a>

    @can('print purchases')
    <a href="{{ route('admin.purchases.pdf',$purchase) }}" class="btn-dark">
        <x-heroicon-o-document-arrow-down class="icon-sm"/> PDF
    </a>
    @endcan

    {{-- CONFIRM --}}
    @can('confirm purchases')
    @if($purchase->status==='draft')
    <form method="POST" action="{{ route('admin.purchases.confirm',$purchase) }}">
        @csrf
        <button class="btn-primary">
            <x-heroicon-o-check-circle class="icon-sm"/> Confirmer
        </button>
    </form>
    @endif
    @endcan

    {{-- PAYMENT --}}
    @can('create payments')
    @if($purchase->due_amount>0 && $purchase->status!=='draft')
    <button @click="openPayment=true" class="btn-success">
        <x-heroicon-o-banknotes class="icon-sm"/> Paiement
    </button>
    @endif
    @endcan

</div>
```

</div>

{{-- ================= KPI ================= --}}

<div class="grid md:grid-cols-3 gap-6">

<div class="stat-card">
    <p>Total</p>
    <h2>{{ $purchase->formatted_total }}</h2>
</div>

<div class="stat-card success">
    <p>Payé</p>
    <h2>{{ number_format($purchase->paid_amount,2) }} {{ company()?->currency }}</h2>
</div>

<div class="stat-card danger">
    <p>Reste à payer</p>
    <h2>{{ $purchase->formatted_due }}</h2>
</div>

</div>

{{-- ================= INFOS ================= --}}

<div class="grid lg:grid-cols-2 gap-8">

<div class="erp-card">
<h3 class="card-title">
<x-heroicon-o-information-circle class="icon"/>
Informations Achat
</h3>

<ul class="info-grid">
<li><span>Date</span>{{ $purchase->purchase_date?->format('d/m/Y') }}</li>
<li><span>Référence</span>{{ $purchase->reference }}</li>
<li><span>Statut</span>
<span class="badge {{ $purchase->status_badge }}">
{{ strtoupper($purchase->status) }}
</span>
</li>
<li><span>Notes</span>{{ $purchase->notes ?? '-' }}</li>
</ul>
</div>

<div class="erp-card">
<h3 class="card-title">
<x-heroicon-o-building-storefront class="icon"/>
Fournisseur
</h3>

<ul class="info-grid">
<li><span>Nom</span>{{ $purchase->supplier?->name }}</li>
<li><span>Email</span>{{ $purchase->supplier?->email ?? '-' }}</li>
<li><span>Téléphone</span>{{ $purchase->supplier?->phone ?? '-' }}</li>
<li><span>Ville</span>{{ $purchase->supplier?->city ?? '-' }}</li>
</ul>
</div>

</div>

{{-- ================= PRODUITS ================= --}}

<div class="erp-card p-0 overflow-hidden">

<table class="erp-table">

<thead>
<tr>
<th>Produit</th>
<th>Qté</th>
<th>Prix</th>
<th>TVA</th>
<th>Total</th>
</tr>
</thead>

<tbody>
@foreach($purchase->items as $item)
<tr>
<td class="font-semibold">{{ $item->product?->name }}</td>
<td>{{ $item->quantity }}</td>
<td>{{ number_format($item->unit_price,2) }}</td>
<td>{{ $item->vat_rate }}%</td>
<td class="price">
{{ number_format($item->total,2) }} {{ company()?->currency }}
</td>
</tr>
@endforeach
</tbody>

</table>

</div>

{{-- ================= MODAL ================= --}}
@can('create payments')

<div x-show="openPayment"
     x-transition.opacity
     class="modal">

<div @click.away="openPayment=false" class="modal-box">

<h2 class="modal-title">
<x-heroicon-o-banknotes class="icon-sm text-green-600"/>
Paiement fournisseur
</h2>

<form method="POST"
      action="{{ route('admin.purchases.pay',$purchase) }}"
      class="space-y-4">
@csrf

<input type="number"
    name="amount"
    value="{{ $purchase->due_amount }}"
    max="{{ $purchase->due_amount }}"
    step="0.01"
    class="input"
    required>

<div class="flex justify-end gap-3">
<button type="button" @click="openPayment=false" class="btn-light">Annuler</button>
<button class="btn-success">Valider</button>
</div>

</form>

</div>
</div>

@endcan

</div>

@endcan

{{-- ================= SAFE CSS (NO BREAK) ================= --}}

<style>

/* ICON FIX (IMPORTANT) */
.icon{width:20px;height:20px;}
.icon-sm{width:16px;height:16px;}
.icon-lg{width:28px;height:28px;}

.erp-hero{
background:linear-gradient(135deg,#4f46e5,#7c3aed);
color:white;
border-radius:24px;
padding:32px;
display:flex;
justify-content:space-between;
flex-wrap:wrap;
gap:24px;
box-shadow:0 10px 25px rgba(0,0,0,.15);
}

.hero-title{font-size:22px;font-weight:700;}
.hero-sub{font-size:13px;color:#e0e7ff;}

.hero-icon{
padding:14px;
background:rgba(255,255,255,.2);
border-radius:14px;
}

.erp-card{
background:white;
border-radius:22px;
border:1px solid #e5e7eb;
padding:28px;
transition:.25s;
}
.erp-card:hover{box-shadow:0 8px 20px rgba(0,0,0,.06);}

.card-title{
display:flex;
align-items:center;
gap:8px;
font-weight:600;
margin-bottom:16px;
}

.stat-card{
background:white;
border-radius:16px;
padding:22px;
border:1px solid #e5e7eb;
}
.stat-card h2{font-size:22px;font-weight:700;margin-top:6px;}
.success{border-color:#22c55e;}
.danger{border-color:#ef4444;}

.erp-table{width:100%;font-size:14px;}
.erp-table th{
background:#f9fafb;
padding:16px;
text-align:left;
font-size:12px;
text-transform:uppercase;
color:#6b7280;
}
.erp-table td{
padding:16px;
border-top:1px solid #f1f5f9;
}
.price{color:#4f46e5;font-weight:700;}

.info-grid li{
display:flex;
justify-content:space-between;
padding:8px 0;
border-bottom:1px solid #f1f5f9;
font-size:14px;
}
.info-grid span{color:#6b7280;}

.badge{
padding:4px 10px;
border-radius:999px;
font-size:12px;
font-weight:600;
}

.modal{
position:fixed;
inset:0;
background:rgba(0,0,0,.5);
display:flex;
align-items:center;
justify-content:center;
z-index:50;
}
.modal-box{
background:white;
border-radius:22px;
padding:28px;
width:100%;
max-width:420px;
}

.btn-primary{background:#4f46e5;color:white;padding:10px 18px;border-radius:12px;display:flex;gap:8px;align-items:center;}
.btn-success{background:#16a34a;color:white;padding:10px 18px;border-radius:12px;display:flex;gap:8px;align-items:center;}
.btn-dark{background:#111827;color:white;padding:10px 18px;border-radius:12px;display:flex;gap:8px;align-items:center;}
.btn-light{border:1px solid #e5e7eb;padding:10px 18px;border-radius:12px;display:flex;gap:8px;align-items:center;}

.input{
width:100%;
padding:10px;
border:1px solid #d1d5db;
border-radius:12px;
}

.fade-in{
animation:fadeIn .4s ease;
}
@keyframes fadeIn{
from{opacity:0;transform:translateY(10px)}
to{opacity:1;transform:translateY(0)}
}

</style>

</x-app-layout>
