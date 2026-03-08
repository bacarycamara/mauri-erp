<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport Caisse - {{ $cashRegister->name }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #2d3748;
        }

        h1 {
            font-size: 20px;
            margin: 0;
        }

        h2 {
            font-size: 14px;
            margin: 0 0 5px 0;
        }

        .header {
            border-bottom: 3px solid #4f46e5;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .company {
            font-size: 11px;
            margin-bottom: 5px;
            color: #555;
        }

        .info-table td {
            padding: 3px 0;
        }

        .summary-box {
            border: 1px solid #ddd;
            padding: 12px;
            margin: 20px 0;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 6px 8px;
        }

        .summary-table .label {
            font-weight: bold;
        }

        .summary-table .value {
            text-align: right;
        }

        .highlight {
            border-top: 2px solid #000;
            font-weight: bold;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 6px;
        }

        .table th {
            background-color: #f3f4f6;
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .text-green {
            color: #16a34a;
        }

        .text-red {
            color: #dc2626;
        }

        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }

        .badge-open {
            background-color: #dcfce7;
            color: #166534;
        }

        .badge-closed {
            background-color: #e5e7eb;
            color: #374151;
        }

        .footer {
            margin-top: 30px;
            font-size: 10px;
            text-align: center;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }
    </style>
</head>
<body>

{{-- ================= HEADER ================= --}}
<div class="header">

    <div class="company">
        <strong>{{ company()?->name }}</strong><br>
        Devise : {{ company()?->currency }}<br>
        Rapport généré le {{ now()->format('d/m/Y H:i') }}
    </div>

    <h1>Rapport de Caisse</h1>
    <h2>{{ $cashRegister->name }}</h2>

    <table class="info-table">
        <tr>
            <td><strong>Statut :</strong></td>
            <td>
                @if($cashRegister->status === 'open')
                    <span class="badge badge-open">Ouverte</span>
                @else
                    <span class="badge badge-closed">Fermée</span>
                @endif
            </td>
        </tr>
        <tr>
            <td><strong>Date d'ouverture :</strong></td>
            <td>{{ $cashRegister->opened_at?->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td><strong>Date de fermeture :</strong></td>
            <td>{{ $cashRegister->closed_at?->format('d/m/Y H:i') ?? '-' }}</td>
        </tr>
    </table>

</div>


{{-- ================= SUMMARY ================= --}}
<div class="summary-box">

    <table class="summary-table">
        <tr>
            <td class="label">Solde Initial</td>
            <td class="value">
                {{ number_format($cashRegister->opening_balance,2) }}
                {{ company()?->currency }}
            </td>
        </tr>

        <tr>
            <td class="label">Total Entrées</td>
            <td class="value text-green">
                + {{ number_format($cashRegister->total_in,2) }}
                {{ company()?->currency }}
            </td>
        </tr>

        <tr>
            <td class="label">Total Sorties</td>
            <td class="value text-red">
                - {{ number_format($cashRegister->total_out,2) }}
                {{ company()?->currency }}
            </td>
        </tr>

        <tr>
            <td class="label highlight">Solde Final</td>
            <td class="value highlight">
                {{ number_format($cashRegister->closing_balance,2) }}
                {{ company()?->currency }}
            </td>
        </tr>
    </table>

</div>


{{-- ================= TRANSACTIONS ================= --}}
<h2>Liste des Transactions</h2>

<table class="table">
    <thead>
        <tr>
            <th width="15%">Date</th>
            <th width="15%">Référence</th>
            <th width="15%">Source</th>
            <th width="25%">Description</th>
            <th width="15%" class="text-right">Entrée</th>
            <th width="15%" class="text-right">Sortie</th>
        </tr>
    </thead>
    <tbody>

    @forelse($cashRegister->transactions->sortBy('created_at') as $transaction)

        <tr>
            <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
            <td>{{ $transaction->reference }}</td>
            <td>{{ $transaction->source_label }}</td>
            <td>{{ $transaction->description ?? '-' }}</td>

            <td class="text-right text-green">
                @if($transaction->isIncoming())
                    {{ number_format($transaction->amount,2) }}
                @endif
            </td>

            <td class="text-right text-red">
                @if($transaction->isOutgoing())
                    {{ number_format($transaction->amount,2) }}
                @endif
            </td>
        </tr>

    @empty

        <tr>
            <td colspan="6" style="text-align:center; padding:20px;">
                Aucune transaction enregistrée
            </td>
        </tr>

    @endforelse

    </tbody>
</table>


{{-- ================= FOOTER ================= --}}
<div class="footer">
    Rapport généré automatiquement par {{ config('app.name') }} —
    Document confidentiel interne
</div>

</body>
</html>