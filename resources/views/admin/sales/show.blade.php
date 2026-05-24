<x-app-layout>

@can('view sales')

@php
    $currency = company()?->currency ?? '';

    // ✅ Whitelist statut
    $safeStatus = in_array($sale->status, ['draft','confirmed','partial','paid','validated','cancelled'])
        ? $sale->status
        : 'draft';

    $statusColors = [
        'draft'     => 'bg-gray-100 text-gray-700',
        'confirmed' => 'bg-blue-100 text-blue-700',
        'partial'   => 'bg-yellow-100 text-yellow-700',
        'paid'      => 'bg-green-100 text-green-700',
        'validated' => 'bg-indigo-100 text-indigo-700',
        'cancelled' => 'bg-red-100 text-red-700',
    ];

    $statusLabels = [
        'draft'     => 'Brouillon',
        'confirmed' => 'Confirmé',
        'partial'   => 'Partiel',
        'paid'      => 'Payé',
        'validated' => 'Validé',
        'cancelled' => 'Annulé',
    ];

    $paymentLabels = [
        'cash'          => 'Espèces',
        'masrvi'        => 'MASRVI',
        'bankily'       => 'BANKILY',
        'sedad'         => 'SEDAD',
        'click'         => 'CLICK',
        'mobile_money'  => 'Mobile Money',
        'bank_transfer' => 'Virement',
        'check'         => 'Chèque',
        'other'         => 'Autre',
    ];
@endphp

