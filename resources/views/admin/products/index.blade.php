<x-app-layout>

@can('view products')

<div class="space-y-10">

```
{{-- ================= SUCCESS TOAST ================= --}}
@if(session('success'))
    <div x-data="{ show:true }"
         x-show="show"
         x-transition
         x-init="setTimeout(() => show=false, 3500)"
         class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-2xl">
        {{ session('success') }}
    </div>
@endif


{{-- ================= HEADER ================= --}}
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">
            Gestion des Produits
        </h1>
        <p class="text-sm text-gray-500 mt-1">
            {{ $products->total() }} produit(s)
        </p>
    </div>

    @can('create products')
    <a href="{{ route('admin.products.create') }}"
       class="inline-flex items-center gap-2 px-6 py-2.5
              bg-indigo-600 hover:bg-indigo-700
              text-white rounded-2xl shadow-md
              hover:shadow-lg transition hover:scale-105">

        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>

        Nouveau produit
    </a>
    @endcan

</div>


{{-- ================= KPI STOCK ================= --}}
@php
    $rupture = $products->where('stock_status','rupture')->count();
    $faible  = $products->where('stock_status','faible')->count();
    $actifs  = $products->where('is_active',1)->count();
@endphp

<div class="grid md:grid-cols-3 gap-6">

    <div class="bg-red-50 p-6 rounded-2xl shadow-sm">
        <p class="text-xs uppercase text-red-600">Rupture</p>
        <p class="text-2xl font-bold text-red-700 mt-2">
            {{ $rupture }}
        </p>
    </div>

    <div class="bg-yellow-50 p-6 rounded-2xl shadow-sm">
        <p class="text-xs uppercase text-yellow-600">Stock faible</p>
        <p class="text-2xl font-bold text-yellow-700 mt-2">
            {{ $faible }}
        </p>
    </div>

    <div class="bg-green-50 p-6 rounded-2xl shadow-sm">
        <p class="text-xs uppercase text-green-600">Produits actifs</p>
        <p class="text-2xl font-bold text-green-700 mt-2">
            {{ $actifs }}
        </p>
    </div>

</div>


{{-- ================= FILTRES ================= --}}
<div class="bg-white p-6 rounded-3xl shadow-sm border">

    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <input type="text"
               name="search"
               value="{{ request('search') }}"
               placeholder="Nom ou SKU..."
               class="rounded-xl border-gray-300 focus:ring-indigo-500">

        <select name="category"
                class="rounded-xl border-gray-300 focus:ring-indigo-500">
            <option value="">Toutes catégories</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}"
                    {{ request('category')==$category->id?'selected':'' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>

        <select name="status"
                class="rounded-xl border-gray-300 focus:ring-indigo-500">
            <option value="">Statut</option>
            <option value="1" @selected(request('status')==='1')>Actif</option>
            <option value="0" @selected(request('status')==='0')>Inactif</option>
        </select>

        <div class="flex gap-2">
            <button class="flex-1 bg-indigo-600 text-white rounded-xl px-4 py-2">
                Filtrer
            </button>

            <a href="{{ route('admin.products.index') }}"
               class="flex-1 text-center border rounded-xl px-4 py-2">
                Reset
            </a>
        </div>

    </form>

</div>


{{-- ================= TABLE ================= --}}
<div class="bg-white rounded-3xl shadow-sm border overflow-hidden">

    <div class="overflow-x-auto">

        <table class="w-full text-sm">

            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
            <tr>
                <th class="px-6 py-3 text-left">Produit</th>
                <th class="px-6 py-3 text-left">Catégorie</th>
                <th class="px-6 py-3 text-left">Prix</th>
                <th class="px-6 py-3 text-left">Stock</th>
                <th class="px-6 py-3 text-left">Statut</th>
                <th class="px-6 py-3 text-right">Actions</th>
            </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">

            @forelse($products as $product)

                <tr class="hover:bg-indigo-50/30 transition">

                    <td class="px-6 py-4">
                        <div class="flex items-center gap-4">
                            <img src="{{ $product->photo 
                                ? asset('storage/'.$product->photo) 
                                : asset('images/default-product.png') }}"
                                 class="h-12 w-12 rounded-xl object-cover shadow">

                            <div>
                                <div class="font-semibold text-gray-800">
                                    {{ $product->name }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    SKU : {{ $product->sku }}
                                </div>
                            </div>
                        </div>
                    </td>

                    <td class="px-6 py-4">
                        {{ $product->category?->name ?? '-' }}
                    </td>

                    <td class="px-6 py-4 font-semibold text-indigo-600">
                        {{ number_format($product->selling_price,2) }}
                        {{ company()?->currency }}
                    </td>

                    <td class="px-6 py-4">
                        @if($product->stock_status === 'rupture')
                            <span class="badge bg-red-100 text-red-700">Rupture</span>
                        @elseif($product->stock_status === 'faible')
                            <span class="badge bg-yellow-100 text-yellow-700">
                                Faible ({{ $product->stock_quantity }})
                            </span>
                        @else
                            <span class="badge bg-green-100 text-green-700">
                                {{ $product->stock_quantity }}
                            </span>
                        @endif
                    </td>

                    <td class="px-6 py-4">
                        @if($product->is_active)
                            <span class="badge bg-green-100 text-green-700">
                                Actif
                            </span>
                        @else
                            <span class="badge bg-gray-200 text-gray-600">
                                Inactif
                            </span>
                        @endif
                    </td>

                    <td class="px-6 py-4 text-right space-x-3">

                        @can('edit products')
                        <a href="{{ route('admin.products.edit',$product) }}"
                           class="text-indigo-600 hover:text-indigo-800">
                            ✏
                        </a>
                        @endcan

                        @can('delete products')
                        <form action="{{ route('admin.products.destroy',$product) }}"
                              method="POST"
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('Supprimer ce produit ?')"
                                    class="text-red-500 hover:text-red-700">
                                🗑
                            </button>
                        </form>
                        @endcan

                    </td>

                </tr>

            @empty
                <tr>
                    <td colspan="6"
                        class="text-center py-14 text-gray-400">
                        Aucun produit trouvé
                    </td>
                </tr>
            @endforelse

            </tbody>

        </table>

    </div>

</div>


{{-- ================= PAGINATION ================= --}}
<div>
    {{ $products->withQueryString()->links() }}
</div>
```

</div>

@endcan

</x-app-layout>
