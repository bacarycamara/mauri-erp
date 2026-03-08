<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<title>Facture {{ $sale->reference }}</title>

<style>

body{
    font-family: DejaVu Sans;
    font-size:12px;
    color:#1f2937;
    margin:0;
    padding:0;
}

/* WATERMARK */

.watermark{
    position:fixed;
    top:40%;
    left:20%;
    font-size:110px;
    color:rgba(16,185,129,0.07);
    transform:rotate(-30deg);
}

/* HEADER */

.header{
    padding:20px 30px;
    border-bottom:3px solid #4f46e5;
}

.company{
    float:left;
    width:60%;
}

.invoice-info{
    float:right;
    width:35%;
    text-align:right;
}

.logo{
    height:70px;
    margin-bottom:10px;
}

.company-name{
    font-size:18px;
    font-weight:bold;
}

.invoice-title{
    font-size:24px;
    font-weight:bold;
    color:#111827;
}

.status{
    display:inline-block;
    padding:6px 14px;
    font-size:10px;
    border-radius:20px;
    margin-top:10px;
    background:#e0e7ff;
    color:#3730a3;
}

/* SECTION */

.section{
    padding:20px 30px;
}

.box{
    background:#f9fafb;
    border:1px solid #e5e7eb;
    padding:15px;
    border-radius:6px;
}

/* TABLE */

table{
    width:100%;
    border-collapse:collapse;
    margin-top:15px;
}

th{
    background:#4f46e5;
    color:white;
    font-size:11px;
    text-transform:uppercase;
}

th,td{
    border:1px solid #e5e7eb;
    padding:8px;
}

.text-right{
    text-align:right;
}

/* TOTALS */

.totals{
    width:45%;
    float:right;
    margin-top:20px;
}

.totals td{
    border:none;
    padding:6px 0;
}

.total-main{
    font-size:14px;
    font-weight:bold;
    border-top:2px solid #111827;
    padding-top:8px;
}

/* FOOTER */

.footer{
    margin-top:60px;
    padding:15px;
    text-align:center;
    font-size:10px;
    color:#6b7280;
    border-top:1px solid #e5e7eb;
}

.clearfix:after{
    content:"";
    display:block;
    clear:both;
}

</style>

</head>

<body>

@if($sale->status === 'paid')
<div class="watermark">PAYÉ</div>
@endif


<div class="header clearfix">

<div class="company">

@if(company()?->logo)
<img src="{{ storage_path('app/public/'.company()->logo) }}" class="logo">
@endif

<div class="company-name">
{{ company()->name }}
</div>

{{ company()->full_address }} <br>
Tél : {{ company()->phone ?? '-' }} <br>
Email : {{ company()->email ?? '-' }} <br>
NIF : {{ company()->nif ?? '-' }} |
RC : {{ company()->rc ?? '-' }}

</div>


<div class="invoice-info">

<div class="invoice-title">FACTURE</div>

<strong>Référence :</strong> {{ $sale->reference }} <br>
<strong>Date :</strong> {{ $sale->sale_date->format('d/m/Y') }} <br>

<strong>Échéance :</strong>
{{ $sale->sale_date->copy()->addDays(30)->format('d/m/Y') }}

<div class="status">
{{ strtoupper($sale->status) }}
</div>

</div>

</div>


<div class="section">

<div class="box">

<strong>Facturé à :</strong><br><br>

<strong>{{ $sale->customer->name }}</strong><br>

@if($sale->customer->phone)
Tél : {{ $sale->customer->phone }}<br>
@endif

@if($sale->customer->email)
Email : {{ $sale->customer->email }}<br>
@endif

@if($sale->customer->city)
Ville : {{ $sale->customer->city }}
@endif

</div>

</div>


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

@foreach($sale->items as $item)

<tr>

<td>{{ $item->product->name }}</td>

<td class="text-right">
{{ number_format($item->quantity,2) }}
</td>

<td class="text-right">
{{ number_format($item->unit_price,2) }} {{ company()->currency }}
</td>

<td class="text-right">
{{ $item->vat_rate }}%
</td>

<td class="text-right">
{{ $item->discount_rate }}%
</td>

<td class="text-right">
{{ number_format($item->total,2) }} {{ company()->currency }}
</td>

</tr>

@endforeach

</tbody>

</table>

</div>


<div class="section">

<table class="totals">

<tr>
<td>Sous-total :</td>
<td class="text-right">
{{ number_format($sale->subtotal,2) }} {{ company()->currency }}
</td>
</tr>

<tr>
<td>TVA :</td>
<td class="text-right">
{{ number_format($sale->vat_amount,2) }} {{ company()->currency }}
</td>
</tr>

<tr>
<td>Remise :</td>
<td class="text-right">
{{ number_format($sale->discount_amount,2) }} {{ company()->currency }}
</td>
</tr>

<tr class="total-main">
<td>Total TTC :</td>
<td class="text-right">
{{ number_format($sale->total_amount,2) }} {{ company()->currency }}
</td>
</tr>

<tr>
<td>Montant payé :</td>
<td class="text-right">
{{ number_format($sale->paid_amount,2) }} {{ company()->currency }}
</td>
</tr>

<tr class="total-main">
<td>Reste à payer :</td>
<td class="text-right">
{{ number_format($sale->due_amount,2) }} {{ company()->currency }}
</td>
</tr>

</table>

</div>


@if($sale->notes)

<div class="section">

<div class="box">

<strong>Notes :</strong><br><br>

{{ $sale->notes }}

</div>

</div>

@endif


<div class="footer">

{{ company()->invoice_footer ?? 'Merci pour votre confiance.' }}

<br>

© {{ date('Y') }} {{ company()->name }} — Tous droits réservés.

</div>


</body>
</html>