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
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-4">
            <div class="p-2.5 bg-red-100 rounded-2xl">
                <x-heroicon-o-shopping-cart class="w-6 h-6 text-red-600"/>
            </div>
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Rapport des Achats</h1>
                <p class="text-xs text-gray-500">
                    Analyse des achats fournisseurs sur la période sélectionnée
                </p>
            </div>
        </div>
        <a href="{{ route('admin.reports.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200
                  bg-white rounded-xl hover:bg-gray-50 transition text-sm text-gray-700">
            <x-heroicon-o-arrow-left class="w-4 h-4"/>
            Retour
        </a>
    </div>


    {{-- ================= FILTRE ================= --}}
    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Du</label>
                <input type="date" name="from" value="{{ $from }}"
                       class="border rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Au</label>
                <input type="date" name="to" value="{{ $to }}"
                       class="border rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-indigo-600 text-white
                           px-5 py-2 rounded-xl hover:bg-indigo-700 transition text-sm">
                <x-heroicon-o-funnel class="w-4 h-4"/>
                Filtrer
            </button>
        </form>
    </div>


    {{-- ================= KPI ================= --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm
                flex justify-between items-center">
        <div>
            <p class="text-sm text-gray-500">Total des Achats</p>
            <p class="text-2xl font-bold text-red-600 mt-1">
                {{ number_format($total ?? 0, 2) }} {{ $currency }}
            </p>
        </div>
        <div class="p-3 bg-red-100 rounded-2xl">
            <x-heroicon-o-shopping-cart class="w-7 h-7 text-red-600"/>
        </div>
    </div>


    {{-- ================= TABLE ================= --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">

                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3 text-left">Référence</th>
                    <th class="px-6 py-3 text-left">Fournisseur</th>
                    <th class="px-6 py-3 text-left">Date</th>
                    <th class="px-6 py-3 text-right">Montant</th>
                    <th class="px-6 py-3 text-left">Statut</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                @forelse($purchases as $purchase)
                @php
                    $safeStatus = array_key_exists($purchase->status, $statusClasses)
                        ? $purchase->status : 'draft';
                @endphp
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 font-semibold text-indigo-600">
                        {{ $purchase->reference }}
                    </td>
                    <td class="px-6 py-4 text-gray-600">
                        {{-- ✅ e() sur le nom fournisseur --}}
                        {{ e($purchase->supplier?->name ?? '-') }}
                    </td>
                    <td class="px-6 py-4 text-gray-600">
                        {{ $purchase->purchase_date?->format('d/m/Y') ?? '-' }}
                    </td>
                    <td class="px-6 py-4 text-right font-semibold text-gray-800">
                        {{ number_format($purchase->total_amount ?? 0, 2) }} {{ $currency }}
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
                    <td colspan="5" class="text-center py-12 text-gray-400">
                        <x-heroicon-o-shopping-cart class="w-8 h-8 mx-auto mb-2 text-gray-300"/>
                        Aucun achat trouvé pour cette période
                    </td>
                </tr>
                @endforelse
                </tbody>

            </table>
        </div>
    </div>


    {{-- ================= PAGINATION ================= --}}
    <div>
        {{ $purchases->withQueryString()->links() }}
    </div>

</div>

@else
<div class="flex flex-col items-center justify-center py-24 text-gray-400">
    <x-heroicon-o-lock-closed class="w-12 h-12 mb-4 text-gray-300"/>
    <p class="text-lg font-medium">Accès non autorisé</p>
</div>
@endcan

</x-app-layout>