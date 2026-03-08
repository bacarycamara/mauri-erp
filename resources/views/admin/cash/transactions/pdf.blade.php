<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport Caisse - {{ $cashRegister->name }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #2d3748;
        }

        .header {
            margin-bottom: 15px;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
        }

        .company-info {
            font-size: 11px;
            color: #555;
            margin-top: 3px;
        }

        hr {
            border: none;
            border-top: 2px solid #1f2937;
            margin: 15px 0;
        }

        h2 {
            margin: 10px 0;
        }

        .info p {
            margin: 3px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th {
            background: #1f2937;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }

        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }

        .right {
            text-align: right;
        }

        .green {
            color: #16a34a;
            font-weight: bold;
        }

        .red {
            color: #dc2626;
            font-weight: bold;
        }

        .summary {
            margin-top: 20px;
            border: 1px solid #ddd;
            padding: 10px;
        }

        .summary table {
            margin-top: 0;
        }

        .summary td {
            padding: 6px;
        }

        .total-final {
            border-top: 2px solid #000;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            font-size: 10px;
            text-align: center;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 6px;
        }
    </style>
</head>

<body>

{{-- ================= HEADER ================= --}}
<div class="header">

    <div class="company-name">
        {{ company()?->name ?? 'Entreprise' }}
    </div>

    <div class="company-info">
        {{ company()?->address ?? '' }}<br>
        {{ company()?->phone ?? '' }}
        @if(company()?->phone && company()?->email) |
        @endif
        {{ company()?->email ?? '' }}
    </div>

</div>

<hr>

<h2>Rapport de Caisse</h2>

<div class="info">
    <p><strong>Caisse :</strong> {{ $cashRegister->name }}</p>
    <p><strong>Statut :</strong> {{ ucfirst($cashRegister->status) }}</p>
    <p><strong>Date ouverture :</strong>
        {{ $cashRegister->opened_at?->format('d/m/Y H:i') }}
    </p>
    <p><strong>Date fermeture :</strong>
        {{ $cashRegister->closed_at?->format('d/m/Y H:i') ?? '-' }}
    </p>
</div>

{{-- ================= TABLE ================= --}}
<table>
    <thead>
        <tr>
            <th width="15%">Référence</th>
            <th width="12%">Type</th>
            <th width="28%">Description</th>
            <th width="18%">Date</th>
            <th width="15%" class="right">Montant</th>
        </tr>
    </thead>
    <tbody>

        @forelse($transactions as $transaction)
            <tr>
                <td>{{ $transaction->reference }}</td>

                <td>
                    {{ $transaction->type === 'in' ? 'Entrée' : 'Sortie' }}
                </td>

                <td>{{ $transaction->description ?? '-' }}</td>

                <td>
                    {{ $transaction->created_at->format('d/m/Y H:i') }}
                </td>

                <td class="right">
                    @if($transaction->type === 'in')
                        <span class="green">
                            +{{ number_format($transaction->amount,2) }}
                        </span>
                    @else
                        <span class="red">
                            -{{ number_format($transaction->amount,2) }}
                        </span>
                    @endif
                    {{ company()?->currency }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align:center; padding:15px;">
                    Aucune transaction enregistrée
                </td>
            </tr>
        @endforelse

    </tbody>
</table>

{{-- ================= SUMMARY ================= --}}
<div class="summary">
    <table width="100%">
        <tr>
            <td><strong>Total Entrées :</strong></td>
            <td class="right green">
                {{ number_format($totalIn,2) }}
                {{ company()?->currency }}
            </td>
        </tr>

        <tr>
            <td><strong>Total Sorties :</strong></td>
            <td class="right red">
                {{ number_format($totalOut,2) }}
                {{ company()?->currency }}
            </td>
        </tr>

        <tr>
            <td class="total-final"><strong>Solde Final :</strong></td>
            <td class="right total-final">
                {{ number_format($cashRegister->current_balance,2) }}
                {{ company()?->currency }}
            </td>
        </tr>
    </table>
</div>

<div class="footer">
    Rapport généré le {{ now()->format('d/m/Y H:i') }} —
    {{ config('app.name') }}
</div>

</body>
</html>