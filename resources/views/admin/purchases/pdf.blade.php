<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Facture Achat {{ $purchase->reference }}</title>

<style>

@page { margin: 25px; }

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 11px;
    color: #1f2937;
}

/* ===== ANTI PAGE BREAK ===== */
.header,
.section,
.box,
table,
tr,
td,
th,
.footer {
    page-break-inside: avoid !important;
}

.section { margin-top: 15px; }

/* ================= WATERMARK ================= */
.watermark {
    position: fixed;
    top: 40%;
    left: 18%;
    font-size: 100px;
    transform: rotate(-30deg);
    z-index: -1;
    font-weight: bold;
}

.watermark-paid { color: rgba(34,197,94,0.08); }
.watermark-pending { color: rgba(234,179,8,0.08); }
.watermark-cancelled { color: rgba(239,68,68,0.08); }

/* ================= HEADER ================= */
.header-table {
    width: 100%;
    border-bottom: 3px solid #dc2626;
    padding-bottom: 10px;
}

.logo { height: 65px; }

.company-name {
    font-size: 17px;
    font-weight: bold;
}

.invoice-title {
    font-size: 22px;
    font-weight: bold;
}

/* ================= STATUS ================= */
.status {
    display: inline-block;
    padding: 5px 12px;
    font-size: 10px;
    border-radius: 20px;
    margin-top: 6px;
    font-weight: bold;
}

.status-paid {
    background: #dcfce7;
    color: #166534;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

/* ================= BOX ================= */
.box {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    padding: 12px;
    border-radius: 5px;
}

/* ================= TABLE ================= */
table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #dc2626;
    color: white;
    font-size: 10px;
    text-transform: uppercase;
}

th, td {
    border: 1px solid #e5e7eb;
    padding: 7px;
}

.text-right { text-align: right; }

/* ================= TOTALS ================= */
.totals {
    width: 100%;
    margin-top: 15px;
}

.totals td {
    border: none;
    padding: 4px 0;
}

.total-main {
    font-weight: bold;
    font-size: 13px;
    border-top: 2px solid #111827;
}

/* ================= FOOTER ================= */
.footer {
    margin-top: 30px;
    text-align: center;
    font-size: 9px;
    color: #6b7280;
    border-top: 1px solid #e5e7eb;
    padding-top: 10px;
}

.powered {
    font-size: 8px;
    color: #9ca3af;
}

</style>
</head>

<body>

@php

$currency = company()?->currency ?? '';

$statusClass = match($purchase->status) {
    'paid' => 'status-paid',
    'pending','draft' => 'status-pending',
    'cancelled' => 'status-cancelled',
    default => 'status-pending',
};

$watermarkClass = match($purchase->status) {
    'paid' => 'watermark-paid',
    'pending','draft' => 'watermark-pending',
    'cancelled' => 'watermark-cancelled',
    default => 'watermark-pending',
};

$watermarkText = match($purchase->status) {
    'paid' => 'PAYÉ',
    'pending','draft' => 'EN ATTENTE',
    'cancelled' => 'ANNULÉ',
    default => strtoupper($purchase->status),
};

@endphp


{{-- WATERMARK --}}
<div class="watermark {{ $watermarkClass }}">
{{ $watermarkText }}
</div>


{{-- HEADER --}}
<table class="header-table">
<tr>

<td width="60%">

@if(company()?->logo)
<img src="{{ storage_path('app/public/'.company()->logo) }}" class="logo"><br>
@endif

<div class="company-name">{{ company()?->name }}</div>

{{ company()?->full_address }}<br>
Tél : {{ company()?->phone ?? '-' }}<br>
Email : {{ company()?->email ?? '-' }}<br>
NIF : {{ company()?->nif ?? '-' }} |
RC : {{ company()?->rc ?? '-' }}

</td>

<td width="40%" class="text-right">

<div class="invoice-title">FACTURE ACHAT</div>

<strong>Référence :</strong> {{ $purchase->reference }}<br>

<strong>Date :</strong>
{{ $purchase->purchase_date?->format('d/m/Y') }}<br>

<strong>Échéance :</strong>
{{ $purchase->purchase_date?->copy()->addDays(30)->format('d/m/Y') }}

<br>

<span class="status {{ $statusClass }}">
{{ strtoupper($purchase->status) }}
</span>

</td>

</tr>
</table>


{{-- FOURNISSEUR --}}
<div class="section">
<div class="box">

<strong>Fournisseur :</strong><br><br>

<strong>{{ $purchase->supplier?->name }}</strong><br>

@if($purchase->supplier?->address)
{{ $purchase->supplier->address }}<br>
@endif

@if($purchase->supplier?->city)
{{ $purchase->supplier->city }}<br>
@endif

@if($purchase->supplier?->phone)
Tél : {{ $purchase->supplier->phone }}<br>
@endif

@if($purchase->supplier?->email)
Email : {{ $purchase->supplier->email }}
@endif

</div>
</div>


{{-- PRODUITS --}}
<div class="section">
<table>

<thead>
<tr>
<th width="35%">Produit</th>
<th width="10%" class="text-right">Qté</th>
<th width="15%" class="text-right">Prix</th>
<th width="10%" class="text-right">TVA</th>
<th width="10%" class="text-right">Remise</th>
<th width="20%" class="text-right">Total</th>
</tr>
</thead>

<tbody>

@foreach($purchase->items as $item)

<tr>

<td>{{ $item->product?->name }}</td>

<td class="text-right">{{ $item->quantity }}</td>

<td class="text-right">
{{ number_format($item->unit_price,2) }} {{ $currency }}
</td>

<td class="text-right">
{{ $item->vat_rate }}%
</td>

<td class="text-right">
{{ $item->discount_rate }}%
</td>

<td class="text-right">
{{ number_format($item->total,2) }} {{ $currency }}
</td>

</tr>

@endforeach

</tbody>

</table>
</div>


{{-- TOTALS --}}
<table class="totals">

<tr>
<td>Sous-total :</td>
<td class="text-right">
{{ number_format($purchase->subtotal,2) }} {{ $currency }}
</td>
</tr>

<tr>
<td>TVA :</td>
<td class="text-right">
{{ number_format($purchase->vat_amount,2) }} {{ $currency }}
</td>
</tr>

<tr>
<td>Remise :</td>
<td class="text-right">
{{ number_format($purchase->discount_amount,2) }} {{ $currency }}
</td>
</tr>

<tr class="total-main">
<td>Total TTC :</td>
<td class="text-right">
{{ number_format($purchase->total_amount,2) }} {{ $currency }}
</td>
</tr>

<tr>
<td>Montant payé :</td>
<td class="text-right">
{{ number_format($purchase->paid_amount,2) }} {{ $currency }}
</td>
</tr>

<tr class="total-main">
<td>Reste à payer :</td>
<td class="text-right">
{{ number_format($purchase->due_amount,2) }} {{ $currency }}
</td>
</tr>

</table>


{{-- NOTES --}}
@if($purchase->notes)

<div class="section">

<div class="box">

<strong>Notes :</strong><br><br>

{{ $purchase->notes }}

</div>

</div>

@endif


{{-- FOOTER --}}
<div class="footer">

{{ company()?->invoice_footer ?? 'Document interne généré automatiquement.' }}<br>

© {{ date('Y') }} {{ company()?->name }} — Tous droits réservés.

<div class="powered">

Généré par {{ config('app.vendor.name') }}
— {{ config('app.vendor.website') }}

</div>

</div>

</body>
</html>