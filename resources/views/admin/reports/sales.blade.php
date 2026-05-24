<x-app-layout>

@can('view reports')

@php
    $currency = company()?->currency ?? '';

    $statusClasses = [
        'draft'     => 'bg-gray-100 text-gray-700',
        'confirmed' => 'bg-blue-100 text-blue-700',
        'partial'   => 'bg-yellow-100 text-yellow-700',
        'paid'      => 'bg-green-100 text-green-700',
        'cancelled' => 'bg-red-100 text-red-700',
    ];

    $statusLabels = [
        'draft'     => 'Brouillon',
        'confirmed' => 'Confirmé',
        'partial'   => 'Partiel',
        'paid'      => 'Payé',
        'cancelled' => 'Annulé',
    ];
@endphp

<div class="max-w-7xl mx-auto py-6 space-y-6"
     x-data
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0">

    {{-- ================= HEADER ================= --}}
    <div class="flex justify-between items-center flex-wrap gap-4">
        <div class="flex items-center gap-3">
            <div class="p-2.5 bg-green-100 rounded-2xl">
                <x-heroicon-o-receipt-percent class="w-6 h-6 text-green-600"/>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Rapport des Ventes</h1>
                <p class="text-sm text-gray-500">Période : {{ $from }} → {{ $to }}</p>
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
    <div class="bg-white p-6 rounded-2xl shadow border border-gray-100">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm text-gray-600 mb-1">Du</label>
                <input type="date" name="from" value="{{ $from }}"
                       class="border rounded-xl px-3 py-2 text-sm
                              focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Au</label>
                <input type="date" name="to" value="{{ $to }}"
                       class="border rounded-xl px-3 py-2 text-sm
                              focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 text-white
                           rounded-xl hover:bg-indigo-700 transition text-sm">
                <x-heroicon-o-funnel class="w-4 h-4"/>
                Filtrer
            </button>
        </form>
    </div>


    {{-- ================= TOTAL ================= --}}
    <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 text-white
                p-6 rounded-2xl shadow-lg flex items-center justify-between">
        <div>
            <h2 class="text-sm font-semibold uppercase opacity-80">
                Chiffre d'affaires total
            </h2>
            <div class="text-3xl font-bold mt-2">
                {{ number_format($total ?? 0, 2) }} {{ $currency }}
            </div>
        </div>
        <x-heroicon-o-chart-bar class="w-10 h-10 opacity-30"/>
    </div>


    {{-- ================= TABLE ================= --}}
    <div class="bg-white rounded-2xl shadow overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">

                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3 text-left">Référence</th>
                    <th class="px-6 py-3 text-left">Client</th>
                    <th class="px-6 py-3 text-left">Date</th>
                    <th class="px-6 py-3 text-right">Montant</th>
                    <th class="px-6 py-3 text-left">Statut</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                @forelse($sales as $sale)
                @php
                    $safeStatus = array_key_exists($sale->status, $statusClasses)
                        ? $sale->status : 'draft';
                @endphp
                <tr class="hover:bg-gray-50 transition">

                    <td class="px-6 py-4 font-semibold text-indigo-600">
                        {{ $sale->reference }}
                    </td>

                    <td class="px-6 py-4 text-gray-600">
                        {{-- ✅ e() + optional chaining --}}
                        {{ e($sale->customer?->name ?? '-') }}
                    </td>

                    <td class="px-6 py-4 text-gray-600">
                        {{ $sale->sale_date?->format('d/m/Y') ?? '-' }}
                    </td>

                    <td class="px-6 py-4 text-right font-semibold text-indigo-600">
                        {{ number_format($sale->total_amount ?? 0, 2) }} {{ $currency }}
                    </td>

                    <td class="px-6 py-4">
                        {{-- ✅ Whitelist CSS --}}
                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                                     {{ $statusClasses[$safeStatus] }}">
                            {{ $statusLabels[$safeStatus] ?? ucfirst($safeStatus) }}
                        </span>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-gray-400">
                        <x-heroicon-o-receipt-percent class="w-8 h-8 mx-auto mb-2 text-gray-300"/>
                        Aucune vente trouvée pour cette période.
                    </td>
                </tr>
                @endforelse
                </tbody>

            </table>
        </div>
    </div>


    {{-- ================= PAGINATION ================= --}}
    <div>
        {{ $sales->withQueryString()->links() }}
    </div>

</div>

@else
<div class="flex flex-col items-center justify-center py-24 text-gray-400">
    <x-heroicon-o-lock-closed class="w-12 h-12 mb-4 text-gray-300"/>
    <p class="text-lg font-medium">Accès non autorisé</p>
</div>
@endcan

</x-app-layout>