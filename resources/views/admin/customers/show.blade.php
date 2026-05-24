<x-app-layout>

@can('view customers')

@php
    $currency = company()?->currency ?? '';
@endphp

<div class="max-w-7xl mx-auto space-y-8"
     x-data
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0">

    {{-- ================= HEADER ================= --}}
    <div class="flex justify-between items-start flex-wrap gap-4">

        <div class="flex items-start gap-3">
            <div class="p-3 bg-indigo-100 rounded-2xl">
                <x-heroicon-o-user-circle class="w-6 h-6 text-indigo-600"/>
            </div>
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl font-bold text-gray-800">Détail Client</h1>

                    {{-- ✅ Whitelist classes CSS — pas de $customer->status_badge dans class --}}
                    @if($customer->is_active)
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                        Actif
                    </span>
                    @else
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-600">
                        Inactif
                    </span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 mt-1">{{ e($customer->name) }}</p>
            </div>
        </div>

        <div class="flex gap-3 flex-wrap">
            <a href="{{ route('admin.customers.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 border rounded-xl hover:bg-gray-100 transition">
                <x-heroicon-o-arrow-left class="w-4 h-4"/>
                Retour
            </a>

            @can('edit customers')
            <a href="{{ route('admin.customers.edit', $customer) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 shadow-md transition">
                <x-heroicon-o-pencil-square class="w-4 h-4"/>
                Modifier
            </a>
            @endcan
        </div>

    </div>


    {{-- ================= INFORMATIONS + STATS ================= --}}
    <div class="grid md:grid-cols-3 gap-6">

        {{-- INFO CARD --}}
        <div class="bg-white p-6 rounded-2xl shadow space-y-4">
            <h2 class="text-lg font-semibold flex items-center gap-2">
                <x-heroicon-o-identification class="w-5 h-5 text-indigo-600"/>
                Informations
            </h2>
            <div class="space-y-2 text-sm text-gray-700">
                <p><strong>Email :</strong> {{ e($customer->email ?? '-') }}</p>
                <p><strong>Téléphone :</strong> {{ e($customer->phone ?? '-') }}</p>
                <p><strong>Ville :</strong> {{ e($customer->city ?? '-') }}</p>
                <p><strong>Pays :</strong> {{ e($customer->country ?? '-') }}</p>
                <p><strong>NIF :</strong> {{ e($customer->nif ?? '-') }}</p>
                <p><strong>RC :</strong> {{ e($customer->rc ?? '-') }}</p>
                @if($customer->notes)
                <div class="border-t pt-2 mt-2">
                    <p class="text-xs text-gray-400 mb-1 uppercase font-semibold">Notes</p>
                    <p class="text-gray-600">{!! nl2br(e($customer->notes)) !!}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- STATS --}}
        <div class="md:col-span-2 grid grid-cols-2 gap-6">

            <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
                <div class="flex justify-between items-center text-sm text-gray-500">
                    <span>Total Ventes</span>
                    <x-heroicon-o-banknotes class="w-4 h-4 text-indigo-500"/>
                </div>
                <div class="text-2xl font-bold text-indigo-600 mt-2">
                    {{ number_format($stats['total_sales'] ?? 0, 2) }} {{ $currency }}
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
                <div class="flex justify-between items-center text-sm text-gray-500">
                    <span>Total Payé</span>
                    <x-heroicon-o-check-circle class="w-4 h-4 text-green-500"/>
                </div>
                <div class="text-2xl font-bold text-green-600 mt-2">
                    {{ number_format($stats['total_paid'] ?? 0, 2) }} {{ $currency }}
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
                <div class="flex justify-between items-center text-sm text-gray-500">
                    <span>Reste à payer</span>
                    <x-heroicon-o-exclamation-circle class="w-4 h-4 text-red-500"/>
                </div>
                <div class="text-2xl font-bold text-red-600 mt-2">
                    {{ number_format($stats['total_due'] ?? 0, 2) }} {{ $currency }}
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
                <div class="flex justify-between items-center text-sm text-gray-500">
                    <span>Nombre de ventes</span>
                    <x-heroicon-o-clipboard-document-list class="w-4 h-4 text-gray-500"/>
                </div>
                <div class="text-2xl font-bold text-gray-800 mt-2">
                    {{ $stats['sales_count'] ?? 0 }}
                </div>
            </div>

        </div>

    </div>


    {{-- ================= HISTORIQUE VENTES ================= --}}
    <div class="bg-white rounded-2xl shadow overflow-hidden">

        <div class="px-6 py-4 border-b flex items-center gap-2">
            <x-heroicon-o-clock class="w-5 h-5 text-indigo-600"/>
            <h2 class="text-lg font-semibold">Historique des ventes</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">

                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3 text-left">Référence</th>
                    <th class="px-6 py-3 text-left">Date</th>
                    <th class="px-6 py-3 text-left">Total</th>
                    <th class="px-6 py-3 text-left">Payé</th>
                    <th class="px-6 py-3 text-left">Reste</th>
                    <th class="px-6 py-3 text-left">Statut</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                @forelse($customer->sales as $sale)
                <tr class="hover:bg-gray-50 transition">

                    <td class="px-6 py-4 font-semibold text-indigo-600">
                        {{ $sale->reference }}
                    </td>

                    <td class="px-6 py-4 text-gray-600">
                        {{ $sale->sale_date?->format('d/m/Y') ?? '-' }}
                    </td>

                    <td class="px-6 py-4">
                        {{ number_format($sale->total_amount ?? 0, 2) }} {{ $currency }}
                    </td>

                    <td class="px-6 py-4 text-green-600 font-medium">
                        {{ number_format($sale->paid_amount ?? 0, 2) }} {{ $currency }}
                    </td>

                    <td class="px-6 py-4 font-medium
                        {{ ($sale->due_amount ?? 0) > 0 ? 'text-red-600' : 'text-gray-400' }}">
                        {{ number_format($sale->due_amount ?? 0, 2) }} {{ $currency }}
                    </td>

                    <td class="px-6 py-4">
                        {{-- ✅ Whitelist statut — pas de $sale->status_badge dans class --}}
                        @php
                            $statusMap = [
                                'draft'     => 'bg-gray-100 text-gray-600',
                                'confirmed' => 'bg-blue-100 text-blue-700',
                                'partial'   => 'bg-yellow-100 text-yellow-700',
                                'paid'      => 'bg-green-100 text-green-700',
                                'cancelled' => 'bg-red-100 text-red-700',
                                'validated' => 'bg-indigo-100 text-indigo-700',
                            ];
                            $statusClass = $statusMap[$sale->status] ?? 'bg-gray-100 text-gray-600';
                            $statusLabel = match($sale->status) {
                                'draft'     => 'Brouillon',
                                'confirmed' => 'Confirmé',
                                'partial'   => 'Partiel',
                                'paid'      => 'Payé',
                                'cancelled' => 'Annulé',
                                'validated' => 'Validé',
                                default     => ucfirst($sale->status),
                            };
                        @endphp
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </td>

                    <td class="px-6 py-4 text-right">
                        @can('view sales')
                        <a href="{{ route('admin.sales.show', $sale) }}"
                           title="Voir"
                           class="text-indigo-600 hover:text-indigo-800 transition">
                            <x-heroicon-o-eye class="w-5 h-5"/>
                        </a>
                        @endcan
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-16 text-gray-400">
                        <x-heroicon-o-document-text class="w-10 h-10 mx-auto mb-3 text-gray-300"/>
                        Aucune vente pour ce client
                    </td>
                </tr>
                @endforelse

                </tbody>
            </table>
        </div>

    </div>

</div>

@else
<div class="flex flex-col items-center justify-center py-24 text-gray-400">
    <x-heroicon-o-lock-closed class="w-12 h-12 mb-4 text-gray-300"/>
    <p class="text-lg font-medium">Accès non autorisé</p>
</div>
@endcan

</x-app-layout>