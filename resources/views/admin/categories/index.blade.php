<x-app-layout>

<div class="max-w-7xl mx-auto space-y-8"
     x-data
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0">

    {{-- ================= STATS ================= --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
            <div class="flex justify-between items-center">
                <p class="text-sm text-gray-500">Total catégories</p>
                <x-heroicon-o-folder class="w-5 h-5 text-indigo-500"/>
            </div>
            <p class="text-2xl font-bold text-indigo-600 mt-2">
                {{ $categories->total() }}
            </p>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
            <div class="flex justify-between items-center">
                <p class="text-sm text-gray-500">Catégories actives</p>
                <x-heroicon-o-check-circle class="w-5 h-5 text-green-500"/>
            </div>
            <p class="text-2xl font-bold text-green-600 mt-2">
                {{ $activeCategories }}
            </p>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
            <div class="flex justify-between items-center">
                <p class="text-sm text-gray-500">Produits total</p>
                <x-heroicon-o-cube class="w-5 h-5 text-purple-500"/>
            </div>
            <p class="text-2xl font-bold text-purple-600 mt-2">
                {{ $totalProducts }}
            </p>
        </div>

    </div>


    {{-- ================= HEADER ================= --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">

        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <x-heroicon-o-folder-open class="w-6 h-6 text-indigo-600"/>
                Gestion des Catégories
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $categories->total() }} catégorie(s)
            </p>
        </div>

        <a href="{{ route('admin.categories.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700
                  text-white rounded-xl shadow-lg hover:scale-105 transition">

            <x-heroicon-o-plus class="w-4 h-4"/>
            Nouvelle catégorie
        </a>

    </div>


    {{-- ================= FILTRE ================= --}}
    <div class="bg-white p-6 rounded-2xl shadow">

        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">

            <div class="relative">
                <x-heroicon-o-magnifying-glass class="w-4 h-4 absolute left-3 top-3 text-gray-400"/>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Rechercher..."
                       class="w-full pl-9 rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <select name="status"
                    class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Tous statuts</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Actif</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactif</option>
            </select>

            <button type="submit"
                    class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl px-4 py-2 transition">
                <x-heroicon-o-funnel class="w-4 h-4"/>
                Rechercher
            </button>

            <a href="{{ route('admin.categories.index') }}"
               class="inline-flex items-center justify-center gap-2 border border-gray-300 rounded-xl px-4 py-2 hover:bg-gray-100 transition">
                <x-heroicon-o-arrow-path class="w-4 h-4"/>
                Réinitialiser
            </a>

        </form>

    </div>


    {{-- ================= TABLE ================= --}}
    <div class="bg-white rounded-2xl shadow overflow-hidden">

        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3 text-left">Image</th>
                    <th class="px-6 py-3 text-left">Nom</th>
                    <th class="px-6 py-3 text-left">Produits</th>
                    <th class="px-6 py-3 text-left">Statut</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                @forelse($categories as $category)

                    <tr class="hover:bg-gray-50 transition">

                        {{-- IMAGE --}}
                        <td class="px-6 py-4">
                            <img src="{{ $category->image_url }}"
                                 class="h-12 w-12 rounded-xl object-cover shadow">
                        </td>

                        {{-- NOM --}}
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-800 flex items-center gap-2
                                {{ $category->parent_id ? 'pl-6' : '' }}">
                                <x-heroicon-o-tag class="w-4 h-4 text-gray-400"/>
                                {{ $category->name }}
                            </div>
                            <div class="text-xs text-gray-500">
                                Slug : {{ $category->slug }}
                            </div>
                        </td>

                        {{-- PRODUITS --}}
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold
                                {{ $category->products_count > 0
                                    ? 'bg-indigo-100 text-indigo-700'
                                    : 'bg-gray-100 text-gray-500' }}">
                                <x-heroicon-o-cube class="w-3 h-3"/>
                                {{ $category->products_count }}
                            </span>
                        </td>

                        {{-- STATUT --}}
                        <td class="px-6 py-4">
                            @if($category->is_active)
                                <span class="inline-flex items-center gap-1 px-3 py-1 text-xs rounded-full bg-green-100 text-green-700">
                                    <x-heroicon-o-check-circle class="w-3 h-3"/>
                                    Actif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-3 py-1 text-xs rounded-full bg-gray-200 text-gray-600">
                                    <x-heroicon-o-pause-circle class="w-3 h-3"/>
                                    Inactif
                                </span>
                            @endif
                        </td>

                        {{-- ACTIONS --}}
                        <td class="px-6 py-4 text-right flex justify-end gap-4">

                            <a href="{{ route('admin.categories.edit',$category) }}"
                               class="text-indigo-600 hover:text-indigo-800 transition">
                                <x-heroicon-o-pencil-square class="w-5 h-5"/>
                            </a>

                            @if($category->products_count == 0)
                                <form action="{{ route('admin.categories.destroy',$category) }}"
                                      method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button onclick="return confirm('Supprimer cette catégorie ?')"
                                            class="text-red-500 hover:text-red-700 transition">
                                        <x-heroicon-o-trash class="w-5 h-5"/>
                                    </button>
                                </form>
                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="5" class="text-center py-16 text-gray-500">
                            <x-heroicon-o-folder class="w-10 h-10 mx-auto mb-3 text-gray-300"/>
                            Aucune catégorie trouvée
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>


    {{-- ================= PAGINATION ================= --}}
    <div>
        {{ $categories->withQueryString()->links() }}
    </div>

</div>


{{-- ================= TOAST ================= --}}
@if(session('success'))
<div x-data="{ show: true }"
     x-show="show"
     x-transition
     x-init="setTimeout(() => show = false, 4000)"
     class="fixed top-6 right-6 bg-green-600 text-white px-6 py-4 rounded-2xl shadow-xl flex items-center gap-2">
    <x-heroicon-o-check-circle class="w-5 h-5"/>
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div x-data="{ show: true }"
     x-show="show"
     x-transition
     x-init="setTimeout(() => show = false, 5000)"
     class="fixed top-6 right-6 bg-red-600 text-white px-6 py-4 rounded-2xl shadow-xl flex items-center gap-2">
    <x-heroicon-o-x-circle class="w-5 h-5"/>
    {{ session('error') }}
</div>
@endif

</x-app-layout>