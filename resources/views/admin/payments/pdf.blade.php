<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Reçu {{ $payment->payment_number ?? $payment->id }}</title>

<style>

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 11px;
    color: #1f2937;
    margin: 0;
}

/* ===== WATERMARK ===== */
.watermark {
    position: fixed;
    top: 40%;
    left: 20%;
    font-size: 95px;
    color: rgba(16,185,129,0.07);
    transform: rotate(-30deg);
    z-index: -1;
}

/* ===== HEADER ===== */
.header {
    padding: 20px 30px;
    border-bottom: 3px solid #4f46e5;
}

.logo {
    height: 65px;
}

.company-name {
    font-size: 16px;
    font-weight: bold;
}

.title {
    font-size: 20px;
    font-weight: bold;
}

.right {
    text-align: right;
}

/* ===== BADGES ===== */
.badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 9px;
    font-weight: bold;
}

.confirmed  { background: #d1fae5; color: #065f46; }
.pending    { background: #fef3c7; color: #92400e; }
.cancelled  { background: #fee2e2; color: #991b1b; }

/* ===== SECTION ===== */
.section {
    margin: 18px 30px;
    border: 1px solid #e5e7eb;
    padding: 12px;
    border-radius: 6px;
    background: #f9fafb;
}

/* ===== TABLE ===== */
table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #4f46e5;
    color: #fff;
    padding: 7px;
    font-size: 10px;
    text-transform: uppercase;
}

td {
    padding: 7px;
    border-bottom: 1px solid #e5e7eb;
}

.amount {
    text-align: right;
    font-size: 12px;
    font-weight: 600;
}

.info-grid td {
    border: none;
    padding: 2px 0;
}

/* ===== FOOTER ===== */
.footer {
    margin-top: 35px;
    padding: 15px;
    font-size: 9px;
    text-align: center;
    color: #6b7280;
    border-top: 1px solid #e5e7eb;
}

.powered {
    margin-top: 5px;
    font-size: 8px;
    color: #9ca3af;
}

</style>
</head>

<body>

@php
    // Whitelist des statuts autorisés pour la classe CSS badge
    $safeStatus = in_array($payment->status, ['confirmed','pending','cancelled'])
        ? $payment->status
        : 'pending';
    $currency = company()?->currency ?? '';
@endphp

{{-- WATERMARK --}}
@if($payment->status === 'confirmed')
<div class="watermark">PAYÉ</div>
@endif


{{-- ================= HEADER ================= --}}
<div class="header">
<table>
<tr>

    <td width="55%">

        @if(company()?->logo)
        {{-- ✅ storage_path() correct pour DomPDF (chemin absolu) --}}
        <img src="{{ storage_path('app/public/' . company()->logo) }}" class="logo">
        @endif

        <div class="company-name">{{ company()?->name }}</div>
        {{ company()?->full_address }}<br>
        Tél : {{ company()?->phone ?? '-' }}<br>
        Email : {{ company()?->email ?? '-' }}

    </td>

    <td width="45%" class="right">

        <div class="title">REÇU DE PAIEMENT</div><br>
        <strong>N° :</strong> {{ $payment->payment_number }}<br>
        <strong>Date :</strong> {{ $payment->payment_date?->format('d/m/Y') }}
        <br><br>

        {{-- ✅ SÉCURISÉ : classe CSS via whitelist uniquement --}}
        <span class="badge {{ $safeStatus }}">
            {{ strtoupper($safeStatus) }}
        </span>

    </td>

</tr>
</table>
</div>


{{-- ================= CLIENT / FOURNISSEUR ================= --}}
<div class="section">

    @if($payment->sale)
    <table class="info-grid">
        <tr>
            <td width="35%"><strong>Client :</strong></td>
            {{-- ✅ htmlspecialchars() au lieu de {{ }} car on est dans un PDF sans Blade auto-escape --}}
            <td>{{ $payment->sale->customer?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Référence Vente :</strong></td>
            <td>{{ $payment->sale->reference }}</td>
        </tr>
        <tr>
            <td><strong>Date Vente :</strong></td>
            <td>{{ $payment->sale->sale_date?->format('d/m/Y') ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Total Vente :</strong></td>
            <td>{{ number_format($payment->sale->total_amount, 2) }} {{ $currency }}</td>
        </tr>
        <tr>
            <td><strong>Reste dû :</strong></td>
            <td>{{ number_format($payment->sale->due_amount, 2) }} {{ $currency }}</td>
        </tr>
    </table>
    @endif

    @if($payment->purchase)
    <table class="info-grid">
        <tr>
            <td width="35%"><strong>Fournisseur :</strong></td>
            <td>{{ $payment->purchase->supplier?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Référence Achat :</strong></td>
            <td>{{ $payment->purchase->reference }}</td>
        </tr>
        <tr>
            <td><strong>Date Achat :</strong></td>
            <td>{{ $payment->purchase->purchase_date?->format('d/m/Y') ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Total Achat :</strong></td>
            <td>{{ number_format($payment->purchase->total_amount, 2) }} {{ $currency }}</td>
        </tr>
        <tr>
            <td><strong>Reste dû :</strong></td>
            <td>{{ number_format($payment->purchase->due_amount, 2) }} {{ $currency }}</td>
        </tr>
    </table>
    @endif

</div>


{{-- ================= PRODUITS ================= --}}
@php
    $hasItems = ($payment->sale && $payment->sale->items->isNotEmpty())
             || ($payment->purchase && $payment->purchase->items->isNotEmpty());
@endphp

@if($hasItems)
<div class="section">
    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th width="10%">Qté</th>
                <th width="20%">Prix unitaire</th>
                <th width="20%">Total</th>
            </tr>
        </thead>
        <tbody>

            @if($payment->sale)
            @foreach($payment->sale->items as $item)
            <tr>
                <td>{{ $item->product?->name ?? '-' }}</td>
                <td class="amount">{{ $item->quantity }}</td>
                <td class="amount">{{ number_format($item->unit_price, 2) }} {{ $currency }}</td>
                <td class="amount">{{ number_format($item->total, 2) }} {{ $currency }}</td>
            </tr>
            @endforeach
            @endif

            @if($payment->purchase)
            @foreach($payment->purchase->items as $item)
            <tr>
                <td>{{ $item->product?->name ?? '-' }}</td>
                <td class="amount">{{ $item->quantity }}</td>
                <td class="amount">{{ number_format($item->unit_price, 2) }} {{ $currency }}</td>
                <td class="amount">{{ number_format($item->total, 2) }} {{ $currency }}</td>
            </tr>
            @endforeach
            @endif

        </tbody>
    </table>
</div>
@endif


{{-- ================= DÉTAILS PAIEMENT ================= --}}
<div class="section">
    <table>
        <thead>
            <tr>
                <th width="30%">Type</th>
                <th width="25%">Méthode</th>
                <th width="20%">Référence</th>
                <th width="25%">Montant</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    {{ $payment->type === 'in'
                        ? 'Entrée (Paiement Client)'
                        : 'Sortie (Paiement Fournisseur)' }}
                </td>
                <td>{{ $payment->payment_method_label ?? str_replace('_', ' ', $payment->payment_method) }}</td>
                <td>{{ $payment->reference ?? '-' }}</td>
                <td class="amount">
                    {{ number_format($payment->amount, 2) }} {{ $currency }}
                </td>
            </tr>
        </tbody>
    </table>
</div>


{{-- ================= NOTES ================= --}}
@if($payment->notes)
<div class="section">
    <strong>Notes :</strong><br><br>
    {{-- ✅ nl2br pour conserver les sauts de ligne --}}
    {!! nl2br(e($payment->notes)) !!}
</div>
@endif


{{-- ================= SIGNATURES ================= --}}
<br><br>
<table width="90%" align="center">
    <tr>
        <td>
            Signature Client / Fournisseur<br><br><br>
            ______________________________
        </td>
        <td style="text-align:right;">
            Signature Responsable<br><br><br>
            ______________________________
        </td>
    </tr>
</table>


{{-- ================= FOOTER ================= --}}
<div class="footer">
    Document généré automatiquement le {{ now()->format('d/m/Y H:i') }}<br>
    © {{ date('Y') }} {{ company()?->name }} — Tous droits réservés.
    <div class="powered">
        Généré par {{ config('app.vendor.name') }} — {{ config('app.vendor.website') }}
    </div>
</div>

</body>
</html>