<div class="max-w-6xl mx-auto space-y-8"
     x-data
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0">

    {{-- ================= HEADER ================= --}}
    <div class="flex justify-between items-start flex-wrap gap-4">

        <div>
            <h1 class="text-2xl font-bold text-gray-800">Facture de Vente</h1>
            <p class="text-sm text-gray-500">
                Référence :
                <span class="font-semibold text-indigo-600">{{ $sale->reference }}</span>
            </p>
        </div>

        <div class="flex flex-wrap gap-3">

            <a href="{{ route('admin.sales.index') }}"
               class="px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-100 transition text-sm">
                Retour
            </a>

            @can('edit sales')
            @if($sale->status === 'draft')
            <a href="{{ route('admin.sales.edit', $sale) }}"
               class="px-4 py-2 bg-indigo-100 text-indigo-700 rounded-xl hover:bg-indigo-200 transition text-sm">
                Modifier
            </a>
            @endif
            @endcan

            @can('print sales')
            <a href="{{ route('admin.sales.pdf', $sale) }}"
               target="_blank"
               rel="noopener noreferrer"
               class="px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition text-sm">
                PDF
            </a>
            @endcan

            @can('confirm sales')
            @if($sale->status === 'draft')
            <form action="{{ route('admin.sales.confirm', $sale) }}"
                  method="POST"
                  onsubmit="return confirm('Confirmer cette vente ?')">
                @csrf
                <button type="submit"
                        class="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition text-sm">
                    Confirmer
                </button>
            </form>
            @endif
            @endcan

            @can('create payments')
            @if(in_array($sale->status, ['confirmed','partial']) && ($sale->due_amount ?? 0) > 0)
            <a href="{{ route('admin.payments.create', ['sale_id' => $sale->id]) }}"
               class="px-4 py-2 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition text-sm">
                Paiement
            </a>
            @endif
            @endcan

            @can('cancel sales')
            @if(!in_array($sale->status, ['paid','cancelled']))
            <form action="{{ route('admin.sales.cancel', $sale) }}"
                  method="POST"
                  onsubmit="return confirm('Annuler cette vente ?')">
                @csrf
                <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700 transition text-sm">
                    Annuler
                </button>
            </form>
            @endif
            @endcan

            @can('delete sales')
            @if($sale->status === 'draft')
            <form action="{{ route('admin.sales.destroy', $sale) }}"
                  method="POST"
                  onsubmit="return confirm('Supprimer définitivement cette vente ?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="px-4 py-2 bg-gray-800 text-white rounded-xl hover:bg-black transition text-sm">
                    Supprimer
                </button>
            </form>
            @endif
            @endcan

        </div>
    </div>


    {{-- ================= STATUT ================= --}}
    <div>
        {{-- ✅ Whitelist CSS — pas de $sale->status dans class directement --}}
        <span class="px-4 py-2 rounded-full text-sm font-semibold
                     {{ $statusColors[$safeStatus] ?? 'bg-gray-100 text-gray-700' }}">
            {{ $statusLabels[$safeStatus] ?? ucfirst($safeStatus) }}
        </span>
    </div>


    {{-- ================= INFOS + CLIENT ================= --}}
    <div class="grid md:grid-cols-2 gap-6">

        <div class="bg-white p-6 rounded-2xl shadow space-y-3">
            <h2 class="text-lg font-semibold mb-4 text-indigo-600">Informations Vente</h2>
            <p class="text-sm">
                <strong>Date :</strong>
                {{ $sale->sale_date?->format('d/m/Y') ?? '-' }}
            </p>
            <p class="text-sm">
                <strong>Référence :</strong> {{ $sale->reference }}
            </p>
            <p class="text-sm">
                <strong>Notes :</strong>
                {{-- ✅ e() + nl2br --}}
                {!! nl2br(e($sale->notes ?? '-')) !!}
            </p>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow space-y-3">
            <h2 class="text-lg font-semibold mb-4 text-indigo-600">Client</h2>
            <p class="text-sm">
                <strong>Nom :</strong> {{ e($sale->customer?->name ?? '-') }}
            </p>
            <p class="text-sm">
                <strong>Email :</strong> {{ e($sale->customer?->email ?? '-') }}
            </p>
            <p class="text-sm">
                <strong>Téléphone :</strong> {{ e($sale->customer?->phone ?? '-') }}
            </p>
            <p class="text-sm">
                <strong>Ville :</strong> {{ e($sale->customer?->city ?? '-') }}
            </p>
        </div>

    </div>


    {{-- ================= PRODUITS ================= --}}
    <div class="bg-white rounded-2xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3 text-left">Produit</th>
                    <th class="px-6 py-3 text-left">Qté</th>
                    <th class="px-6 py-3 text-left">Prix unitaire</th>
                    <th class="px-6 py-3 text-left">TVA</th>
                    <th class="px-6 py-3 text-left">Remise</th>
                    <th class="px-6 py-3 text-left">Total</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($sale->items as $item)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 font-semibold">
                        {{ e($item->product?->name ?? '-') }}
                    </td>
                    <td class="px-6 py-4">{{ $item->quantity }}</td>
                    <td class="px-6 py-4">
                        {{ number_format($item->unit_price ?? 0, 2) }} {{ $currency }}
                    </td>
                    <td class="px-6 py-4 text-gray-500">
                        {{ $item->vat_rate ?? 0 }}%
                    </td>
                    <td class="px-6 py-4 text-gray-500">
                        {{ $item->discount_rate ?? 0 }}%
                    </td>
                    <td class="px-6 py-4 font-semibold text-indigo-600">
                        {{ number_format($item->total ?? 0, 2) }} {{ $currency }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-8 text-gray-400">Aucun produit</td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>


    {{-- ================= TOTAUX ================= --}}
    <div class="bg-white p-6 rounded-2xl shadow">
        <div class="flex justify-end">
            <div class="space-y-2 text-sm w-64">
                <div class="flex justify-between text-gray-700">
                    <span>Total</span>
                    <span class="font-bold text-lg">
                        {{ number_format($sale->total_amount ?? 0, 2) }} {{ $currency }}
                    </span>
                </div>
                <div class="flex justify-between text-green-600">
                    <span>Payé</span>
                    <span>{{ number_format($sale->paid_amount ?? 0, 2) }} {{ $currency }}</span>
                </div>
                <div class="flex justify-between text-red-600 font-bold border-t pt-2">
                    <span>Reste à payer</span>
                    <span>{{ number_format($sale->due_amount ?? 0, 2) }} {{ $currency }}</span>
                </div>
            </div>
        </div>
    </div>


    {{-- ================= HISTORIQUE PAIEMENTS ================= --}}
    @if($sale->payments && $sale->payments->count())
    <div class="bg-white p-6 rounded-2xl shadow">
        <h2 class="text-lg font-semibold mb-4 text-indigo-600">Historique des paiements</h2>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                <tr>
                    <th class="px-4 py-2 text-left">Date</th>
                    <th class="px-4 py-2 text-left">Méthode</th>
                    <th class="px-4 py-2 text-left">Montant</th>
                    <th class="px-4 py-2 text-left">Statut</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @foreach($sale->payments as $payment)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-2">
                        {{ $payment->payment_date?->format('d/m/Y') ?? '-' }}
                    </td>
                    <td class="px-4 py-2">
                        {{-- ✅ Whitelist méthode paiement --}}
                        {{ $paymentLabels[$payment->payment_method] ?? e(str_replace('_', ' ', $payment->payment_method)) }}
                    </td>
                    <td class="px-4 py-2 font-semibold">
                        {{ number_format($payment->amount ?? 0, 2) }} {{ $currency }}
                    </td>
                    <td class="px-4 py-2 text-gray-600">
                        {{-- ✅ Label français --}}
                        {{ $statusLabels[$payment->status] ?? ucfirst($payment->status) }}
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

@else
<div class="flex flex-col items-center justify-center py-24 text-gray-400">
    <x-heroicon-o-lock-closed class="w-12 h-12 mb-4 text-gray-300"/>
    <p class="text-lg font-medium">Accès non autorisé</p>
</div>
@endcan

</x-app-layout>