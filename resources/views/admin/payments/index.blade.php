<x-app-layout>

@can('view payments')

<div x-data="{ loaded:false }"
     x-init="setTimeout(() => loaded = true, 120)"
     class="max-w-7xl mx-auto space-y-8"
     x-cloak>

```
{{-- ================= HEADER ================= --}}
<div x-show="loaded"
     x-transition.opacity.duration.500ms
     class="flex flex-col md:flex-row md:justify-between md:items-start gap-6">

    <div class="flex items-start gap-3">

        <div class="p-3 bg-indigo-100 rounded-2xl">
            <x-heroicon-o-banknotes class="w-6 h-6 text-indigo-600"/>
        </div>

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Gestion des Paiements
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $payments->total() }} paiement(s)
            </p>
        </div>

    </div>

    @can('create payments')
    <a href="{{ route('admin.payments.create') }}"
       class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700
              text-white rounded-xl shadow-lg transition hover:scale-105 active:scale-95">

        <x-heroicon-o-plus class="w-4 h-4"/>
        Nouveau paiement
    </a>
    @endcan

</div>


{{-- ================= KPI SUMMARY ================= --}}
@php
    $totalIn  = $payments->where('type','in')->sum('amount');
    $totalOut = $payments->where('type','out')->sum('amount');
    $net = $totalIn - $totalOut;
@endphp

<div x-show="loaded"
     x-transition.duration.500ms
     class="grid md:grid-cols-3 gap-6">

    <div class="bg-white border rounded-2xl p-6 shadow-sm hover:shadow-md transition">
        <div class="flex justify-between items-center">
            <span class="text-sm text-gray-500">Total Entrées</span>
            <x-heroicon-o-arrow-down-circle class="w-5 h-5 text-green-500"/>
        </div>
        <p class="text-2xl font-bold text-green-600 mt-3">
            {{ number_format($totalIn,2) }} {{ company()?->currency }}
        </p>
    </div>

    <div class="bg-white border rounded-2xl p-6 shadow-sm hover:shadow-md transition">
        <div class="flex justify-between items-center">
            <span class="text-sm text-gray-500">Total Sorties</span>
            <x-heroicon-o-arrow-up-circle class="w-5 h-5 text-red-500"/>
        </div>
        <p class="text-2xl font-bold text-red-600 mt-3">
            {{ number_format($totalOut,2) }} {{ company()?->currency }}
        </p>
    </div>

    <div class="bg-white border rounded-2xl p-6 shadow-sm hover:shadow-md transition">
        <div class="flex justify-between items-center">
            <span class="text-sm text-gray-500">Net</span>
            <x-heroicon-o-scale class="w-5 h-5 text-indigo-500"/>
        </div>
        <p class="text-2xl font-bold mt-3 {{ $net >= 0 ? 'text-indigo-600' : 'text-red-600' }}">
            {{ number_format($net,2) }} {{ company()?->currency }}
        </p>
    </div>

</div>


{{-- ================= FILTRES ================= --}}
<form method="GET"
      x-show="loaded"
      x-transition.duration.500ms
      class="bg-white p-6 rounded-2xl shadow-sm border space-y-6">

    <div class="grid md:grid-cols-7 gap-4">

        <input type="text"
               name="search"
               value="{{ request('search') }}"
               placeholder="Numéro / Référence..."
               class="col-span-2 rounded-xl border-gray-300 focus:ring-indigo-500">

        <select name="type" class="rounded-xl border-gray-300 focus:ring-indigo-500">
            <option value="">Type</option>
            <option value="in" @selected(request('type')=='in')>Entrée</option>
            <option value="out" @selected(request('type')=='out')>Sortie</option>
        </select>

        <select name="status" class="rounded-xl border-gray-300 focus:ring-indigo-500">
            <option value="">Statut</option>
            <option value="confirmed" @selected(request('status')=='confirmed')>Confirmé</option>
            <option value="pending" @selected(request('status')=='pending')>En attente</option>
            <option value="cancelled" @selected(request('status')=='cancelled')>Annulé</option>
        </select>

        <input type="date"
               name="from"
               value="{{ request('from') }}"
               class="rounded-xl border-gray-300 focus:ring-indigo-500">

        <input type="date"
               name="to"
               value="{{ request('to') }}"
               class="rounded-xl border-gray-300 focus:ring-indigo-500">

        <select name="sort" class="rounded-xl border-gray-300 focus:ring-indigo-500">
            <option value="latest" @selected(request('sort')=='latest')>Plus récent</option>
            <option value="oldest" @selected(request('sort')=='oldest')>Plus ancien</option>
            <option value="amount_asc" @selected(request('sort')=='amount_asc')>Montant ↑</option>
            <option value="amount_desc" @selected(request('sort')=='amount_desc')>Montant ↓</option>
        </select>

    </div>

    <div class="flex justify-end gap-3">
        <a href="{{ route('admin.payments.index') }}"
           class="px-5 py-2 border rounded-xl text-gray-600 hover:bg-gray-100 transition">
            Reset
        </a>

        <button type="submit"
                class="px-6 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition">
            Filtrer
        </button>
    </div>

</form>


{{-- ================= TABLE ================= --}}
<div x-show="loaded"
     x-transition.duration.500ms
     class="bg-white rounded-2xl shadow-sm border overflow-hidden">

    <div class="overflow-x-auto">

        <table class="w-full text-sm">

            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
            <tr>
                <th class="px-6 py-3 text-left">Numéro</th>
                <th class="px-6 py-3 text-left">Client / Fournisseur</th>
                <th class="px-6 py-3 text-left">Type</th>
                <th class="px-6 py-3 text-left">Montant</th>
                <th class="px-6 py-3 text-left">Statut</th>
                <th class="px-6 py-3 text-left">Date</th>
                <th class="px-6 py-3 text-right">Actions</th>
            </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">

            @forelse($payments as $payment)

                <tr class="hover:bg-indigo-50/40 transition">

                    <td class="px-6 py-4 font-semibold text-indigo-600">
                        {{ $payment->payment_number }}
                    </td>

                    <td class="px-6 py-4 text-gray-700">
                        {{ $payment->sale?->customer->name
                           ?? $payment->purchase?->supplier->name
                           ?? '-' }}
                    </td>

                    <td class="px-6 py-4">
                        @if($payment->isIncoming())
                            <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-medium">
                                Entrée
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-medium">
                                Sortie
                            </span>
                        @endif
                    </td>

                    <td class="px-6 py-4 font-semibold">
                        {{ $payment->formatted_amount }}
                    </td>

                    <td class="px-6 py-4">
                        {!! $payment->statusBadge !!}
                    </td>

                    <td class="px-6 py-4 text-gray-600">
                        {{ $payment->payment_date?->format('d/m/Y') }}
                    </td>

                    <td class="px-6 py-4 text-right flex justify-end items-center gap-4">

                        @can('view payments')
                        <a href="{{ route('admin.payments.show',$payment) }}"
                           class="text-blue-600 hover:text-blue-800 transition"
                           title="Voir">
                            <x-heroicon-o-eye class="w-5 h-5"/>
                        </a>
                        @endcan

                        @can('print payments')
                        <a href="{{ route('admin.payments.pdf',$payment) }}"
                           class="text-gray-600 hover:text-black transition"
                           title="PDF">
                            <x-heroicon-o-printer class="w-5 h-5"/>
                        </a>

                        <a href="{{ route('admin.payments.printer',$payment) }}"
                           target="_blank"
                           class="text-indigo-600 hover:text-indigo-800 transition"
                           title="Imprimer Ticket">
                            <x-heroicon-o-receipt-percent class="w-5 h-5"/>
                        </a>
                        @endcan

                        @can('cancel payments')
                        @if($payment->canBeCancelled())
                            <form action="{{ route('admin.payments.cancel',$payment) }}"
                                  method="POST"
                                  class="inline">
                                @csrf
                                <button onclick="return confirm('Annuler ce paiement ?')"
                                        class="text-yellow-600 hover:text-yellow-800 transition">
                                    <x-heroicon-o-x-circle class="w-5 h-5"/>
                                </button>
                            </form>
                        @endif
                        @endcan

                    </td>

                </tr>

            @empty
                <tr>
                    <td colspan="7"
                        class="text-center py-16 text-gray-400">
                        <x-heroicon-o-document-text class="w-10 h-10 mx-auto mb-3 text-gray-300"/>
                        Aucun paiement trouvé
                    </td>
                </tr>
            @endforelse

            </tbody>

        </table>

    </div>

</div>


{{-- ================= PAGINATION ================= --}}
<div>
    {{ $payments->withQueryString()->links() }}
</div>
```

</div>

@endcan

</x-app-layout>
