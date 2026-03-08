<x-app-layout>

<div class="max-w-7xl mx-auto py-6 space-y-10"
     x-data
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0">

    {{-- ================= HEADER ================= --}}
    <div class="flex justify-between items-center">

        <div class="flex items-center gap-3">
            <x-icon-receipt class="w-8 h-8 text-green-600"/>

            <div>
                <h1 class="text-3xl font-bold text-gray-800">
                    Rapport des Ventes
                </h1>

                <p class="text-sm text-gray-500">
                    Période : {{ $from }} → {{ $to }}
                </p>
            </div>
        </div>

        {{-- BOUTON RETOUR --}}
        <a href="{{ route('admin.reports.index') }}"
           class="flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition">

            <svg xmlns="http://www.w3.org/2000/svg"
                 class="w-4 h-4"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor">

                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M15 19l-7-7 7-7"/>

            </svg>

            Retour
        </a>

    </div>


    {{-- ================= FILTRE DATE ================= --}}
    <div class="bg-white p-6 rounded-2xl shadow border border-gray-100">

        <form method="GET" class="flex flex-wrap gap-6 items-end">

            <div>
                <label class="block text-sm text-gray-600 mb-1">
                    Du
                </label>

                <input type="date"
                       name="from"
                       value="{{ $from }}"
                       class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">
                    Au
                </label>

                <input type="date"
                       name="to"
                       value="{{ $to }}"
                       class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <button
                class="px-5 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition flex items-center gap-2">

                <x-icon-activity class="w-4 h-4"/>

                Filtrer

            </button>

        </form>

    </div>


    {{-- ================= TOTAL ================= --}}
    <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 text-white p-6 rounded-2xl shadow-lg flex items-center justify-between">

        <div>

            <h2 class="text-lg font-semibold opacity-90">
                Chiffre d'affaires total
            </h2>

            <div class="text-3xl font-bold mt-2">
                {{ number_format($total ?? 0,2) }}
                {{ company()?->currency }}
            </div>

        </div>

        <x-icon-chart class="w-10 h-10 opacity-30"/>

    </div>


    {{-- ================= TABLE ================= --}}
    <div class="bg-white rounded-2xl shadow overflow-hidden border border-gray-100">

        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">

                    <tr>

                        <th class="px-6 py-3 text-left">
                            Référence
                        </th>

                        <th class="px-6 py-3 text-left">
                            Client
                        </th>

                        <th class="px-6 py-3 text-left">
                            Date
                        </th>

                        <th class="px-6 py-3 text-right">
                            Montant
                        </th>

                        <th class="px-6 py-3 text-left">
                            Statut
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-gray-100">

                    @forelse($sales as $sale)

                        <tr class="hover:bg-gray-50 transition">

                            <td class="px-6 py-4 font-semibold text-gray-700">
                                {{ $sale->reference }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $sale->customer->name ?? '-' }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $sale->sale_date->format('d/m/Y') }}
                            </td>

                            <td class="px-6 py-4 text-right font-semibold text-indigo-600">
                                {{ number_format($sale->total_amount,2) }}
                                {{ company()?->currency }}
                            </td>

                            <td class="px-6 py-4">

                                @php
                                    $colors = [
                                        'draft' => 'bg-gray-100 text-gray-700',
                                        'confirmed' => 'bg-blue-100 text-blue-700',
                                        'partial' => 'bg-yellow-100 text-yellow-700',
                                        'paid' => 'bg-green-100 text-green-700',
                                        'cancelled' => 'bg-red-100 text-red-700',
                                    ];
                                @endphp

                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $colors[$sale->status] ?? 'bg-gray-100 text-gray-700' }}">

                                    {{ ucfirst($sale->status) }}

                                </span>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="5"
                                class="px-6 py-10 text-center text-gray-500">

                                <div class="flex flex-col items-center gap-3">

                                    <x-icon-database
                                        class="w-8 h-8 text-gray-300"/>

                                    Aucune vente trouvée pour cette période.

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>


    {{-- ================= PAGINATION ================= --}}
    <div>

        {{ $sales->links() }}

    </div>

</div>

</x-app-layout>