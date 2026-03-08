<x-app-layout>

<div class="space-y-8"
     x-data
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">

        <div class="flex items-center gap-3">
            <div class="p-3 bg-indigo-100 rounded-2xl shadow-sm">
                <x-heroicon-o-users class="w-7 h-7 text-indigo-600"/>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-800 tracking-tight">
                    Gestion des utilisateurs
                </h1>
                <p class="text-gray-500 text-sm">
                    Administration des comptes ERP
                </p>
            </div>
        </div>

        @can('create users')
        <a href="{{ route('admin.users.create') }}"
           class="inline-flex items-center gap-2 bg-indigo-600 text-white px-5 py-2.5 rounded-xl shadow hover:bg-indigo-700 hover:scale-105 transition">
            <x-heroicon-o-plus class="w-5 h-5"/>
            Nouveau utilisateur
        </a>
        @endcan

    </div>


    {{-- FILTRES --}}
    <div class="bg-white p-5 rounded-2xl shadow border border-gray-100">

        <form method="GET" class="flex flex-col md:flex-row md:items-center gap-4">

            {{-- Recherche --}}
            <div class="relative w-full md:w-64">
                <x-heroicon-o-magnifying-glass 
                    class="w-4 h-4 absolute left-3 top-3 text-gray-400"/>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Rechercher..."
                       class="w-full pl-9 pr-3 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            {{-- Filtre rôle --}}
            <select name="role"
                    class="rounded-xl border border-gray-200 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                <option value="">Tous les rôles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}"
                        {{ request('role') == $role->name ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>

            {{-- Filtre statut --}}
            <select name="status"
                    class="rounded-xl border border-gray-200 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                <option value="">Tous statuts</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactif</option>
            </select>

            <button class="inline-flex items-center gap-2 bg-gray-800 text-white px-4 py-2 rounded-xl hover:bg-gray-900 transition">
                <x-heroicon-o-funnel class="w-4 h-4"/>
                Filtrer
            </button>

        </form>
    </div>


    {{-- TABLE --}}
    <div class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden">

        <table class="w-full text-sm">

            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="p-4 text-left">Utilisateur</th>
                    <th>Rôle</th>
                    <th>Statut</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">

                @forelse($users as $user)
                <tr class="hover:bg-gray-50 transition">

                    {{-- USER --}}
                    <td class="p-4 flex items-center gap-3">
                        <div class="p-2 bg-indigo-50 rounded-xl">
                            <x-heroicon-o-user class="w-5 h-5 text-indigo-600"/>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">
                                {{ $user->name }}
                            </p>
                            <p class="text-gray-500 text-xs">
                                {{ $user->email }}
                            </p>
                        </div>
                    </td>

                    {{-- ROLE --}}
                    <td>
                        <span class="px-3 py-1 text-xs rounded-full bg-blue-100 text-blue-700 font-semibold">
                            {{ $user->role_name }}
                        </span>
                    </td>

                    {{-- STATUS --}}
                    <td>
                        {!! $user->status_badge !!}
                    </td>

                    {{-- ACTIONS --}}
                    <td class="flex justify-center gap-3 p-4">

                        @can('edit users')
                        <a href="{{ route('admin.users.edit',$user) }}"
                           class="p-2 bg-blue-50 rounded-lg hover:bg-blue-100 transition"
                           title="Modifier">
                            <x-heroicon-o-pencil-square class="w-5 h-5 text-blue-600"/>
                        </a>
                        @endcan

                        @can('edit users')
                        <form method="POST"
                              action="{{ route('admin.users.toggle-status',$user) }}">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="p-2 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition"
                                    title="Activer / Désactiver">
                                <x-heroicon-o-power class="w-5 h-5 text-yellow-600"/>
                            </button>
                        </form>
                        @endcan

                        @can('delete users')
                        <form method="POST"
                              action="{{ route('admin.users.destroy',$user) }}"
                              onsubmit="return confirm('Confirmer la suppression ?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="p-2 bg-red-50 rounded-lg hover:bg-red-100 transition"
                                    title="Supprimer">
                                <x-heroicon-o-trash class="w-5 h-5 text-red-600"/>
                            </button>
                        </form>
                        @endcan

                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center p-10 text-gray-400">
                        <div class="flex flex-col items-center gap-3">
                            <x-heroicon-o-user-group class="w-10 h-10"/>
                            Aucun utilisateur trouvé
                        </div>
                    </td>
                </tr>
                @endforelse

            </tbody>
        </table>

    </div>


    {{-- PAGINATION --}}
    <div>
        {{ $users->withQueryString()->links() }}
    </div>

</div>

</x-app-layout>