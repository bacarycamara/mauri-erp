<x-app-layout>

@can('view reports')

@php
    $currency    = company()?->currency ?? '';
    $isPositive  = ($profitNet ?? 0) >= 0;
@endphp

<div class="max-w-6xl mx-auto space-y-6">

    {{-- ================= HEADER ================= --}}
    <div class="flex justify-between items-center flex-wrap gap-4">
        <div class="flex items-center gap-3">
            <div class="p-2.5 bg-indigo-100 rounded-2xl">
                <x-heroicon-o-chart-bar class="w-5 h-5 text-indigo-600"/>
            </div>
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Rapport Financier</h1>
                <p class="text-xs text-gray-500">Analyse globale des performances financières</p>
            </div>
        </div>
        <a href="{{ route('admin.reports.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700
                  rounded-xl hover:bg-gray-200 transition text-sm">
            <x-heroicon-o-arrow-left class="w-4 h-4"/>
            Retour
        </a>
    </div>


    {{-- ================= FILTRE ================= --}}
    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm text-gray-600 mb-1">Date début</label>
                <input type="date" name="from" value="{{ $from }}"
                       class="border rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 text-sm w-40">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Date fin</label>
                <input type="date" name="to" value="{{ $to }}"
                       class="border rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 text-sm w-40">
            </div>
            <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 text-white
                           rounded-xl hover:bg-indigo-700 transition text-sm">
                <x-heroicon-o-funnel class="w-4 h-4"/>
                Filtrer
            </button>
        </form>
    </div>


    {{-- ================= KPI ================= --}}
    <div class="grid md:grid-cols-4 gap-4">

        <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-md transition">
            <div class="flex justify-between items-center">
                <span class="text-xs uppercase text-gray-500">Total Ventes</span>
                <x-heroicon-o-receipt-percent class="w-4 h-4 text-green-500"/>
            </div>
            <p class="text-xl font-bold text-green-600 mt-2">
                {{ number_format($salesTotal ?? 0, 2) }} {{ $currency }}
            </p>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-md transition">
            <div class="flex justify-between items-center">
                <span class="text-xs uppercase text-gray-500">Total Achats</span>
                <x-heroicon-o-shopping-cart class="w-4 h-4 text-red-500"/>
            </div>
            <p class="text-xl font-bold text-red-600 mt-2">
                {{ number_format($purchaseTotal ?? 0, 2) }} {{ $currency }}
            </p>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-md transition">
            <div class="flex justify-between items-center">
                <span class="text-xs uppercase text-gray-500">Dépenses</span>
                <x-heroicon-o-banknotes class="w-4 h-4 text-yellow-500"/>
            </div>
            <p class="text-xl font-bold text-yellow-600 mt-2">
                {{ number_format($expenseTotal ?? 0, 2) }} {{ $currency }}
            </p>
        </div>

        {{-- ✅ Whitelist CSS — pas de condition dans class directement --}}
        <div class="rounded-2xl p-5 text-white shadow-md
                    {{ $isPositive
                        ? 'bg-gradient-to-r from-indigo-600 to-indigo-800'
                        : 'bg-gradient-to-r from-red-600 to-red-800' }}">
            <div class="flex justify-between items-center mb-1 opacity-80 text-xs uppercase">
                <span>Bénéfice Net</span>
                @if($isPositive)
                    <x-heroicon-o-arrow-trending-up class="w-4 h-4"/>
                @else
                    <x-heroicon-o-arrow-trending-down class="w-4 h-4"/>
                @endif
            </div>
            <p class="text-xl font-bold mt-1">
                {{ number_format($profitNet ?? 0, 2) }} {{ $currency }}
            </p>
        </div>

    </div>


    {{-- ================= CASH FLOW ================= --}}
    <div class="grid md:grid-cols-2 gap-4">

        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <x-heroicon-o-arrow-trending-up class="w-5 h-5 text-green-600"/>
                <h2 class="font-semibold text-sm text-gray-700">Encaissements</h2>
            </div>
            <p class="text-2xl font-bold text-green-600">
                {{ number_format($totalIn ?? 0, 2) }} {{ $currency }}
            </p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <x-heroicon-o-arrow-trending-down class="w-5 h-5 text-red-600"/>
                <h2 class="font-semibold text-sm text-gray-700">Décaissements</h2>
            </div>
            <p class="text-2xl font-bold text-red-600">
                {{ number_format(($totalOut ?? 0) + ($expenseTotal ?? 0), 2) }} {{ $currency }}
            </p>
        </div>

    </div>


    {{-- ================= RÉSUMÉ ================= --}}
    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">

        <h2 class="font-semibold text-sm text-gray-700 flex items-center gap-2 mb-4">
            <x-heroicon-o-circle-stack class="w-4 h-4 text-indigo-600"/>
            Résumé Financier
        </h2>

        <table class="w-full text-sm">
            <tr class="border-b border-gray-100">
                <td class="py-2 text-gray-600">Ventes</td>
                <td class="py-2 text-right font-semibold text-green-600">
                    {{ number_format($salesTotal ?? 0, 2) }} {{ $currency }}
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <td class="py-2 text-gray-600">Achats</td>
                <td class="py-2 text-right font-semibold text-red-600">
                    - {{ number_format($purchaseTotal ?? 0, 2) }} {{ $currency }}
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <td class="py-2 text-gray-600">Dépenses</td>
                <td class="py-2 text-right font-semibold text-orange-600">
                    - {{ number_format($expenseTotal ?? 0, 2) }} {{ $currency }}
                </td>
            </tr>
            <tr>
                <td class="pt-3 text-base font-bold text-gray-800">Résultat Net</td>
                <td class="pt-3 text-right text-base font-bold
                           {{ $isPositive ? 'text-green-600' : 'text-red-600' }}">
                    {{ number_format($profitNet ?? 0, 2) }} {{ $currency }}
                </td>
            </tr>
        </table>

    </div>


    {{-- ================= GRAPHIQUE ================= --}}
    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">

        <h2 class="font-semibold text-sm text-gray-700 flex items-center gap-2 mb-4">
            <x-heroicon-o-chart-bar class="w-4 h-4 text-indigo-600"/>
            Évolution Mensuelle ({{ now()->year }})
        </h2>

        <canvas id="salesChart" height="80"></canvas>

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
    const monthlyData = @json($monthlySales ?? []);
    const labels = ['Jan','Fév','Mar','Avr','Mai','Juin','Juil','Août','Sep','Oct','Nov','Déc'];
    const data   = labels.map((_, i) => parseFloat(monthlyData[i + 1]) || 0);

    const ctx = document.getElementById('salesChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Ventes',
                data,
                backgroundColor: '#4f46e5',
                borderRadius: 6
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            responsive: true,
            scales: { y: { beginAtZero: true } }
        }
    });
}());
</script>
@endpush

</x-app-layout>