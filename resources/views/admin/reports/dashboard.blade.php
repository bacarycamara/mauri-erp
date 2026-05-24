<x-app-layout>

@can('view reports')

@php
    $currency   = company()?->currency ?? '';
    $isPositive = ($profitNet ?? 0) >= 0;
@endphp

<div class="max-w-6xl mx-auto space-y-6">

    {{-- ================= HEADER ================= --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-3">
            <div class="p-2.5 bg-indigo-100 rounded-2xl">
                <x-heroicon-o-presentation-chart-line class="w-5 h-5 text-indigo-600"/>
            </div>
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Dashboard Global ERP</h1>
                <p class="text-xs text-gray-500">Vue stratégique des performances</p>
            </div>
        </div>
        <a href="{{ route('admin.reports.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200
                  bg-gray-50 rounded-xl hover:bg-gray-100 transition text-sm text-gray-700">
            <x-heroicon-o-arrow-left class="w-4 h-4"/>
            Retour
        </a>
    </div>


    {{-- ================= KPI ================= --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">

        <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm">
            <div class="flex justify-between items-center text-xs text-gray-500 mb-2">
                <span>Ventes</span>
                <x-heroicon-o-receipt-percent class="w-4 h-4 text-green-500"/>
            </div>
            <p class="text-base font-bold text-green-600">
                {{ number_format($totalSales ?? 0, 2) }}
                <small class="text-xs text-gray-400 font-normal">{{ $currency }}</small>
            </p>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm">
            <div class="flex justify-between items-center text-xs text-gray-500 mb-2">
                <span>Aujourd'hui</span>
                <x-heroicon-o-bolt class="w-4 h-4 text-indigo-500"/>
            </div>
            <p class="text-base font-bold text-indigo-600">
                {{ number_format($todaySales ?? 0, 2) }}
                <small class="text-xs text-gray-400 font-normal">{{ $currency }}</small>
            </p>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm">
            <div class="flex justify-between items-center text-xs text-gray-500 mb-2">
                <span>Achats</span>
                <x-heroicon-o-shopping-cart class="w-4 h-4 text-red-500"/>
            </div>
            <p class="text-base font-bold text-red-600">
                {{ number_format($totalPurchases ?? 0, 2) }}
                <small class="text-xs text-gray-400 font-normal">{{ $currency }}</small>
            </p>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm">
            <div class="flex justify-between items-center text-xs text-gray-500 mb-2">
                <span>Dépenses</span>
                <x-heroicon-o-banknotes class="w-4 h-4 text-orange-500"/>
            </div>
            <p class="text-base font-bold text-orange-600">
                {{ number_format($totalExpenses ?? 0, 2) }}
                <small class="text-xs text-gray-400 font-normal">{{ $currency }}</small>
            </p>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm">
            <div class="flex justify-between items-center text-xs text-gray-500 mb-2">
                <span>Bénéfice Net</span>
                <x-heroicon-o-chart-bar class="w-4 h-4 text-purple-500"/>
            </div>
            <p class="text-base font-bold {{ $isPositive ? 'text-purple-600' : 'text-red-600' }}">
                {{ number_format($profitNet ?? 0, 2) }}
                <small class="text-xs text-gray-400 font-normal">{{ $currency }}</small>
            </p>
        </div>

    </div>


    {{-- ================= INFOS ERP ================= --}}
    <div class="grid md:grid-cols-2 gap-4">

        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
            <h2 class="font-semibold text-sm text-gray-700 flex items-center gap-2 mb-4">
                <x-heroicon-o-arrows-right-left class="w-4 h-4 text-indigo-600"/>
                Flux Financier
            </h2>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between items-center">
                    <span class="text-gray-500">Encaissements</span>
                    <strong class="text-green-600">
                        {{ number_format($totalIn ?? 0, 2) }} {{ $currency }}
                    </strong>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-500">Décaissements</span>
                    <strong class="text-red-600">
                        {{ number_format($totalOut ?? 0, 2) }} {{ $currency }}
                    </strong>
                </div>
                <div class="flex justify-between items-center border-t pt-2">
                    <span class="text-gray-500 font-medium">Solde net</span>
                    <strong class="{{ ($totalIn ?? 0) >= ($totalOut ?? 0) ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format(($totalIn ?? 0) - ($totalOut ?? 0), 2) }} {{ $currency }}
                    </strong>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
            <h2 class="font-semibold text-sm text-gray-700 flex items-center gap-2 mb-4">
                <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-yellow-500"/>
                Alertes Stock
            </h2>
            <div class="flex items-end gap-2">
                <span class="text-3xl font-bold text-red-600">
                    {{ $lowStockProducts ?? 0 }}
                </span>
                <span class="text-sm text-gray-500 mb-1">
                    produit(s) sous seuil minimum
                </span>
            </div>
            @if(($lowStockProducts ?? 0) > 0)
            <a href="{{ route('admin.reports.stock') }}"
               class="inline-flex items-center gap-1 mt-3 text-xs text-indigo-600 hover:underline">
                <x-heroicon-o-arrow-right class="w-3 h-3"/>
                Voir le rapport stock
            </a>
            @endif
        </div>

    </div>


    {{-- ================= GRAPHIQUE ================= --}}
    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
        <h2 class="font-semibold text-sm text-gray-700 flex items-center gap-2 mb-4">
            <x-heroicon-o-chart-bar class="w-4 h-4 text-indigo-600"/>
            Évolution du Bénéfice ({{ now()->year }})
        </h2>
        <canvas id="profitChart" height="70"></canvas>
    </div>

</div>

@else
<div class="flex flex-col items-center justify-center py-24 text-gray-400">
    <x-heroicon-o-lock-closed class="w-12 h-12 mb-4 text-gray-300"/>
    <p class="text-lg font-medium">Accès non autorisé</p>
</div>
@endcan


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    const monthlyData = @json($monthlyProfit ?? []);
    const labels = ['Jan','Fév','Mar','Avr','Mai','Juin','Juil','Août','Sep','Oct','Nov','Déc'];
    const values = labels.map((_, i) => parseFloat(monthlyData[i + 1]) || 0);

    const ctx = document.getElementById('profitChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                data: values,
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79,70,229,0.08)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 3
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
}());
</script>
@endpush

</x-app-layout>