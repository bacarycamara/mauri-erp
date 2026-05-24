<x-app-layout>

@can('view purchases')

@php
    $currency    = company()?->currency ?? '';
    $safeStatus  = in_array($purchase->status, ['draft','confirmed','partial','paid','validated','cancelled'])
        ? $purchase->status
        : 'draft';
    $statusMap   = [
        'draft'     => ['label' => 'Brouillon',  'class' => 'bg-gray-100 text-gray-600'],
        'confirmed' => ['label' => 'Confirmé',   'class' => 'bg-blue-100 text-blue-700'],
        'partial'   => ['label' => 'Partiel',    'class' => 'bg-yellow-100 text-yellow-700'],
        'paid'      => ['label' => 'Payé',       'class' => 'bg-green-100 text-green-700'],
        'validated' => ['label' => 'Validé',     'class' => 'bg-indigo-100 text-indigo-700'],
        'cancelled' => ['label' => 'Annulé',     'class' => 'bg-red-100 text-red-700'],
    ];
    $statusInfo  = $statusMap[$safeStatus];
@endphp

<div class="max-w-7xl mx-auto space-y-8"
     x-data="{ openPayment: false }"
     x-cloak>

    {{-- ================= HEADER ================= --}}
    <div class="bg-gradient-to-r from-indigo-600 to-violet-600 text-white
                rounded-3xl p-8 flex justify-between flex-wrap gap-6 shadow-lg">

        <div class="flex items-center gap-4">
            <div class="p-3 bg-white/20 rounded-2xl">
                <x-heroicon-o-receipt-percent class="w-7 h-7 text-white"/>
            </div>
            <div>
                <h1 class="text-xl font-bold">Achat {{ $purchase->reference }}</h1>
                <p class="text-sm text-indigo-200 mt-1">Gestion et suivi financier de l'achat fournisseur</p>
            </div>
        </div>

        <div class="flex gap-3 flex-wrap items-center">

            <a href="{{ route('admin.purchases.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 border border-white/40
                      text-white rounded-xl hover:bg-white/10 transition text-sm">
                <x-heroicon-o-arrow-left class="w-4 h-4"/>
                Retour
            </a>

            @can('edit purchases')
            @if($purchase->status === 'draft')
            <a href="{{ route('admin.purchases.edit', $purchase) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-white/20
                      text-white rounded-xl hover:bg-white/30 transition text-sm">
                <x-heroicon-o-pencil-square class="w-4 h-4"/>
                Modifier
            </a>
            @endif
            @endcan

            @can('print purchases')
            <a href="{{ route('admin.purchases.pdf', $purchase) }}"
               target="_blank"
               rel="noopener noreferrer"
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-900
                      text-white rounded-xl hover:bg-black transition text-sm">
                <x-heroicon-o-document-arrow-down class="w-4 h-4"/>
                PDF
            </a>
            @endcan

            @can('confirm purchases')
            @if($purchase->status === 'draft')
            <form method="POST"
                  action="{{ route('admin.purchases.confirm', $purchase) }}"
                  onsubmit="return confirm('Confirmer cet achat ?')">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-800
                               text-white rounded-xl hover:bg-indigo-900 transition text-sm">
                    <x-heroicon-o-check-circle class="w-4 h-4"/>
                    Confirmer
                </button>
            </form>
            @endif
            @endcan

            @can('create payments')
            @if(($purchase->due_amount ?? 0) > 0 && $purchase->status !== 'draft')
            <button @click="openPayment = true"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-green-600
                           text-white rounded-xl hover:bg-green-700 transition text-sm">
                <x-heroicon-o-banknotes class="w-4 h-4"/>
                Paiement
            </button>
            @endif
            @endcan

        </div>
    </div>


    {{-- ================= KPI ================= --}}
    <div class="grid md:grid-cols-3 gap-6">

        <div class="bg-white border rounded-2xl p-6 shadow-sm hover:shadow-md transition">
            <p class="text-xs uppercase text-gray-500">Total</p>
            <p class="text-2xl font-bold text-indigo-600 mt-2">{{ $purchase->formatted_total }}</p>
        </div>

        <div class="bg-white border border-green-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition">
            <p class="text-xs uppercase text-gray-500">Payé</p>
            <p class="text-2xl font-bold text-green-600 mt-2">
                {{ number_format($purchase->paid_amount ?? 0, 2) }} {{ $currency }}
            </p>
        </div>

        <div class="bg-white border border-red-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition">
            <p class="text-xs uppercase text-gray-500">Reste à payer</p>
            <p class="text-2xl font-bold text-red-600 mt-2">{{ $purchase->formatted_due }}</p>
        </div>

    </div>


    {{-- ================= INFOS ================= --}}
    <div class="grid lg:grid-cols-2 gap-6">

        {{-- Informations achat --}}
        <div class="bg-white rounded-2xl border p-6 shadow-sm hover:shadow-md transition">
            <h3 class="font-semibold text-gray-700 flex items-center gap-2 mb-4">
                <x-heroicon-o-information-circle class="w-5 h-5 text-indigo-600"/>
                Informations Achat
            </h3>
            <ul class="divide-y divide-gray-100 text-sm">
                <li class="flex justify-between py-2 text-gray-600">
                    <span class="font-medium text-gray-500">Date</span>
                    {{ $purchase->purchase_date?->format('d/m/Y') ?? '-' }}
                </li>
                <li class="flex justify-between py-2 text-gray-600">
                    <span class="font-medium text-gray-500">Référence</span>
                    {{ $purchase->reference }}
                </li>
                <li class="flex justify-between items-center py-2">
                    <span class="font-medium text-gray-500">Statut</span>
                    {{-- ✅ Whitelist statut --}}
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusInfo['class'] }}">
                        {{ $statusInfo['label'] }}
                    </span>
                </li>
                <li class="flex justify-between py-2 text-gray-600">
                    <span class="font-medium text-gray-500">Notes</span>
                    {{ e($purchase->notes ?? '-') }}
                </li>
            </ul>
        </div>

        {{-- Fournisseur --}}
        <div class="bg-white rounded-2xl border p-6 shadow-sm hover:shadow-md transition">
            <h3 class="font-semibold text-gray-700 flex items-center gap-2 mb-4">
                <x-heroicon-o-building-storefront class="w-5 h-5 text-indigo-600"/>
                Fournisseur
            </h3>
            <ul class="divide-y divide-gray-100 text-sm">
                <li class="flex justify-between py-2 text-gray-600">
                    <span class="font-medium text-gray-500">Nom</span>
                    {{ e($purchase->supplier?->name ?? '-') }}
                </li>
                <li class="flex justify-between py-2 text-gray-600">
                    <span class="font-medium text-gray-500">Email</span>
                    {{ e($purchase->supplier?->email ?? '-') }}
                </li>
                <li class="flex justify-between py-2 text-gray-600">
                    <span class="font-medium text-gray-500">Téléphone</span>
                    {{ e($purchase->supplier?->phone ?? '-') }}
                </li>
                <li class="flex justify-between py-2 text-gray-600">
                    <span class="font-medium text-gray-500">Ville</span>
                    {{ e($purchase->supplier?->city ?? '-') }}
                </li>
            </ul>
        </div>

    </div>


    {{-- ================= LIGNES PRODUITS ================= --}}
    <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-5 py-3 text-left">Produit</th>
                    <th class="px-5 py-3 text-center">Qté</th>
                    <th class="px-5 py-3 text-center">Prix unitaire</th>
                    <th class="px-5 py-3 text-center">TVA</th>
                    <th class="px-5 py-3 text-center">Remise</th>
                    <th class="px-5 py-3 text-right">Total</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($purchase->items as $item)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-3 font-semibold text-gray-800">
                        {{ e($item->product?->name ?? '-') }}
                    </td>
                    <td class="px-5 py-3 text-center text-gray-600">{{ $item->quantity }}</td>
                    <td class="px-5 py-3 text-center text-gray-600">
                        {{ number_format($item->unit_price ?? 0, 2) }} {{ $currency }}
                    </td>
                    <td class="px-5 py-3 text-center text-gray-600">
                        {{ $item->vat_rate ?? 0 }}%
                    </td>
                    <td class="px-5 py-3 text-center text-gray-600">
                        {{ $item->discount_rate ?? 0 }}%
                    </td>
                    <td class="px-5 py-3 text-right font-bold text-indigo-600">
                        {{ number_format($item->total ?? 0, 2) }} {{ $currency }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-10 text-gray-400">Aucune ligne</td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>


    {{-- ================= MODAL PAIEMENT ================= --}}
    @can('create payments')
    <div x-show="openPayment"
         x-transition.opacity
         x-cloak
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

        <div @click.away="openPayment = false"
             @click.stop
             class="bg-white rounded-2xl p-8 w-full max-w-md shadow-2xl">

            <h2 class="text-lg font-bold flex items-center gap-2 mb-6">
                <x-heroicon-o-banknotes class="w-5 h-5 text-green-600"/>
                Paiement fournisseur
            </h2>

            <form method="POST"
                  action="{{ route('admin.purchases.pay', $purchase) }}"
                  class="space-y-4">
                @csrf

                <div>
                    <label for="pay_amount" class="block text-sm font-medium text-gray-700 mb-1">
                        Montant <span class="text-red-500">*</span>
                    </label>
                    <input type="number"
                           id="pay_amount"
                           name="amount"
                           value="{{ $purchase->due_amount }}"
                           max="{{ $purchase->due_amount }}"
                           min="0.01"
                           step="0.01"
                           required
                           class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-green-500">
                    <p class="text-xs text-gray-400 mt-1">
                        Maximum : {{ number_format($purchase->due_amount ?? 0, 2) }} {{ $currency }}
                    </p>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button"
                            @click="openPayment = false"
                            class="px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-100 transition text-sm">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-5 py-2 bg-green-600 text-white rounded-xl
                                   hover:bg-green-700 transition text-sm">
                        Valider
                    </button>
                </div>

            </form>

        </div>
    </div>
    @endcan

</div>

@else
<div class="flex flex-col items-center justify-center py-24 text-gray-400">
    <x-heroicon-o-lock-closed class="w-12 h-12 mb-4 text-gray-300"/>
    <p class="text-lg font-medium">Accès non autorisé</p>
</div>
@endcan

</x-app-layout>