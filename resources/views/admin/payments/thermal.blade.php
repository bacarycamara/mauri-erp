<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Ticket {{ $payment->payment_number }}</title>

<style>

@page{
    size:80mm auto;
    margin:0;
}

body{
    width:72mm;
    margin:0 auto;
    font-family:"Courier New", monospace;
    font-size:11px;
    color:#000;
}

.center{text-align:center;}
.right{text-align:right;}
.bold{font-weight:bold;}
.small{font-size:10px}

.line{
    border-top:1px dashed #000;
    margin:6px 0;
}

table{
    width:100%;
    border-collapse:collapse;
}

td{
    padding:2px 0;
    vertical-align:top;
}

.total{
    font-size:14px;
    font-weight:bold;
}

</style>
</head>

<body onload="window.print()">

@php

$transactionId = 'TXN-'.date('Ymd').'-'.$payment->id;

$totalFacture = $payment->sale?->total_amount
    ?? $payment->purchase?->total_amount
    ?? 0;

$reste = $payment->sale?->due_amount
    ?? $payment->purchase?->due_amount
    ?? 0;

$tva = $payment->sale?->vat_amount
    ?? $payment->purchase?->vat_amount
    ?? 0;

$currency = company()?->currency ?? '';

@endphp


{{-- ================= HEADER ================= --}}
<div class="center bold">
{{ company()?->name ?? config('app.name') }}
</div>

<div class="center small">
{{ company()?->full_address ?? '' }}<br>
Tel : {{ company()?->phone ?? '' }}
</div>

@if(company()?->nif)
<div class="center small">
NIF : {{ company()->nif }}
</div>
@endif

<div class="center bold">
REÇU DE PAIEMENT
</div>

<div class="line"></div>

Transaction : {{ $transactionId }}<br>
Ticket : {{ $payment->payment_number }}<br>
Date : {{ $payment->payment_date?->format('d/m/Y H:i') }}<br>
Caissier : {{ $payment->user?->name ?? '-' }}

<div class="line"></div>


{{-- CLIENT / FOURNISSEUR --}}
@if($payment->sale)
Client : {{ $payment->sale?->customer?->name ?? 'Client direct' }}<br>
Facture : {{ $payment->sale->reference }}
@endif

@if($payment->purchase)
Fournisseur : {{ $payment->purchase?->supplier?->name ?? '-' }}<br>
Achat : {{ $payment->purchase->reference }}
@endif

<div class="line"></div>


{{-- PRODUITS --}}
<table>

<tr class="bold">
<td>Produit</td>
<td class="right">Qté</td>
<td class="right">Total</td>
</tr>

@if($payment->sale)
@foreach($payment->sale->items as $item)
<tr>
<td>{{ \Illuminate\Support\Str::limit($item->product?->name,18) }}</td>
<td class="right">{{ $item->quantity }}</td>
<td class="right">
{{ number_format($item->total,0,',',' ') }}
</td>
</tr>
@endforeach
@endif

@if($payment->purchase)
@foreach($payment->purchase->items as $item)
<tr>
<td>{{ \Illuminate\Support\Str::limit($item->product?->name,18) }}</td>
<td class="right">{{ $item->quantity }}</td>
<td class="right">
{{ number_format($item->total,0,',',' ') }}
</td>
</tr>
@endforeach
@endif

</table>

<div class="line"></div>


{{-- RÉCAP --}}
<table>

<tr>
<td>Total facture</td>
<td class="right">
{{ number_format($totalFacture,0,',',' ') }} {{ $currency }}
</td>
</tr>

@if($tva > 0)
<tr>
<td>TVA incluse</td>
<td class="right">
{{ number_format($tva,0,',',' ') }} {{ $currency }}
</td>
</tr>
@endif

<tr class="total">
<td>PAYÉ</td>
<td class="right">
{{ number_format($payment->amount,0,',',' ') }} {{ $currency }}
</td>
</tr>

<tr>
<td>Reste</td>
<td class="right">
{{ number_format($reste,0,',',' ') }} {{ $currency }}
</td>
</tr>

</table>

<div class="line"></div>

Mode : <strong>{{ $payment->payment_method_label ?? '-' }}</strong>

@if($payment->reference)
<br>Réf : {{ $payment->reference }}
@endif

<div class="line"></div>


{{-- FOOTER --}}
<div class="center">
Merci pour votre confiance 🙏
</div>

<div class="center small">
{{ now()->format('d/m/Y H:i') }}
</div>

<div class="center small">
{{ config('app.vendor.name') }}
</div>

<br><br><br><br>

</body>
</html>