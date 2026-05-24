<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport Caisse — {{ $cashRegister->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            color: #1a1a2e;
            background: #fff;
            padding: 30px;
        }

        /* ── HEADER ── */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 20px;
            border-bottom: 3px solid #4f46e5;
            margin-bottom: 24px;
        }
        .header-left h1 {
            font-size: 22px;
            font-weight: 700;
            color: #4f46e5;
        }
        .header-left p {
            color: #6b7280;
            font-size: 12px;
            margin-top: 4px;
        }
        .header-right {
            text-align: right;
            font-size: 12px;
            color: #6b7280;
        }
        .header-right .company-name {
            font-size: 16px;
            font-weight: 700;
            color: #1a1a2e;
        }
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            margin-top: 6px;
        }
        .badge-open  { background: #dcfce7; color: #15803d; }
        .badge-close { background: #fee2e2; color: #b91c1c; }

        /* ── INFOS CAISSE ── */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        .info-card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 14px 16px;
        }
        .info-card .label { font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; }
        .info-card .value { font-size: 18px; font-weight: 700; margin-top: 4px; }
        .info-card.green  { background: #f0fdf4; border-color: #bbf7d0; }
        .info-card.green  .value { color: #15803d; }
        .info-card.red    { background: #fff1f2; border-color: #fecdd3; }
        .info-card.red    .value { color: #b91c1c; }
        .info-card.indigo { background: #eef2ff; border-color: #c7d2fe; }
        .info-card.indigo .value { color: #4338ca; }

        /* ── TABLE ── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        thead tr {
            background: #4f46e5;
            color: #fff;
        }
        thead th {
            padding: 10px 12px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        tbody tr:nth-child(even) { background: #f9fafb; }
        tbody tr:hover { background: #eef2ff; }
        tbody td {
            padding: 9px 12px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 12px;
        }
        .badge-in  { background: #dcfce7; color: #15803d; padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .badge-out { background: #fee2e2; color: #b91c1c; padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .ref { color: #4f46e5; font-weight: 600; font-size: 11px; }
        .amount { font-weight: 700; }

        /* ── FOOTER ── */
        .footer {
            border-top: 2px solid #e5e7eb;
            padding-top: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            color: #9ca3af;
        }

        @media print {
            body { padding: 15px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    {{-- ── BOUTON IMPRESSION (masqué à l'impression) ── --}}
    <div class="no-print" style="margin-bottom:20px; display:flex; gap:12px;">
        <button onclick="window.print()"
                style="padding:10px 20px; background:#4f46e5; color:#fff; border:none; border-radius:8px; cursor:pointer; font-size:13px; font-weight:600;">
            🖨️ Imprimer
        </button>
        <button onclick="window.close()"
                style="padding:10px 20px; background:#6b7280; color:#fff; border:none; border-radius:8px; cursor:pointer; font-size:13px;">
            ✕ Fermer
        </button>
    </div>

    {{-- ── HEADER ── --}}
    <div class="header">
        <div class="header-left">
            <h1>Rapport de Caisse</h1>
            <p>{{ $cashRegister->name }}</p>
            <span class="badge {{ $cashRegister->isOpen() ? 'badge-open' : 'badge-close' }}">
                {{ $cashRegister->isOpen() ? 'Ouverte' : 'Fermée' }}
            </span>
        </div>
        <div class="header-right">
            <div class="company-name">{{ company()?->name ?? 'MauriERP' }}</div>
            <div style="margin-top:6px;">Imprimé le : {{ now()->format('d/m/Y à H:i') }}</div>
            @if($cashRegister->opened_at)
            <div>Ouverture : {{ \Carbon\Carbon::parse($cashRegister->opened_at)->format('d/m/Y H:i') }}</div>
            @endif
            @if($cashRegister->closed_at)
            <div>Fermeture : {{ \Carbon\Carbon::parse($cashRegister->closed_at)->format('d/m/Y H:i') }}</div>
            @endif
        </div>
    </div>

    {{-- ── STATS ── --}}
    <div class="info-grid">
        <div class="info-card green">
            <div class="label">Total Entrées</div>
            <div class="value">{{ number_format($totalIn, 2, ',', ' ') }} {{ company()?->currency }}</div>
        </div>
        <div class="info-card red">
            <div class="label">Total Sorties</div>
            <div class="value">{{ number_format($totalOut, 2, ',', ' ') }} {{ company()?->currency }}</div>
        </div>
        <div class="info-card indigo">
            <div class="label">Solde Actuel</div>
            <div class="value">{{ number_format($cashRegister->current_balance ?? 0, 2, ',', ' ') }} {{ company()?->currency }}</div>
        </div>
    </div>

    {{-- ── TABLE ── --}}
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Référence</th>
                <th>Type</th>
                <th>Montant</th>
                <th>Source</th>
                <th>Description</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $i => $tx)
            <tr>
                <td style="color:#9ca3af;">{{ $i + 1 }}</td>
                <td class="ref">{{ $tx->reference ?? '-' }}</td>
                <td>
                    @if($tx->type === 'in')
                        <span class="badge-in">Entrée</span>
                    @else
                        <span class="badge-out">Sortie</span>
                    @endif
                </td>
                <td class="amount">{{ number_format($tx->amount, 2, ',', ' ') }} {{ company()?->currency }}</td>
                <td>{{ $tx->source_label ?? $tx->source ?? '-' }}</td>
                <td>{{ $tx->description ?? '-' }}</td>
                <td>{{ $tx->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center; padding:30px; color:#9ca3af;">
                    Aucune transaction enregistrée
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ── FOOTER ── --}}
    <div class="footer">
        <span>{{ company()?->name ?? 'MauriERP' }} — Rapport généré automatiquement</span>
        <span>{{ $transactions->count() }} transaction(s) au total</span>
    </div>

    <script>
        // Auto-print à l'ouverture si souhaité (décommenter)
        // window.onload = () => window.print();
    </script>

</body>
</html>