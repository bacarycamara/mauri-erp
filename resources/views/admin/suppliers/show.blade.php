<x-app-layout>

@can('view suppliers')

@php
    $currency = company()?->currency ?? '';

    $statusLabels = [
        'draft'     => 'Brouillon',
        'confirmed' => 'Confirmé',
        'partial'   => 'Partiel',
        'paid'      => 'Payé',
        'validated' => 'Validé',
        'cancelled' => 'Annulé',
    ];

    $statusClasses = [
        'draft'     => 'bg-gray-100 text-gray-600',
        'confirmed' => 'bg-blue-100 text-blue-700',
        'partial'   => 'bg-yellow-100 text-yellow-700',
        'paid'      => 'bg-green-100 text-green-700',
        'validated' => 'bg-indigo-100 text-indigo-700',
        'cancelled' => 'bg-red-100 text-red-700',
    ];
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
                <x-heroicon-o-building-storefront class="w-6 h-6 text-indigo-600"/>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Détail Fournisseur</h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ e($supplier->name) }}
                </p>
            </div>
        </div>

        <div class="flex gap-3 flex-wrap">

            <a href="{{ route('admin.suppliers.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 border rounded-xl
                      text-gray-600 hover:bg-gray-100 transition text-sm">
                <x-heroicon-o-arrow-left class="w-4 h-4"/>
                Retour
            </a>

            @can('edit suppliers')
            <a href="{{ route('admin.suppliers.edit', $supplier) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white
                      rounded-xl hover:bg-indigo-700 shadow transition text-sm">
                <x-heroicon-o-pencil-square class="w-4 h-4"/>
                Modifier
            </a>
            @endcan

            @can('create purchases')
            <a href="{{ route('admin.purchases.create', ['supplier_id' => $supplier->id]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white
                      rounded-xl hover:bg-green-700 shadow transition text-sm">
                <x-heroicon-o-shopping-cart class="w-4 h-4"/>
                Nouvel achat
            </a>
            @endcan

        </div>
    </div>


    {{-- ================= STATS ================= --}}
    <div class="grid md:grid-cols-4 gap-6">

        <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500">Total Achats</span>
                <x-heroicon-o-banknotes class="w-5 h-5 text-indigo-500"/>
            </div>
            <div class="text-2xl font-bold text-indigo-600 mt-2">
                {{ number_format($stats['total_purchases'] ?? 0, 2) }} {{ $currency }}
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500">Total Payé</span>
                <x-heroicon-o-check-circle class="w-5 h-5 text-green-500"/>
            </div>
            <div class="text-2xl font-bold text-green-600 mt-2">
                {{ number_format($stats['total_paid'] ?? 0, 2) }} {{ $currency }}
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500">Reste à payer</span>
                <x-heroicon-o-exclamation-circle class="w-5 h-5 text-red-500"/>
            </div>
            <div class="text-2xl font-bold text-red-600 mt-2">
                {{ number_format($stats['total_due'] ?? 0, 2) }} {{ $currency }}
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500">Nb Achats</span>
                <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-gray-500"/>
            </div>
            <div class="text-2xl font-bold text-gray-800 mt-2">
                {{ $stats['purchases_count'] ?? 0 }}
            </div>
        </div>

    </div>


    {{-- ================= INFOS ================= --}}
    <div class="grid md:grid-cols-2 gap-6">

        {{-- Informations générales --}}
        <div class="bg-white p-6 rounded-2xl shadow space-y-3">
            <h2 class="text-lg font-semibold text-indigo-600 flex items-center gap-2">
                <x-heroicon-o-information-circle class="w-5 h-5"/>
                Informations
            </h2>
            <div class="space-y-2 text-sm">
                <p><strong class="text-gray-500">Nom :</strong>
                    {{ e($supplier->name) }}
                </p>
                <p><strong class="text-gray-500">Contact :</strong>
                    {{ e($supplier->contact_person ?? '-') }}
                </p>
                <p><strong class="text-gray-500">Email :</strong>
                    {{ e($supplier->email ?? '-') }}
                </p>
                <p><strong class="text-gray-500">Téléphone :</strong>
                    {{ e($supplier->phone ?? '-') }}
                </p>
                <p><strong class="text-gray-500">NIF :</strong>
                    {{ e($supplier->nif ?? '-') }}
                </p>
                <p><strong class="text-gray-500">RC :</strong>
                    {{ e($supplier->rc ?? '-') }}
                </p>
            </div>
        </div>

        {{-- Adresse + Finances --}}
        <div class="bg-white p-6 rounded-2xl shadow space-y-3">
            <h2 class="text-lg font-semibold text-indigo-600 flex items-center gap-2">
                <x-heroicon-o-map-pin class="w-5 h-5"/>
                Adresse & Finances
            </h2>
            <div class="space-y-2 text-sm">
                <p><strong class="text-gray-500">Adresse :</strong>
                    {{ e($supplier->address ?? '-') }}
                </p>
                <p><strong class="text-gray-500">Ville :</strong>
                    {{ e($supplier->city ?? '-') }}
                </p>
                <p><strong class="text-gray-500">Pays :</strong>
                    {{ e($supplier->country ?? '-') }}
                </p>
                <p><strong class="text-gray-500">Solde initial :</strong>
                    {{ number_format($supplier->opening_balance ?? 0, 2) }} {{ $currency }}
                </p>
                <p>
                    <strong class="text-gray-500">Solde actuel :</strong>
                    <span class="{{ ($supplier->current_balance ?? 0) > 0 ? 'text-red-600 font-semibold' : 'text-green-600' }}">
                        {{ number_format($supplier->current_balance ?? 0, 2) }} {{ $currency }}
                    </span>
                </p>
                <p>
                    <strong class="text-gray-500">Statut :</strong>
                    @if($supplier->is_active)
                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Actif</span>
                    @else
                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-600">Inactif</span>
                    @endif
                </p>
            </div>
        </div>

    </div>


    {{-- ================= HISTORIQUE ACHATS ================= --}}
    <div class="bg-white rounded-2xl shadow overflow-hidden">

        <div class="px-6 py-4 border-b flex items-center justify-between">
            <h2 class="text-lg font-semibold flex items-center gap-2">
                <x-heroicon-o-clock class="w-5 h-5 text-indigo-600"/>
                Historique des achats
                <span class="text-sm text-gray-400 font-normal">(10 derniers)</span>
            </h2>
            @can('view purchases')
            <a href="{{ route('admin.purchases.index', ['supplier_id' => $supplier->id]) }}"
               class="text-sm text-indigo-600 hover:underline">
                Voir tous →
            </a>
            @endcan
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

                @forelse($supplier->purchases as $purchase)
                @php
                    $pStatus      = $purchase->status ?? 'draft';
                    $pStatusClass = $statusClasses[$pStatus] ?? 'bg-gray-100 text-gray-600';
                    $pStatusLabel = $statusLabels[$pStatus]  ?? ucfirst($pStatus);
                @endphp
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 font-semibold text-indigo-600">
                        {{ $purchase->reference }}
                    </td>
                    <td class="px-6 py-4 text-gray-600">
                        {{ $purchase->purchase_date?->format('d/m/Y') ?? '-' }}
                    </td>
                    <td class="px-6 py-4">
                        {{ number_format($purchase->total_amount ?? 0, 2) }} {{ $currency }}
                    </td>
                    <td class="px-6 py-4 text-green-600 font-medium">
                        {{ number_format($purchase->paid_amount ?? 0, 2) }} {{ $currency }}
                    </td>
                    <td class="px-6 py-4 font-medium
                        {{ ($purchase->due_amount ?? 0) > 0 ? 'text-red-600' : 'text-gray-400' }}">
                        {{ number_format($purchase->due_amount ?? 0, 2) }} {{ $currency }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $pStatusClass }}">
                            {{ $pStatusLabel }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        @can('view purchases')
                        <a href="{{ route('admin.purchases.show', $purchase) }}"
                           title="Voir"
                           class="text-indigo-600 hover:text-indigo-800 transition">
                            <x-heroicon-o-eye class="w-5 h-5 inline"/>
                        </a>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-12 text-gray-400">
                        <x-heroicon-o-shopping-cart class="w-10 h-10 mx-auto mb-3 text-gray-300"/>
                        Aucun achat pour ce fournisseur
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