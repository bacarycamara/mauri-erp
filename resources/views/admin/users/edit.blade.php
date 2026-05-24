<x-app-layout>

@can('edit users')

<div class="max-w-3xl mx-auto space-y-8"
     x-data="{ submitting: false }"
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0">

    {{-- ================= HEADER ================= --}}
    <div class="flex items-center gap-4 flex-wrap">
        <div class="p-3 bg-blue-100 rounded-2xl shadow-sm flex-shrink-0">
            <x-heroicon-o-pencil-square class="w-7 h-7 text-blue-600"/>
        </div>
        <div>
            <h1 class="text-3xl font-bold text-gray-800 tracking-tight">
                Modifier utilisateur
            </h1>
            <p class="text-gray-500 text-sm">Mise à jour des informations du compte</p>
        </div>
        <div class="ml-auto flex items-center gap-3">
            {{-- ✅ e() sur le nom du rôle --}}
            <span class="px-3 py-1 text-xs rounded-full bg-indigo-100 text-indigo-700 font-semibold">
                {{ e($user->role_name ?? '-') }}
            </span>
            <a href="{{ route('admin.users.index') }}"
               class="px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-100 transition text-sm">
                Retour
            </a>
        </div>
    </div>


    {{-- ================= ERREURS ================= --}}
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-2xl text-sm">
        <p class="font-semibold mb-1">Veuillez corriger les erreurs :</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif


    {{-- ================= FORM UPDATE ================= --}}
    <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100">

        <form method="POST"
              action="{{ route('admin.users.update', $user) }}"
              @submit.prevent="submitting = true; $el.submit()"
              class="space-y-6">
            @csrf
            @method('PUT')

            @include('admin.users._form', [
                'user'  => $user,
                'roles' => $roles,
            ])

            <div class="flex justify-between items-center pt-6 border-t border-gray-100 flex-wrap gap-4">

                <a href="{{ route('admin.users.index') }}"
                   class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 transition text-sm">
                    <x-heroicon-o-arrow-left class="w-5 h-5"/>
                    Retour
                </a>

                <div class="flex gap-3 flex-wrap">

                    {{-- Bouton supprimer --}}
                    @can('delete users')
                    @if($user->id !== auth()->id())
                    <form method="POST"
                          action="{{ route('admin.users.destroy', $user) }}"
                          onsubmit="return confirm('Supprimer définitivement {{ addslashes(e($user->name)) }} ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl shadow
                                       bg-red-100 text-red-700 hover:bg-red-200 transition text-sm">
                            <x-heroicon-o-trash class="w-4 h-4"/>
                            Supprimer
                        </button>
                    </form>
                    @endif
                    @endcan

                    {{-- Toggle Statut --}}
                    @if($user->id !== auth()->id())
                    <form method="POST"
                          action="{{ route('admin.users.toggle-status', $user) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl shadow transition text-sm
                                       {{ $user->isActive()
                                            ? 'bg-yellow-500 hover:bg-yellow-600 text-white'
                                            : 'bg-green-600 hover:bg-green-700 text-white' }}">
                            <x-heroicon-o-power class="w-4 h-4"/>
                            {{ $user->isActive() ? 'Désactiver' : 'Activer' }}
                        </button>
                    </form>
                    @endif

                    {{-- Sauvegarder --}}
                    <button type="submit"
                            :disabled="submitting"
                            class="inline-flex items-center gap-2 bg-blue-600 text-white
                                   px-6 py-2.5 rounded-xl shadow hover:bg-blue-700 transition text-sm
                                   disabled:opacity-50 disabled:cursor-not-allowed
                                   hover:scale-105 disabled:scale-100">
                        <x-heroicon-o-check class="w-5 h-5"/>
                        <span x-show="!submitting">Mettre à jour</span>
                        <span x-show="submitting" x-cloak>Mise à jour...</span>
                    </button>

                </div>

            </div>

        </form>

    </div>

</div>

@else
<div class="flex flex-col items-center justify-center py-24 text-gray-400">
    <x-heroicon-o-lock-closed class="w-12 h-12 mb-4 text-gray-300"/>
    <p class="text-lg font-medium">Accès non autorisé</p>
</div>
@endcan

</x-app-layout>