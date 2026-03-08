<x-app-layout>

<div class="max-w-3xl mx-auto space-y-8"
     x-data
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0">

    {{-- HEADER --}}
    <div class="flex items-center gap-4">

        <div class="p-3 bg-blue-100 rounded-2xl shadow-sm">
            <x-heroicon-o-pencil-square class="w-7 h-7 text-blue-600"/>
        </div>

        <div>
            <h1 class="text-3xl font-bold text-gray-800 tracking-tight">
                Modifier utilisateur
            </h1>
            <p class="text-gray-500 text-sm">
                Mise à jour des informations du compte
            </p>
        </div>

        {{-- BADGE ROLE --}}
        <div class="ml-auto">
            <span class="px-3 py-1 text-xs rounded-full bg-indigo-100 text-indigo-700 font-semibold">
                {{ $user->role_name }}
            </span>
        </div>

    </div>


    {{-- CARD FORM --}}
    <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100">

        <form method="POST"
              action="{{ route('admin.users.update', $user) }}"
              class="space-y-6">

            @csrf
            @method('PUT')

            {{-- FORM PARTIAL --}}
            @include('admin.users._form', [
                'user'  => $user,
                'roles' => $roles
            ])

            {{-- ACTIONS --}}
            <div class="flex justify-between items-center pt-6 border-t border-gray-100">

                <a href="{{ route('admin.users.index') }}"
                   class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 transition">
                    <x-heroicon-o-arrow-left class="w-5 h-5"/>
                    Retour
                </a>

                <div class="flex gap-3">

                    {{-- Toggle Status --}}
                    <form method="POST"
                          action="{{ route('admin.users.toggle-status', $user) }}">
                        @csrf
                        @method('PATCH')

                        <button type="submit"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl shadow transition
                                {{ $user->isActive()
                                    ? 'bg-yellow-500 hover:bg-yellow-600 text-white'
                                    : 'bg-green-600 hover:bg-green-700 text-white' }}">

                            <x-heroicon-o-power class="w-5 h-5"/>

                            {{ $user->isActive() ? 'Désactiver' : 'Activer' }}
                        </button>
                    </form>

                    {{-- Save --}}
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-2.5 rounded-xl shadow hover:bg-blue-700 hover:scale-105 transition">
                        <x-heroicon-o-check class="w-5 h-5"/>
                        Mettre à jour
                    </button>

                </div>

            </div>

        </form>

    </div>

</div>

</x-app-layout>