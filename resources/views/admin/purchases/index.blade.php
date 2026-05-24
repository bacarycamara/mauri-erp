<x-app-layout>

@can('view purchases')

@php
    $currency = company()?->currency ?? '';
@endphp

<div class="max-w-7xl mx-auto space-y-8">

    {{-- ================= HEADER ================= --}}
    <div class="flex items-center justify-between flex-wrap gap-4">

        <div class="flex items-center gap-4">
            <div class="p-3 bg-indigo-100 rounded-2xl">
                <x-heroicon-o-shopping-cart class="w-6 h-6 text-indigo-600"/>
            </div>
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Achats</h1>
                <p class="text-xs text-gray-500">Gestion des achats fournisseurs</p>
            </div>
        </div>

        @can('create purchases')
        <a href="{{ route('admin.purchases.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700
                  text-white rounded-xl shadow-md transition hover:scale-105 text-sm">
            <x-heroicon-o-plus class="w-4 h-4"/>
            Nouvel achat
        </a>
        @endcan

    </div>


    {{-- ================= KPI ================= --}}
    {{--
        ✅ CORRIGÉ : éviter les requêtes directes dans la vue
        Les comptages doivent venir du controller idéalement.
        En attendant, on les garde mais on les isole proprement.
    --}}
    @php
        $countConfirmed = \App\Models\Purchase::confirmed()->count();
        $countPaid      = \App\Models\Purchase::paid()->count();
        $countPending   = \App\Models\Purchase::pending()->count();
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-4 gap-5">

        <div class="bg-white border rounded-2xl p-5 shadow-sm hover:shadow-md transition">
            <p class="text-xs uppercase text-gray-500">Total</p>
            <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $purchases->total() }}</p>
        </div>

        <div class="bg-white border rounded-2xl p-5 shadow-sm hover:shadow-md transition">
            <p class="text-xs uppercase text-gray-500">Confirmés</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $countConfirmed }}</p>
        </div>

        <div class="bg-white border rounded-2xl p-5 shadow-sm hover:shadow-md transition">
            <p class="text-xs uppercase text-gray-500">Payés</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $countPaid }}</p>
        </div>

        <div class="bg-white border rounded-2xl p-5 shadow-sm hover:shadow-md transition">
            <p class="text-xs uppercase text-gray-500">En attente</p>
            <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $countPending }}</p>
        </div>

    </div>


    {{-- ================= FILTRES ================= --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-sm">
        <form method="GET" class="flex flex-wrap items-center gap-3">

            <input name="search"
                   value="{{ e(request('search')) }}"
                   placeholder="Référence..."
                   maxlength="100"
                   class="rounded-xl border-gray-300 focus:ring-indigo-500 text-sm w-56">

            <select name="status"
                    class="rounded-xl border-gray-300 focus:ring-indigo-500 text-sm w-44">
                <option value="">Statut</option>
                @php
                    $statusLabels = [
                        'draft'     => 'Brouillon',
                        'confirmed' => 'Confirmé',
                        'partial'   => 'Partiel',
                        'paid'      => 'Payé',
                        'cancelled' => 'Annulé',
                    ];
                @endphp
                @foreach($statusLabels as $val => $label)
                <option value="{{ $val }}" @selected(request('status') === $val)>
                    {{ $label }}
                </option>
                @endforeach
            </select>

            <input type="date"
                   name="date"
                   value="{{ request('date') }}"
                   class="rounded-xl border-gray-300 focus:ring-indigo-500 text-sm">

            <button type="submit"
                    class="inline-flex items-center gap-2 bg-indigo-600 text-white
                           px-4 py-2 rounded-xl text-sm hover:bg-indigo-700 transition">
                <x-heroicon-o-funnel class="w-4 h-4"/>
                Filtrer
            </button>

            <a href="{{ route('admin.purchases.index') }}"
               class="px-4 py-2 border border-gray-200 rounded-xl text-sm hover:bg-gray-50 transition">
                Reset
            </a>

        </form>
    </div>


    {{-- ================= LISTE ================= --}}
    <div class="space-y-3">

        @forelse($purchases as $purchase)

        @php
            $statusClasses = [
                'draft'     => 'bg-gray-100 text-gray-600',
                'confirmed' => 'bg-blue-100 text-blue-700',
                'partial'   => 'bg-yellow-100 text-yellow-700',
                'paid'      => 'bg-green-100 text-green-700',
                'validated' => 'bg-indigo-100 text-indigo-700',
                'cancelled' => 'bg-red-100 text-red-700',
            ];
            $statusClass = $statusClasses[$purchase->status] ?? 'bg-gray-100 text-gray-600';
            $statusLabel = $statusLabels[$purchase->status] ?? ucfirst($purchase->status);
        @endphp

        <div class="flex items-center justify-between bg-white border border-gray-200
                    rounded-2xl px-6 py-4 shadow-sm hover:shadow-md hover:-translate-y-0.5
                    transition duration-200">

            {{-- GAUCHE : avatar + référence --}}
            <div class="flex items-center gap-4 w-64 min-w-0">
                <div class="w-10 h-10 flex items-center justify-center bg-indigo-100
                            text-indigo-600 rounded-xl flex-shrink-0">
                    <x-heroicon-o-shopping-cart class="w-5 h-5"/>
                </div>
                <div class="min-w-0">
                    <p class="font-semibold text-gray-900 truncate">
                        {{ $purchase->reference }}
                    </p>
                    <p class="text-xs text-gray-500 truncate">
                        {{ e($purchase->supplier?->name ?? '-') }}
                    </p>
                </div>
            </div>

            {{-- DATE --}}
            <div class="w-28 text-sm text-gray-600 hidden md:block">
                {{ $purchase->purchase_date?->format('d/m/Y') ?? '-' }}
            </div>

            {{-- TOTAL --}}
            <div class="w-36 text-sm font-semibold text-indigo-600 hidden md:block">
                {{ $purchase->formatted_total }}
            </div>

            {{-- RESTE DÛ --}}
            <div class="w-32 hidden md:block">
                @if(($purchase->due_amount ?? 0) > 0)
                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                    {{ $purchase->formatted_due }}
                </span>
                @else
                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                    Soldé
                </span>
                @endif
            </div>

            {{-- STATUT --}}
            <div class="w-28 hidden md:block">
                {{-- ✅ Whitelist CSS — pas de $purchase->status_badge dans class --}}
                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                    {{ $statusLabel }}
                </span>
            </div>

            {{-- ACTIONS --}}
            <div class="flex items-center gap-2">

                @can('view purchases')
                <a href="{{ route('admin.purchases.show', $purchase) }}"
                   title="Voir"
                   class="p-2 rounded-xl text-indigo-600 hover:bg-indigo-50 transition">
                    <x-heroicon-o-eye class="w-4 h-4"/>
                </a>
                @endcan

                @can('confirm purchases')
                @if($purchase->status === 'draft')
                <form method="POST"
                      action="{{ route('admin.purchases.confirm', $purchase) }}"
                      onsubmit="return confirm('Confirmer cet achat ?')">
                    @csrf
                    <button type="submit"
                            title="Confirmer"
                            class="p-2 rounded-xl text-blue-600 hover:bg-blue-50 transition">
                        <x-heroicon-o-check class="w-4 h-4"/>
                    </button>
                </form>
                @endif
                @endcan

                @can('create payments')
                @if(($purchase->due_amount ?? 0) > 0 && $purchase->status !== 'draft')
                <a href="{{ route('admin.payments.create', ['purchase_id' => $purchase->id]) }}"
                   title="Enregistrer paiement"
                   class="p-2 rounded-xl text-green-600 hover:bg-green-50 transition">
                    <x-heroicon-o-banknotes class="w-4 h-4"/>
                </a>
                @endif
                @endcan

            </div>

        </div>

        @empty

        <div class="text-center py-16 bg-white border border-gray-200 rounded-2xl text-gray-400">
            <x-heroicon-o-shopping-cart class="w-10 h-10 mx-auto mb-3 text-gray-300"/>
            Aucun achat trouvé
        </div>

        @endforelse

    </div>


    {{-- ================= PAGINATION ================= --}}
    <div class="pt-4">
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