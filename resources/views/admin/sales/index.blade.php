<x-app-layout>

@can('view sales')

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

    // Comptage des produits à approvisionner (sur la page courante)
    $productsToOrder = 0;
    foreach($sales as $sale) {
        foreach($sale->items as $item) {
            if ($item->product &&
                $item->product->type === 'physical' &&
                $item->product->stock_quantity < $item->quantity) {
                $productsToOrder++;
                break;
            }
        }
    }
@endphp

<div class="max-w-7xl mx-auto space-y-8">

    {{-- ================= HEADER ================= --}}
    <div class="flex items-center justify-between flex-wrap gap-4">

        <div class="flex items-center gap-4">
            <div class="p-3 bg-indigo-100 rounded-2xl">
                <x-heroicon-o-receipt-percent class="w-5 h-5 text-indigo-600"/>
            </div>
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Ventes</h1>
                <p class="text-xs text-gray-500">Gestion des ventes clients</p>
            </div>
        </div>

        <div class="flex items-center gap-3 flex-wrap">

            @if($productsToOrder > 0)
            <div class="flex items-center gap-2 px-4 py-2 rounded-xl
                        bg-orange-50 border border-orange-200 text-orange-700
                        text-sm font-semibold">
                <x-heroicon-o-exclamation-triangle class="w-4 h-4 flex-shrink-0"/>
                {{ $productsToOrder }} produit(s) à approvisionner
            </div>
            @endif

            @can('create sales')
            <a href="{{ route('admin.sales.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600
                      hover:bg-indigo-700 text-white rounded-xl shadow-md
                      hover:scale-105 transition text-sm">
                <x-heroicon-o-plus class="w-4 h-4"/>
                Nouvelle vente
            </a>
            @endcan

        </div>

    </div>


    {{-- ================= FILTRES ================= --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-sm">
        <form method="GET" class="flex flex-wrap items-center gap-3">

            <input name="search"
                   value="{{ e(request('search')) }}"
                   placeholder="Référence ou client..."
                   maxlength="100"
                   class="rounded-xl border-gray-300 focus:ring-indigo-500 text-sm w-56">

            <select name="status"
                    class="rounded-xl border-gray-300 focus:ring-indigo-500 text-sm w-44">
                <option value="">Statut</option>
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

            <a href="{{ route('admin.sales.index') }}"
               class="px-4 py-2 border border-gray-200 rounded-xl text-sm hover:bg-gray-50 transition">
                Reset
            </a>

        </form>
    </div>


    {{-- ================= LISTE VENTES ================= --}}
    <div class="space-y-3">

        @forelse($sales as $sale)
        @php
            $stockIssue     = false;
            $productToOrder = null;

            foreach($sale->items as $item) {
                if ($item->product &&
                    $item->product->type === 'physical' &&
                    $item->product->stock_quantity < $item->quantity) {
                    $stockIssue     = true;
                    $productToOrder = $item->product;
                    break;
                }
            }

            $saleStatus      = $sale->status ?? 'draft';
            $saleStatusClass = $statusClasses[$saleStatus] ?? 'bg-gray-100 text-gray-600';
            $saleStatusLabel = $statusLabels[$saleStatus]  ?? ucfirst($saleStatus);
        @endphp

        <div class="flex items-center justify-between bg-white border rounded-2xl
                    px-6 py-4 shadow-sm hover:shadow-md hover:-translate-y-0.5
                    transition duration-200
                    {{ $stockIssue ? 'border-red-200 bg-red-50/40' : 'border-gray-200' }}">

            {{-- GAUCHE --}}
            <div class="flex items-center gap-4 w-64 min-w-0">
                <div class="w-10 h-10 flex items-center justify-center bg-indigo-100
                            text-indigo-600 rounded-xl flex-shrink-0">
                    <x-heroicon-o-shopping-bag class="w-5 h-5"/>
                </div>
                <div class="min-w-0">
                    <p class="font-semibold text-gray-900 truncate">{{ $sale->reference }}</p>
                    <p class="text-xs text-gray-500 truncate">
                        {{-- ✅ e() sur le nom client --}}
                        {{ e($sale->customer?->name ?? '-') }}
                    </p>
                </div>
            </div>

            {{-- DATE --}}
            <div class="w-28 text-sm text-gray-600 hidden md:block">
                {{ $sale->sale_date?->format('d/m/Y') ?? '-' }}
            </div>

            {{-- TOTAL --}}
            <div class="w-36 text-sm font-semibold text-indigo-600 hidden md:block">
                {{ number_format($sale->total_amount ?? 0, 2) }} {{ $currency }}
            </div>

            {{-- RESTE DÛ --}}
            <div class="w-32 hidden md:block">
                @if(($sale->due_amount ?? 0) > 0)
                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                    {{ number_format($sale->due_amount, 2) }}
                </span>
                @else
                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                    Soldé
                </span>
                @endif
            </div>

            {{-- STATUT --}}
            <div class="w-24 hidden md:block">
                {{-- ✅ Whitelist CSS --}}
                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $saleStatusClass }}">
                    {{ $saleStatusLabel }}
                </span>
            </div>

            {{-- STOCK --}}
            <div class="w-20 hidden lg:block">
                @if($stockIssue)
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full
                             text-xs font-semibold bg-red-100 text-red-700 animate-pulse">
                    <x-heroicon-o-exclamation-circle class="w-3 h-3"/>
                    Rupture
                </span>
                @else
                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                    OK
                </span>
                @endif
            </div>

            {{-- ACTIONS --}}
            <div class="flex items-center gap-2">

                @can('view sales')
                <a href="{{ route('admin.sales.show', $sale) }}"
                   title="Voir"
                   class="p-2 rounded-xl text-indigo-600 hover:bg-indigo-50 transition">
                    <x-heroicon-o-eye class="w-4 h-4"/>
                </a>
                @endcan

                @can('edit sales')
                @if($sale->status === 'draft')
                <a href="{{ route('admin.sales.edit', $sale) }}"
                   title="Modifier"
                   class="p-2 rounded-xl text-blue-600 hover:bg-blue-50 transition">
                    <x-heroicon-o-pencil-square class="w-4 h-4"/>
                </a>
                @endif
                @endcan

                @can('create purchases')
                @if($stockIssue && $productToOrder && $sale->status === 'draft')
                <a href="{{ route('admin.purchases.create', ['product_id' => $productToOrder->id, 'qty' => 1]) }}"
                   title="Approvisionner"
                   class="p-2 rounded-xl text-orange-600 hover:bg-orange-50 transition">
                    <x-heroicon-o-shopping-cart class="w-4 h-4"/>
                </a>
                @endif
                @endcan

            </div>

        </div>

        @empty
        <div class="text-center py-16 bg-white border border-gray-200 rounded-2xl text-gray-400">
            <x-heroicon-o-receipt-percent class="w-10 h-10 mx-auto mb-3 text-gray-300"/>
            Aucune vente trouvée
        </div>
        @endforelse

    </div>


    {{-- ================= PAGINATION ================= --}}
    <div class="pt-4">
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