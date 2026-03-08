<x-app-layout>

@section('title','Rôles')

<div class="max-w-7xl mx-auto space-y-8"
     x-data="{ loaded:false }"
     x-init="setTimeout(()=>loaded=true,120)"
     x-cloak>

    {{-- ================= HEADER ================= --}}
    <div x-show="loaded"
         x-transition.opacity.duration.500ms
         class="flex justify-between items-center">

        <div class="flex items-center gap-4">
            <div class="p-3 rounded-xl bg-indigo-100">
                <x-heroicon-o-lock-closed class="w-7 h-7 text-indigo-600"/>
            </div>

            <div>
                <h1 class="text-3xl font-bold text-gray-800 tracking-tight">
                    Gestion des Rôles
                </h1>
                <p class="text-sm text-gray-500">
                    Administration des accès et permissions du système
                </p>
            </div>
        </div>

        @can('create roles')
        <a href="{{ route('admin.roles.create') }}"
           class="group bg-gradient-to-r from-indigo-600 to-indigo-700
                  text-white px-6 py-3 rounded-xl shadow-lg
                  hover:shadow-xl hover:scale-105
                  transition flex items-center gap-2">

            <x-heroicon-o-plus class="w-5 h-5 group-hover:rotate-90 transition"/>
            Nouveau rôle
        </a>
        @endcan
    </div>


    {{-- ================= TABLE CARD ================= --}}
    <div x-show="loaded"
         x-transition.scale.duration.400ms
         class="bg-white rounded-3xl shadow-lg overflow-hidden border border-gray-100">

        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                {{-- HEADER --}}
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-6 py-4 text-left">Rôle</th>
                        <th class="px-6 py-4 text-center">Permissions</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                @forelse($roles as $index => $role)

                <tr
                    x-data="{show:false}"
                    x-init="setTimeout(()=>show=true, {{ $index*70 }})"
                    x-show="show"
                    x-transition:enter="transition ease-out duration-400"
                    x-transition:enter-start="opacity-0 translate-y-3"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="hover:bg-gray-50 transition">

                    {{-- ROLE --}}
                    <td class="px-6 py-5">
                        <div class="flex items-center gap-3">

                            <div class="p-2 rounded-lg bg-indigo-50">
                                <x-heroicon-o-lock-closed class="w-5 h-5 text-indigo-500"/>
                            </div>

                            <div class="font-semibold text-gray-800">
                                {{ $role->name }}
                            </div>

                            @if($role->name === 'Super Admin')
                                <span class="px-3 py-1 text-xs rounded-full
                                             bg-indigo-100 text-indigo-700 font-semibold">
                                    Système
                                </span>
                            @endif
                        </div>
                    </td>

                    {{-- PERMISSIONS --}}
                    <td class="px-6 py-5 text-center">
                        <span class="px-4 py-1.5 rounded-full
                                     bg-gray-100 text-gray-700
                                     text-xs font-semibold shadow-sm">
                            {{ $role->permissions_count }}
                        </span>
                    </td>

                    {{-- ACTIONS --}}
                    <td class="px-6 py-5 text-right">

                        <div class="flex justify-end gap-2">

                        @can('edit roles')
                        <a href="{{ route('admin.roles.edit',$role->id) }}"
                           class="inline-flex items-center gap-1 px-4 py-2
                                  text-sm rounded-lg
                                  bg-blue-100 text-blue-700
                                  hover:bg-blue-200 hover:scale-105
                                  transition shadow-sm">

                            <x-heroicon-o-pencil-square class="w-4 h-4"/>
                            Modifier
                        </a>
                        @endcan


                        @can('delete roles')
                        @if($role->name !== 'Super Admin')

                        <form action="{{ route('admin.roles.destroy',$role->id) }}"
                              method="POST"
                              x-data
                              @submit.prevent="
                                if(confirm('Supprimer ce rôle ?')) $el.submit()
                              ">
                            @csrf
                            @method('DELETE')

                            <button
                                class="inline-flex items-center gap-1 px-4 py-2
                                       text-sm rounded-lg
                                       bg-red-100 text-red-700
                                       hover:bg-red-200 hover:scale-105
                                       transition shadow-sm">

                                <x-heroicon-o-trash class="w-4 h-4"/>
                                Supprimer
                            </button>
                        </form>

                        @endif
                        @endcan

                        </div>
                    </td>

                </tr>

                @empty

                {{-- EMPTY STATE --}}
                <tr>
                    <td colspan="3" class="px-6 py-16 text-center text-gray-500">

                        <div class="flex flex-col items-center gap-4 animate-pulse">
                            <x-heroicon-o-circle-stack class="w-10 h-10 text-gray-300"/>
                            <p class="font-medium">Aucun rôle trouvé</p>
                        </div>

                    </td>
                </tr>

                @endforelse

                </tbody>
            </table>

        </div>
    </div>

    {{-- ================= PAGINATION ================= --}}
    <div x-show="loaded"
         x-transition.opacity.duration.700ms>
        {{ $roles->links() }}
    </div>

</div>

</x-app-layout>