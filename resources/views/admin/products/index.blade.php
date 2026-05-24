<x-app-layout>

@can('view products')

@php
    $currency = company()?->currency ?? '';
@endphp

<div class="space-y-6">

    {{-- ================= TOAST ================= --}}
    @if(session('success'))
    <div x-data="{ show: true }"
         x-show="show"
         x-cloak
         x-transition
         x-init="setTimeout(() => show = false, 3500)"
         class="bg-green-50 border border-green-200 text-green-700 px-5 py-3 rounded-2xl text-sm flex items-center gap-2">
        <x-heroicon-o-check-circle class="w-4 h-4 flex-shrink-0"/>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div x-data="{ show: true }"
         x-show="show"
         x-cloak
         x-transition
         x-init="setTimeout(() => show = false, 4000)"
         class="bg-red-50 border border-red-200 text-red-700 px-5 py-3 rounded-2xl text-sm flex items-center gap-2">
        <x-heroicon-o-x-circle class="w-4 h-4 flex-shrink-0"/>
        {{ session('error') }}
    </div>
    @endif


    {{-- ================= HEADER ================= --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gestion des Produits</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $products->total() }} produit(s)</p>
        </div>

        @can('create products')
        <a href="{{ route('admin.products.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700
                  text-white rounded-2xl shadow-md hover:scale-105 transition text-sm">
            <x-heroicon-o-plus class="w-4 h-4"/>
            Nouveau produit
        </a>
        @endcan
    </div>


    {{-- ================= KPI ================= --}}
    {{--
        ✅ CORRIGÉ : $products est paginé — filter() et where() sur collection paginée
        ne donnent que la page courante. Utiliser getCollection() pour être explicite.
    --}}
    @php
        $col     = $products->getCollection();
        $rupture = $col->filter(fn($p) => $p->stock_quantity <= 0)->count();
        $faible  = $col->filter(fn($p) => $p->stock_quantity > 0 && $p->stock_quantity <= $p->minimum_stock)->count();
        $actifs  = $col->where('is_active', true)->count();
    @endphp

    <div class="grid grid-cols-3 gap-4">
        <div class="bg-red-50 p-4 rounded-2xl shadow-sm">
            <p class="text-xs uppercase text-red-600">Rupture</p>
            <p class="text-2xl font-bold text-red-700 mt-1">{{ $rupture }}</p>
        </div>
        <div class="bg-yellow-50 p-4 rounded-2xl shadow-sm">
            <p class="text-xs uppercase text-yellow-600">Stock faible</p>
            <p class="text-2xl font-bold text-yellow-700 mt-1">{{ $faible }}</p>
        </div>
        <div class="bg-green-50 p-4 rounded-2xl shadow-sm">
            <p class="text-xs uppercase text-green-600">Actifs</p>
            <p class="text-2xl font-bold text-green-700 mt-1">{{ $actifs }}</p>
        </div>
    </div>


    {{-- ================= FILTRES ================= --}}
    <div class="bg-white p-4 rounded-2xl shadow-sm border">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">

            <input type="text"
                   name="search"
                   value="{{ e(request('search')) }}"
                   maxlength="100"
                   placeholder="Nom ou SKU..."
                   class="rounded-xl border-gray-300 focus:ring-indigo-500 text-sm">

            <select name="category" class="rounded-xl border-gray-300 focus:ring-indigo-500 text-sm">
                <option value="">Toutes catégories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected(request('category') == $cat->id)>
                    {{ e($cat->name) }}
                </option>
                @endforeach
            </select>

            <select name="status" class="rounded-xl border-gray-300 focus:ring-indigo-500 text-sm">
                <option value="">Statut</option>
                <option value="1" @selected(request('status') === '1')>Actif</option>
                <option value="0" @selected(request('status') === '0')>Inactif</option>
            </select>

            <div class="flex gap-2">
                <button type="submit"
                        class="flex-1 bg-indigo-600 text-white rounded-xl px-4 py-2 text-sm hover:bg-indigo-700 transition">
                    Filtrer
                </button>
                <a href="{{ route('admin.products.index') }}"
                   class="flex-1 text-center border rounded-xl px-4 py-2 text-sm hover:bg-gray-50 transition">
                    Reset
                </a>
            </div>

        </form>
    </div>


    {{-- ================= TABLE ================= --}}
    <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">

                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-5 py-3 text-left">Produit</th>
                    <th class="px-5 py-3 text-left">Catégorie</th>
                    <th class="px-5 py-3 text-left">Prix</th>
                    <th class="px-5 py-3 text-left">Stock</th>
                    <th class="px-5 py-3 text-left">Statut</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                @forelse($products as $product)
                <tr class="hover:bg-indigo-50/30 transition">

                    {{-- PRODUIT --}}
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ $product->photo
                                ? asset('storage/' . $product->photo)
                                : 'https://placehold.co/40x40/e0e7ff/6366f1?text=P' }}"
                                 alt="{{ e($product->name) }}"
                                 class="h-10 w-10 rounded-xl object-cover shadow-sm flex-shrink-0">
                            <div>
                                <div class="font-semibold text-gray-800">
                                    {{ e($product->name) }}
                                </div>
                                <div class="text-xs text-gray-400">
                                    SKU : {{ e($product->sku ?? '-') }}
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- CATÉGORIE --}}
                    <td class="px-5 py-3 text-gray-600">
                        {{ e($product->category?->name ?? '-') }}
                    </td>

                    {{-- PRIX --}}
                    <td class="px-5 py-3 font-semibold text-indigo-600">
                        {{ number_format($product->selling_price ?? 0, 2) }} {{ $currency }}
                    </td>

                    {{-- STOCK --}}
                    <td class="px-5 py-3">
                        @if(($product->stock_quantity ?? 0) <= 0)
                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                            Rupture
                        </span>
                        @elseif($product->stock_quantity <= ($product->minimum_stock ?? 0))
                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                            Faible ({{ $product->stock_quantity }})
                        </span>
                        @else
                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                            {{ $product->stock_quantity }}
                        </span>
                        @endif
                    </td>

                    {{-- STATUT --}}
                    <td class="px-5 py-3">
                        @if($product->is_active)
                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                            Actif
                        </span>
                        @else
                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-600">
                            Inactif
                        </span>
                        @endif
                    </td>

                    {{-- ACTIONS --}}
                    <td class="px-5 py-3">
                        <div class="flex justify-end items-center gap-3">

                            @can('view products')
                            <a href="{{ route('admin.products.show', $product) }}"
                               title="Voir"
                               class="text-blue-600 hover:text-blue-800 transition">
                                <x-heroicon-o-eye class="w-5 h-5"/>
                            </a>
                            @endcan

                            @can('edit products')
                            <a href="{{ route('admin.products.edit', $product) }}"
                               title="Modifier"
                               class="text-indigo-600 hover:text-indigo-800 transition">
                                <x-heroicon-o-pencil-square class="w-5 h-5"/>
                            </a>
                            @endcan

                            @can('delete products')
                            <form action="{{ route('admin.products.destroy', $product) }}"
                                  method="POST"
                                  onsubmit="return confirm('Supprimer ce produit ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        title="Supprimer"
                                        class="text-red-500 hover:text-red-700 transition">
                                    <x-heroicon-o-trash class="w-5 h-5"/>
                                </button>
                            </form>
                            @endcan

                        </div>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-12 text-gray-400">
                        <x-heroicon-o-cube class="w-10 h-10 mx-auto mb-3 text-gray-300"/>
                        Aucun produit trouvé
                    </td>
                </tr>
                @endforelse

                </tbody>
            </table>
        </div>
    </div>


    {{-- ================= PAGINATION ================= --}}
    {{ $products->withQueryString()->links() }}

</div>

@else
<div class="flex flex-col items-center justify-center py-24 text-gray-400">
    <x-heroicon-o-lock-closed class="w-12 h-12 mb-4 text-gray-300"/>
    <p class="text-lg font-medium">Accès non autorisé</p>
</div>
@endcan

</x-app-layout>