<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Facture {{ $sale->reference }}</title>

<style>

body {
    font-family: DejaVu Sans;
    font-size: 12px;
    color: #1f2937;
    margin: 0;
    padding: 0;
}

/* WATERMARK */
.watermark {
    position: fixed;
    top: 40%;
    left: 20%;
    font-size: 110px;
    transform: rotate(-30deg);
}

.watermark-paid      { color: rgba(16,185,129,0.07); }
.watermark-pending   { color: rgba(234,179,8,0.07); }
.watermark-cancelled { color: rgba(239,68,68,0.07); }

/* HEADER */
.header {
    padding: 20px 30px;
    border-bottom: 3px solid #4f46e5;
}

.company      { float: left; width: 60%; }
.invoice-info { float: right; width: 35%; text-align: right; }
.logo         { height: 70px; margin-bottom: 10px; }
.company-name { font-size: 18px; font-weight: bold; }
.invoice-title { font-size: 24px; font-weight: bold; color: #111827; }

/* STATUS BADGE */
.status {
    display: inline-block;
    padding: 6px 14px;
    font-size: 10px;
    border-radius: 20px;
    margin-top: 10px;
    font-weight: bold;
}

.status-draft     { background: #f3f4f6; color: #374151; }
.status-confirmed { background: #dbeafe; color: #1e40af; }
.status-partial   { background: #fef3c7; color: #92400e; }
.status-paid      { background: #dcfce7; color: #166534; }
.status-validated { background: #e0e7ff; color: #3730a3; }
.status-cancelled { background: #fee2e2; color: #991b1b; }

/* SECTION */
.section { padding: 20px 30px; }

.box {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    padding: 15px;
    border-radius: 6px;
}

/* TABLE */
table           { width: 100%; border-collapse: collapse; margin-top: 15px; }
th              { background: #4f46e5; color: white; font-size: 11px; text-transform: uppercase; }
th, td          { border: 1px solid #e5e7eb; padding: 8px; }
.text-right     { text-align: right; }

/* TOTALS */
.totals      { width: 45%; float: right; margin-top: 20px; }
.totals td   { border: none; padding: 6px 0; }
.total-main  { font-size: 14px; font-weight: bold; border-top: 2px solid #111827; padding-top: 8px; }

/* FOOTER */
.footer {
    margin-top: 60px;
    padding: 15px;
    text-align: center;
    font-size: 10px;
    color: #6b7280;
    border-top: 1px solid #e5e7eb;
}

.clearfix:after { content: ""; display: block; clear: both; }

</style>
</head>

<body>

@php
    // Variables extraites une seule fois — évite appels répétés à company()
    $co          = company();
    $currency    = $co?->currency ?? '';
    $companyName = $co?->name ?? config('app.name');

    // ✅ Whitelist statut pour classes CSS et textes
    $safeStatus = in_array($sale->status, ['draft','confirmed','partial','paid','validated','cancelled'])
        ? $sale->status
        : 'draft';

    $statusLabels = [
        'draft'     => 'BROUILLON',
        'confirmed' => 'CONFIRMÉ',
        'partial'   => 'PARTIEL',
        'paid'      => 'PAYÉ',
        'validated' => 'VALIDÉ',
        'cancelled' => 'ANNULÉ',
    ];

    $watermarkClass = match($safeStatus) {
        'paid', 'validated'    => 'watermark-paid',
        'cancelled'            => 'watermark-cancelled',
        default                => 'watermark-pending',
    };

    $watermarkText = $statusLabels[$safeStatus] ?? strtoupper($safeStatus);
@endphp

{{-- WATERMARK --}}
<div class="watermark {{ $watermarkClass }}">{{ $watermarkText }}</div>


{{-- HEADER --}}
<div class="header clearfix">

    <div class="company">
        @if($co?->logo)
        <img src="{{ storage_path('app/public/' . $co->logo) }}" class="logo">
        @endif

        <div class="company-name">{{ $companyName }}</div>
        {{ $co?->full_address ?? '' }}<br>
        Tél : {{ $co?->phone ?? '-' }}<br>
        Email : {{ $co?->email ?? '-' }}<br>
        NIF : {{ $co?->nif ?? '-' }} | RC : {{ $co?->rc ?? '-' }}
    </div>

    <div class="invoice-info">
        <div class="invoice-title">FACTURE</div>

        <strong>Référence :</strong> {{ $sale->reference }}<br>
        <strong>Date :</strong> {{ $sale->sale_date?->format('d/m/Y') ?? '-' }}<br>
        <strong>Échéance :</strong>
        {{ $sale->sale_date?->copy()->addDays(30)->format('d/m/Y') ?? '-' }}

        {{-- ✅ Classe CSS via whitelist --}}
        <div class="status status-{{ $safeStatus }}">
            {{ $statusLabels[$safeStatus] }}
        </div>
    </div>

</div>


{{-- CLIENT --}}
<div class="section">
<div class="box">
    <strong>Facturé à :</strong><br><br>

    <strong>{{ $sale->customer?->name ?? '-' }}</strong><br>

    @if($sale->customer?->phone)
    Tél : {{ $sale->customer->phone }}<br>
    @endif

    @if($sale->customer?->email)
    Email : {{ $sale->customer->email }}<br>
    @endif

    @if($sale->customer?->city)
    Ville : {{ $sale->customer->city }}
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
    @foreach($sale->items as $item)
    <tr>
        {{-- ✅ e() sur nom produit --}}
        <td>{{ e($item->product?->name ?? '-') }}</td>
        <td class="text-right">{{ number_format($item->quantity ?? 0, 2) }}</td>
        <td class="text-right">{{ number_format($item->unit_price ?? 0, 2) }} {{ $currency }}</td>
        <td class="text-right">{{ $item->vat_rate ?? 0 }}%</td>
        <td class="text-right">{{ $item->discount_rate ?? 0 }}%</td>
        <td class="text-right">{{ number_format($item->total ?? 0, 2) }} {{ $currency }}</td>
    </tr>
    @endforeach
    </tbody>
</table>
</div>


{{-- TOTAUX --}}
<div class="section">
<table class="totals">
    <tr>
        <td>Sous-total :</td>
        <td class="text-right">{{ number_format($sale->subtotal ?? 0, 2) }} {{ $currency }}</td>
    </tr>
    <tr>
        <td>TVA :</td>
        <td class="text-right">{{ number_format($sale->vat_amount ?? 0, 2) }} {{ $currency }}</td>
    </tr>
    <tr>
        <td>Remise :</td>
        <td class="text-right">{{ number_format($sale->discount_amount ?? 0, 2) }} {{ $currency }}</td>
    </tr>
    <tr class="total-main">
        <td>Total TTC :</td>
        <td class="text-right">{{ number_format($sale->total_amount ?? 0, 2) }} {{ $currency }}</td>
    </tr>
    <tr>
        <td>Montant payé :</td>
        <td class="text-right">{{ number_format($sale->paid_amount ?? 0, 2) }} {{ $currency }}</td>
    </tr>
    <tr class="total-main">
        <td>Reste à payer :</td>
        <td class="text-right">{{ number_format($sale->due_amount ?? 0, 2) }} {{ $currency }}</td>
    </tr>
</table>
</div>


{{-- NOTES --}}
@if($sale->notes)
<div class="section">
<div class="box">
    <strong>Notes :</strong><br><br>
    {{-- ✅ nl2br + e() pour sauts de ligne sans XSS --}}
    {!! nl2br(e($sale->notes)) !!}
</div>
</div>
@endif


{{-- FOOTER --}}
<div class="footer">
    {{ $co?->invoice_footer ?? 'Merci pour votre confiance.' }}<br>
    © {{ date('Y') }} {{ $companyName }} — Tous droits réservés.
</div>

</body>
</html>