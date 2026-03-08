<x-app-layout>

@can('view suppliers')

<div class="space-y-8"
     x-data
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0"
>

```
{{-- ================= ALERTS ================= --}}
@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
        {{ session('error') }}
    </div>
@endif


{{-- ================= STATS ================= --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-6">

    <div class="bg-white p-6 rounded-2xl shadow">
        <p class="text-sm text-gray-500">Total fournisseurs</p>
        <p class="text-2xl font-bold text-indigo-600">
            {{ $totalSuppliers }}
        </p>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow">
        <p class="text-sm text-gray-500">Actifs</p>
        <p class="text-2xl font-bold text-green-600">
            {{ $activeSuppliers }}
        </p>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow">
        <p class="text-sm text-gray-500">Avec dette</p>
        <p class="text-2xl font-bold text-red-600">
            {{ $suppliersWithDebt }}
        </p>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow">
        <p class="text-sm text-gray-500">Total dette</p>
        <p class="text-2xl font-bold text-red-600">
            {{ number_format($totalDebtAmount,2) }}
            {{ company()?->currency }}
        </p>
    </div>

</div>


{{-- ================= HEADER ================= --}}
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-800">
            Gestion des Fournisseurs
        </h1>
        <p class="text-sm text-gray-500 mt-1">
            {{ $suppliers->total() }} résultat(s)
        </p>
    </div>

    @can('create suppliers')
    <a href="{{ route('admin.suppliers.create') }}"
       class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700
              text-white rounded-xl shadow-lg hover:scale-105 transition">
        + Nouveau fournisseur
    </a>
    @endcan
</div>


{{-- ================= FILTRES ================= --}}
<div class="bg-white p-6 rounded-2xl shadow">

    <form method="GET"
          class="grid grid-cols-1 md:grid-cols-5 gap-4">

        <input type="text"
               name="search"
               value="{{ request('search') }}"
               placeholder="Rechercher nom, téléphone, NIF..."
               class="rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">

        <select name="status"
                class="rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">Tous statuts</option>
            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Actif</option>
            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactif</option>
        </select>

        <select name="debt"
                class="rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">Toutes dettes</option>
            <option value="yes" {{ request('debt') === 'yes' ? 'selected' : '' }}>Avec dette</option>
            <option value="no" {{ request('debt') === 'no' ? 'selected' : '' }}>Sans dette</option>
        </select>

        <select name="sort"
                class="rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
            <option value="latest">Plus récents</option>
            <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Nom A-Z</option>
            <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Nom Z-A</option>
            <option value="balance_desc" {{ request('sort') === 'balance_desc' ? 'selected' : '' }}>Dette ↓</option>
        </select>

        <div class="flex gap-2">
            <button class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl px-4 py-2">
                Filtrer
            </button>

            <a href="{{ route('admin.suppliers.index') }}"
               class="flex-1 text-center border border-gray-300 rounded-xl px-4 py-2 hover:bg-gray-100">
                Reset
            </a>
        </div>

    </form>

</div>


{{-- ================= TABLE ================= --}}
<div class="bg-white rounded-2xl shadow overflow-hidden">

    <div class="overflow-x-auto">
        <table class="w-full text-sm">

            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
            <tr>
                <th class="px-6 py-3 text-left">Nom</th>
                <th class="px-6 py-3 text-left">Contact</th>
                <th class="px-6 py-3 text-left">Ville</th>
                <th class="px-6 py-3 text-left">Solde</th>
                <th class="px-6 py-3 text-left">Statut</th>
                <th class="px-6 py-3 text-right">Actions</th>
            </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">

            @forelse($suppliers as $supplier)

                <tr class="hover:bg-gray-50 transition">

                    <td class="px-6 py-4 font-semibold text-gray-800">
                        {{ $supplier->name }}
                    </td>

                    <td class="px-6 py-4 text-gray-600">
                        {{ $supplier->phone ?? '-' }}
                    </td>

                    <td class="px-6 py-4">
                        {{ $supplier->city ?? '-' }}
                    </td>

                    <td class="px-6 py-4">
                        @if($supplier->hasDebt())
                            <span class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-semibold">
                                {{ $supplier->formatted_balance }}
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">
                                0.00 {{ company()?->currency }}
                            </span>
                        @endif
                    </td>

                    <td class="px-6 py-4">
                        @if($supplier->is_active)
                            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs">
                                Actif
                            </span>
                        @else
                            <span class="px-3 py-1 bg-gray-200 text-gray-600 rounded-full text-xs">
                                Inactif
                            </span>
                        @endif
                    </td>

                    <td class="px-6 py-4 text-right space-x-3">

                        @can('edit suppliers')
                        <a href="{{ route('admin.suppliers.edit',$supplier) }}"
                           class="text-indigo-600 hover:text-indigo-800">
                            ✏
                        </a>
                        @endcan

                        @can('delete suppliers')
                        <form action="{{ route('admin.suppliers.destroy',$supplier) }}"
                              method="POST"
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('Supprimer ce fournisseur ?')"
                                    class="text-red-500 hover:text-red-700">
                                🗑
                            </button>
                        </form>
                        @endcan

                    </td>

                </tr>

            @empty
                <tr>
                    <td colspan="6" class="text-center py-12 text-gray-500">
                        Aucun fournisseur trouvé
                    </td>
                </tr>
            @endforelse

            </tbody>
        </table>
    </div>

</div>

<div>
    {{ $suppliers->links() }}
</div>
```

</div>

@endcan

</x-app-layout>
