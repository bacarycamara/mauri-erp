<x-app-layout>

@can('view users')

<div class="space-y-8"
     x-data
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0">

    {{-- ================= HEADER ================= --}}
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 flex-wrap">

        <div class="flex items-center gap-3">
            <div class="p-3 bg-indigo-100 rounded-2xl shadow-sm">
                <x-heroicon-o-users class="w-7 h-7 text-indigo-600"/>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-800 tracking-tight">
                    Gestion des utilisateurs
                </h1>
                <p class="text-gray-500 text-sm">Administration des comptes ERP</p>
            </div>
        </div>

        @can('create users')
        <a href="{{ route('admin.users.create') }}"
           class="inline-flex items-center gap-2 bg-indigo-600 text-white
                  px-5 py-2.5 rounded-xl shadow hover:bg-indigo-700 hover:scale-105 transition text-sm">
            <x-heroicon-o-plus class="w-5 h-5"/>
            Nouveau utilisateur
        </a>
        @endcan

    </div>


    {{-- ================= FILTRES ================= --}}
    <div class="bg-white p-5 rounded-2xl shadow border border-gray-100">
        <form method="GET" class="flex flex-col md:flex-row md:items-center gap-4 flex-wrap">

            <div class="relative w-full md:w-64">
                <x-heroicon-o-magnifying-glass class="w-4 h-4 absolute left-3 top-3 text-gray-400"/>
                <input type="text"
                       name="search"
                       value="{{ e(request('search')) }}"
                       maxlength="100"
                       placeholder="Rechercher..."
                       class="w-full pl-9 pr-3 py-2 rounded-xl border border-gray-200
                              focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
            </div>

            <select name="role"
                    class="rounded-xl border border-gray-200 px-3 py-2
                           focus:ring-2 focus:ring-indigo-500 text-sm">
                <option value="">Tous les rôles</option>
                @foreach($roles as $role)
                <option value="{{ $role->name }}"
                        @selected(request('role') === $role->name)>
                    {{ e($role->name) }}
                </option>
                @endforeach
            </select>

            <select name="status"
                    class="rounded-xl border border-gray-200 px-3 py-2
                           focus:ring-2 focus:ring-indigo-500 text-sm">
                <option value="">Tous statuts</option>
                <option value="active"   @selected(request('status') === 'active')>Actif</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactif</option>
            </select>

            <button type="submit"
                    class="inline-flex items-center gap-2 bg-gray-800 text-white
                           px-4 py-2 rounded-xl hover:bg-gray-900 transition text-sm">
                <x-heroicon-o-funnel class="w-4 h-4"/>
                Filtrer
            </button>

            @if(request()->hasAny(['search','role','status']))
            <a href="{{ route('admin.users.index') }}"
               class="px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-100 transition text-sm">
                Reset
            </a>
            @endif

        </form>
    </div>


    {{-- ================= TABLE ================= --}}
    <div class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">

                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="p-4 text-left">Utilisateur</th>
                    <th class="p-4 text-left">Rôle</th>
                    <th class="p-4 text-left">Statut</th>
                    <th class="p-4 text-center">Actions</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                @forelse($users as $user)
                <tr class="hover:bg-gray-50 transition">

                    {{-- UTILISATEUR --}}
                    <td class="p-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-indigo-50 rounded-xl flex-shrink-0">
                                <x-heroicon-o-user class="w-5 h-5 text-indigo-600"/>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">
                                    {{-- ✅ e() sur le nom --}}
                                    {{ e($user->name) }}
                                </p>
                                <p class="text-gray-500 text-xs">
                                    {{-- ✅ e() sur l'email --}}
                                    {{ e($user->email) }}
                                </p>
                            </div>
                        </div>
                    </td>

                    {{-- RÔLE --}}
                    <td class="p-4">
                        <span class="px-3 py-1 text-xs rounded-full bg-blue-100 text-blue-700 font-semibold">
                            {{-- ✅ e() sur le nom du rôle --}}
                            {{ e($user->role_name ?? '-') }}
                        </span>
                    </td>

                    {{-- STATUT --}}
                    <td class="p-4">
                        {{-- ✅ Whitelist — pas de $user->status_badge dans {!! !!} --}}
                        @if($user->is_active)
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                            Actif
                        </span>
                        @else
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-600">
                            Inactif
                        </span>
                        @endif
                    </td>

                    {{-- ACTIONS --}}
                    <td class="p-4">
                        <div class="flex justify-center gap-3">

                            @can('edit users')
                            <a href="{{ route('admin.users.edit', $user) }}"
                               class="p-2 bg-blue-50 rounded-lg hover:bg-blue-100 transition"
                               title="Modifier">
                                <x-heroicon-o-pencil-square class="w-5 h-5 text-blue-600"/>
                            </a>
                            @endcan

                            @can('edit users')
                            {{-- ✅ Ne pas permettre à un admin de se désactiver lui-même --}}
                            @if($user->id !== auth()->id())
                            <form method="POST"
                                  action="{{ route('admin.users.toggle-status', $user) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="p-2 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition"
                                        title="{{ $user->is_active ? 'Désactiver' : 'Activer' }}">
                                    <x-heroicon-o-power class="w-5 h-5 text-yellow-600"/>
                                </button>
                            </form>
                            @endif
                            @endcan

                            @can('delete users')
                            @if($user->id !== auth()->id())
                            <form method="POST"
                                  action="{{ route('admin.users.destroy', $user) }}"
                                  onsubmit="return confirm('Confirmer la suppression de {{ addslashes(e($user->name)) }} ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="p-2 bg-red-50 rounded-lg hover:bg-red-100 transition"
                                        title="Supprimer">
                                    <x-heroicon-o-trash class="w-5 h-5 text-red-600"/>
                                </button>
                            </form>
                            @endif
                            @endcan

                        </div>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center p-10 text-gray-400">
                        <div class="flex flex-col items-center gap-3">
                            <x-heroicon-o-user-group class="w-10 h-10 text-gray-300"/>
                            Aucun utilisateur trouvé
                        </div>
                    </td>
                </tr>
                @endforelse

                </tbody>
            </table>
        </div>
    </div>


    {{-- ================= PAGINATION ================= --}}
    <div>
        {{ $users->withQueryString()->links() }}
    </div>

</div>

@else
<div class="flex flex-col items-center justify-center py-24 text-gray-400">
    <x-heroicon-o-lock-closed class="w-12 h-12 mb-4 text-gray-300"/>
    <p class="text-lg font-medium">Accès non autorisé</p>
</div>
@endcan

</x-app-layout>