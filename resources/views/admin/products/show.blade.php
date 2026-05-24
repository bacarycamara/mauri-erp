<x-app-layout>

@can('view products')

@php
    $currency = company()?->currency ?? '';
    $stockStatus = $product->stock_status;
    $stockLabel = match($stockStatus) {
        'rupture' => 'Rupture de stock',
        'faible'  => 'Stock faible',
        default   => 'En stock',
    };
    $kpiColor = match($stockStatus) {
        'rupture' => ['bg' => 'bg-red-50',    'text' => 'text-red-600',    'val' => 'text-red-700'],
        'faible'  => ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-600', 'val' => 'text-yellow-700'],
        default   => ['bg' => 'bg-green-50',  'text' => 'text-green-600',  'val' => 'text-green-700'],
    };
@endphp

<div class="space-y-6">

    {{-- ================= HEADER ================= --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ e($product->name) }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">SKU : {{ e($product->sku ?? '-') }}</p>
        </div>
        <div class="flex gap-2">
            @can('edit products')
            <a href="{{ route('admin.products.edit', $product) }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700
                      text-white rounded-2xl shadow-md hover:scale-105 transition text-sm">
                <x-heroicon-o-pencil-square class="w-4 h-4"/>
                Modifier
            </a>
            @endcan
            <a href="{{ route('admin.products.index') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 border border-gray-300
                      text-gray-600 rounded-2xl hover:bg-gray-50 transition text-sm">
                <x-heroicon-o-arrow-left class="w-4 h-4"/>
                Retour
            </a>
        </div>
    </div>

    {{-- ================= KPI ================= --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-indigo-50 p-4 rounded-2xl shadow-sm">
            <p class="text-xs uppercase text-indigo-600">Prix de vente</p>
            <p class="text-2xl font-bold text-indigo-700 mt-1">
                {{ number_format($product->selling_price, 2) }} {{ $currency }}
            </p>
        </div>
        <div class="bg-gray-50 p-4 rounded-2xl shadow-sm">
            <p class="text-xs uppercase text-gray-500">Prix d'achat</p>
            <p class="text-2xl font-bold text-gray-700 mt-1">
                {{ number_format($product->purchase_price, 2) }} {{ $currency }}
            </p>
        </div>
        <div class="{{ $kpiColor['bg'] }} p-4 rounded-2xl shadow-sm">
            <p class="text-xs uppercase {{ $kpiColor['text'] }}">{{ $stockLabel }}</p>
            <p class="text-2xl font-bold {{ $kpiColor['val'] }} mt-1">
                {{ $product->stock_quantity }} unités
            </p>
        </div>
    </div>

    {{-- ================= CONTENU PRINCIPAL ================= --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- PHOTO --}}
        <div class="bg-white rounded-2xl shadow-sm border p-6 flex flex-col items-center justify-center">
            <img src="{{ $product->photo ? asset('storage/' . $product->photo) : 'https://placehold.co/200x200/e0e7ff/6366f1?text=P' }}"
                 alt="{{ e($product->name) }}"
                 class="h-48 w-48 rounded-2xl object-cover shadow-md">
            <p class="mt-4 text-sm font-semibold text-gray-700">{{ e($product->name) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ e($product->category?->name ?? '-') }}</p>
            <span class="mt-3 px-3 py-1 rounded-full text-xs font-semibold
                {{ $product->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                {{ $product->is_active ? 'Actif' : 'Inactif' }}
            </span>
        </div>

        {{-- INFORMATIONS GÉNÉRALES --}}
        <div class="md:col-span-2 bg-white rounded-2xl shadow-sm border p-6 space-y-4">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">
                Informations générales
            </h2>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-400 text-xs mb-0.5">Nom</p>
                    <p class="font-medium text-gray-800">{{ e($product->name) }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-xs mb-0.5">SKU</p>
                    <p class="font-medium text-gray-800">{{ e($product->sku ?? '-') }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-xs mb-0.5">Code-barres</p>
                    <p class="font-medium text-gray-800">{{ e($product->barcode ?? '-') }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-xs mb-0.5">Catégorie</p>
                    <p class="font-medium text-gray-800">{{ e($product->category?->name ?? '-') }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-xs mb-0.5">Type</p>
                    <p class="font-medium text-gray-800 capitalize">{{ e($product->type ?? '-') }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-xs mb-0.5">Unité</p>
                    <p class="font-medium text-gray-800">{{ e($product->unit ?? '-') }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-xs mb-0.5">Réf. fournisseur</p>
                    <p class="font-medium text-gray-800">{{ e($product->supplier_reference ?? '-') }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-xs mb-0.5">Créé le</p>
                    <p class="font-medium text-gray-800">
                        {{ $product->created_at->format('d/m/Y à H:i') }}
                    </p>
                </div>
            </div>
            @if($product->description)
            <div class="pt-2 border-t">
                <p class="text-gray-400 text-xs mb-1">Description</p>
                <p class="text-sm text-gray-700">{{ e($product->description) }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- ================= TARIFICATION & STOCK ================= --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- TARIFICATION --}}
        <div class="bg-white rounded-2xl shadow-sm border p-6 space-y-4">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">
                Tarification
            </h2>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-400 text-xs mb-0.5">Prix de vente</p>
                    <p class="font-semibold text-indigo-600 text-lg">
                        {{ number_format($product->selling_price, 2) }} {{ $currency }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-400 text-xs mb-0.5">Prix d'achat</p>
                    <p class="font-semibold text-gray-700 text-lg">
                        {{ number_format($product->purchase_price, 2) }} {{ $currency }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-400 text-xs mb-0.5">Marge bénéficiaire</p>
                    <p class="font-medium text-gray-800">{{ number_format($product->profit_margin, 2) }} %</p>
                </div>
                <div>
                    <p class="text-gray-400 text-xs mb-0.5">Bénéfice unitaire</p>
                    <p class="font-medium text-gray-800">
                        {{ number_format($product->profit, 2) }} {{ $currency }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-400 text-xs mb-0.5">TVA</p>
                    <p class="font-medium text-gray-800">{{ number_format($product->vat_rate, 2) }} %</p>
                </div>
            </div>
        </div>

        {{-- STOCK --}}
        <div class="bg-white rounded-2xl shadow-sm border p-6 space-y-4">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">
                Stock
            </h2>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-400 text-xs mb-0.5">Quantité en stock</p>
                    <p class="font-semibold text-gray-800 text-lg">{{ $product->stock_quantity }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-xs mb-0.5">Stock minimum</p>
                    <p class="font-semibold text-gray-800 text-lg">{{ $product->minimum_stock }}</p>
                </div>
                <div class="col-span-2">
                    <p class="text-gray-400 text-xs mb-1">État du stock</p>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                        @if($stockStatus === 'rupture') bg-red-100 text-red-700
                        @elseif($stockStatus === 'faible') bg-yellow-100 text-yellow-700
                        @else bg-green-100 text-green-700
                        @endif">
                        {{ $stockLabel }}
                    </span>
                </div>
            </div>

            @if($product->minimum_stock > 0)
            @php
                $pct = min(100, ($product->stock_quantity / max($product->minimum_stock, 1)) * 100);
                $barColor = $stockStatus === 'rupture' ? 'bg-red-500' : ($stockStatus === 'faible' ? 'bg-yellow-400' : 'bg-green-500');
            @endphp
            <div>
                <div class="flex justify-between text-xs text-gray-400 mb-1">
                    <span>0</span>
                    <span>Min : {{ $product->minimum_stock }}</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="{{ $barColor }} h-2 rounded-full transition-all"
                         style="width: {{ $pct }}%"></div>
                </div>
            </div>
            @endif
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