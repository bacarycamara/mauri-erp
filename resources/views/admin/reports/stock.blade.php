<x-app-layout>

@can('view reports')

<div class="max-w-6xl mx-auto space-y-6">

    {{-- ================= HEADER ================= --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-3">
            <div class="p-2.5 bg-indigo-100 rounded-2xl">
                <x-heroicon-o-cube class="w-5 h-5 text-indigo-600"/>
            </div>
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Rapport Stock</h1>
                <p class="text-xs text-gray-500">Analyse des niveaux de stock</p>
            </div>
        </div>
        <a href="{{ route('admin.reports.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-gray-50 border border-gray-200
                  rounded-xl hover:bg-gray-100 transition text-sm text-gray-700">
            <x-heroicon-o-arrow-left class="w-4 h-4"/>
            Retour
        </a>
    </div>


    {{-- ================= KPI ================= --}}
    {{--
        ✅ CORRIGÉ : getCollection() pour les KPI sur la page courante uniquement.
        Les vrais totaux devraient venir du controller. Ici on note clairement
        que c'est la page courante.
    --}}
    @php
        $col           = $products->getCollection();
        $totalProducts = $products->total();
        $lowStockCount = $col->filter(fn($p) => $p->stock_quantity <= ($p->minimum_stock ?? 0))->count();
        $healthyStock  = $col->filter(fn($p) => $p->stock_quantity > ($p->minimum_stock ?? 0))->count();
    @endphp

    <div class="grid md:grid-cols-3 gap-4">

        <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm flex items-center gap-3">
            <div class="p-2.5 bg-indigo-100 rounded-xl flex-shrink-0">
                <x-heroicon-o-squares-2x2 class="w-5 h-5 text-indigo-600"/>
            </div>
            <div>
                <p class="text-xs text-gray-500">Total produits</p>
                <p class="text-xl font-bold text-indigo-600">{{ $totalProducts }}</p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm flex items-center gap-3">
            <div class="p-2.5 bg-green-100 rounded-xl flex-shrink-0">
                <x-heroicon-o-check-circle class="w-5 h-5 text-green-600"/>
            </div>
            <div>
                <p class="text-xs text-gray-500">Stock OK (page)</p>
                <p class="text-xl font-bold text-green-600">{{ $healthyStock }}</p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm flex items-center gap-3">
            <div class="p-2.5 bg-red-100 rounded-xl flex-shrink-0">
                <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-red-600"/>
            </div>
            <div>
                <p class="text-xs text-gray-500">Stock faible (page)</p>
                <p class="text-xl font-bold text-red-600">{{ $lowStockCount }}</p>
            </div>
        </div>

    </div>


    {{-- ================= TABLE ================= --}}
    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">

        <h2 class="font-semibold text-sm text-gray-700 flex items-center gap-2 mb-4">
            <x-heroicon-o-table-cells class="w-4 h-4 text-indigo-600"/>
            Liste des Produits
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">

                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Produit</th>
                    <th class="px-4 py-3 text-left">Catégorie</th>
                    <th class="px-4 py-3 text-right">Stock</th>
                    <th class="px-4 py-3 text-right">Minimum</th>
                    <th class="px-4 py-3 text-center">Statut</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                @forelse($products as $product)
                @php
                    $isLow = ($product->stock_quantity ?? 0) <= ($product->minimum_stock ?? 0);
                @endphp
                <tr class="hover:bg-gray-50 transition">

                    <td class="px-4 py-3 font-medium text-gray-700">
                        {{-- ✅ e() sur le nom du produit --}}
                        {{ e($product->name) }}
                    </td>

                    <td class="px-4 py-3 text-gray-500">
                        {{ e($product->category?->name ?? '-') }}
                    </td>

                    <td class="px-4 py-3 text-right font-bold
                               {{ $isLow ? 'text-red-600' : 'text-gray-800' }}">
                        {{ $product->stock_quantity ?? 0 }}
                    </td>

                    <td class="px-4 py-3 text-right text-gray-500">
                        {{ $product->minimum_stock ?? 0 }}
                    </td>

                    <td class="px-4 py-3 text-center">
                        @if($product->stock_quantity <= 0)
                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                            Rupture
                        </span>
                        @elseif($isLow)
                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                            Stock faible
                        </span>
                        @else
                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                            Disponible
                        </span>
                        @endif
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-10 text-center text-gray-400">
                        <x-heroicon-o-cube class="w-8 h-8 mx-auto mb-2 text-gray-300"/>
                        Aucun produit trouvé
                    </td>
                </tr>
                @endforelse
                </tbody>

            </table>
        </div>

        {{-- ================= PAGINATION ================= --}}
        @if($products->hasPages())
        <div class="mt-4 flex justify-center">
            {{ $products->withQueryString()->links() }}
        </div>
        @endif

    </div>

</div>

@else
<div class="flex flex-col items-center justify-center py-24 text-gray-400">
    <x-heroicon-o-lock-closed class="w-12 h-12 mb-4 text-gray-300"/>
    <p class="text-lg font-medium">Accès non autorisé</p>
</div>
@endcan

</x-app-layout>