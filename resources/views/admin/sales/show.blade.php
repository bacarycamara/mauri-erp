<x-app-layout>

@can('view sales')

<div class="max-w-6xl mx-auto space-y-8"
     x-data="{ showPayment:false }"
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0">

```
{{-- ================= HEADER ================= --}}
<div class="flex justify-between items-center">

    <div>
        <h1 class="text-2xl font-bold text-gray-800">
            Facture de Vente
        </h1>
        <p class="text-sm text-gray-500">
            Référence :
            <span class="font-semibold text-indigo-600">
                {{ $sale->reference }}
            </span>
        </p>
    </div>

    <div class="flex flex-wrap gap-3">

        <a href="{{ route('admin.sales.index') }}"
           class="px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-100 transition">
            Retour
        </a>

        {{-- PDF --}}
        @can('print sales')
        <a href="{{ route('admin.sales.pdf',$sale) }}"
           class="px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition">
            PDF
        </a>
        @endcan

        {{-- CONFIRMER --}}
        @can('confirm sales')
        @if($sale->status === 'draft')
            <form action="{{ route('admin.sales.confirm',$sale) }}" method="POST">
                @csrf
                <button onclick="return confirm('Confirmer cette vente ?')"
                        class="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition hover:scale-105">
                     Confirmer
                </button>
            </form>
        @endif
        @endcan

        {{-- PAIEMENT --}}
        @can('create payments')
        @if(in_array($sale->status,['confirmed','partial']) && $sale->due_amount > 0)
            <a href="{{ route('admin.payments.create',['sale_id'=>$sale->id]) }}"
               class="px-4 py-2 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition hover:scale-105">
                 Paiement
            </a>
        @endif
        @endcan

        {{-- ANNULER --}}
        @can('cancel sales')
        @if(!in_array($sale->status,['paid','cancelled']))
            <form action="{{ route('admin.sales.cancel',$sale) }}" method="POST">
                @csrf
                <button onclick="return confirm('Annuler cette vente ?')"
                        class="px-4 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700 transition">
                    Annuler
                </button>
            </form>
        @endif
        @endcan

        {{-- SUPPRIMER --}}
        @can('delete sales')
        @if($sale->status === 'draft')
            <form action="{{ route('admin.sales.destroy',$sale) }}" method="POST">
                @csrf
                @method('DELETE')
                <button onclick="return confirm('Supprimer définitivement ?')"
                        class="px-4 py-2 bg-gray-800 text-white rounded-xl hover:bg-black transition">
                    Supprimer
                </button>
            </form>
        @endif
        @endcan

    </div>
</div>


{{-- ================= STATUS BADGE ================= --}}
@php
    $colors = [
        'draft' => 'bg-gray-100 text-gray-700',
        'confirmed' => 'bg-blue-100 text-blue-700',
        'partial' => 'bg-yellow-100 text-yellow-700',
        'paid' => 'bg-green-100 text-green-700',
        'cancelled' => 'bg-red-100 text-red-700'
    ];
@endphp

<div>
    <span class="px-4 py-2 rounded-full text-sm font-semibold animate-pulse
          {{ $colors[$sale->status] ?? 'bg-gray-100' }}">
        {{ ucfirst($sale->status) }}
    </span>
</div>


{{-- ================= INFOS + CLIENT ================= --}}
<div class="grid md:grid-cols-2 gap-6">

    <div class="bg-white p-6 rounded-2xl shadow space-y-3">
        <h2 class="text-lg font-semibold mb-4 text-indigo-600">
            Informations Vente
        </h2>
        <p><strong>Date :</strong> {{ $sale->sale_date->format('d/m/Y') }}</p>
        <p><strong>Référence :</strong> {{ $sale->reference }}</p>
        <p><strong>Notes :</strong> {{ $sale->notes ?? '-' }}</p>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow space-y-3">
        <h2 class="text-lg font-semibold mb-4 text-indigo-600">
            Client
        </h2>
        <p><strong>Nom :</strong> {{ $sale->customer->name }}</p>
        <p><strong>Email :</strong> {{ $sale->customer->email ?? '-' }}</p>
        <p><strong>Téléphone :</strong> {{ $sale->customer->phone ?? '-' }}</p>
        <p><strong>Ville :</strong> {{ $sale->customer->city ?? '-' }}</p>
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
                <th class="px-6 py-3 text-left">Prix</th>
                <th class="px-6 py-3 text-left">Total</th>
            </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
            @foreach($sale->items as $item)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 font-semibold">
                        {{ $item->product->name }}
                    </td>
                    <td class="px-6 py-4">{{ $item->quantity }}</td>
                    <td class="px-6 py-4">
                        {{ number_format($item->unit_price,2) }}
                        {{ company()?->currency }}
                    </td>
                    <td class="px-6 py-4 font-semibold text-indigo-600">
                        {{ number_format($item->total,2) }}
                        {{ company()?->currency }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>


{{-- ================= TOTALS ================= --}}
<div class="bg-white p-6 rounded-2xl shadow space-y-3">

    <div class="flex justify-between">
        <span>Total</span>
        <span class="font-bold text-lg">
            {{ number_format($sale->total_amount,2) }}
            {{ company()?->currency }}
        </span>
    </div>

    <div class="flex justify-between text-green-600">
        <span>Payé</span>
        <span>
            {{ number_format($sale->paid_amount,2) }}
            {{ company()?->currency }}
        </span>
    </div>

    <div class="flex justify-between text-red-600 text-lg font-bold">
        <span>Reste à payer</span>
        <span>
            {{ number_format($sale->due_amount,2) }}
            {{ company()?->currency }}
        </span>
    </div>

</div>


{{-- ================= HISTORIQUE PAIEMENTS ================= --}}
@if($sale->payments->count())
    <div class="bg-white p-6 rounded-2xl shadow">
        <h2 class="text-lg font-semibold mb-4 text-indigo-600">
            Historique des paiements
        </h2>

        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-600">
            <tr>
                <th class="px-4 py-2 text-left">Date</th>
                <th class="px-4 py-2 text-left">Méthode</th>
                <th class="px-4 py-2 text-left">Montant</th>
                <th class="px-4 py-2 text-left">Statut</th>
            </tr>
            </thead>

            <tbody class="divide-y">
            @foreach($sale->payments as $payment)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-2">
                        {{ $payment->payment_date?->format('d/m/Y') }}
                    </td>
                    <td class="px-4 py-2 capitalize">
                        {{ str_replace('_',' ',$payment->payment_method) }}
                    </td>
                    <td class="px-4 py-2 font-semibold">
                        {{ number_format($payment->amount,2) }}
                        {{ company()?->currency }}
                    </td>
                    <td class="px-4 py-2">
                        {{ ucfirst($payment->status) }}
                    </td>
                </tr>
            @endforeach
            </tbody>

        </table>
    </div>
@endif
```

</div>

@endcan

</x-app-layout>
