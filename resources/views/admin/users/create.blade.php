<x-app-layout>

@can('create users')

<div class="max-w-3xl mx-auto space-y-8"
     x-data="{ submitting: false }"
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0">

    {{-- ================= HEADER ================= --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-indigo-100 rounded-2xl shadow-sm">
                <x-heroicon-o-user-plus class="w-7 h-7 text-indigo-600"/>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-800 tracking-tight">
                    Créer un utilisateur
                </h1>
                <p class="text-gray-500 text-sm">Ajouter un nouveau compte au système ERP</p>
            </div>
        </div>
        <a href="{{ route('admin.users.index') }}"
           class="px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-100 transition text-sm">
            Retour
        </a>
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


    {{-- ================= FORM ================= --}}
    <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100">

        <form method="POST"
              action="{{ route('admin.users.store') }}"
              @submit.prevent="submitting = true; $el.submit()"
              class="space-y-6">
            @csrf

            @include('admin.users._form', ['roles' => $roles])

            <div class="flex justify-between items-center pt-6 border-t border-gray-100 flex-wrap gap-4">

                <a href="{{ route('admin.users.index') }}"
                   class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 transition text-sm">
                    <x-heroicon-o-arrow-left class="w-5 h-5"/>
                    Retour
                </a>

                <button type="submit"
                        :disabled="submitting"
                        class="inline-flex items-center gap-2 bg-indigo-600 text-white
                               px-6 py-2.5 rounded-xl shadow hover:bg-indigo-700 transition text-sm
                               disabled:opacity-50 disabled:cursor-not-allowed disabled:scale-100
                               hover:scale-105">
                    <x-heroicon-o-check class="w-5 h-5"/>
                    <span x-show="!submitting">Enregistrer</span>
                    <span x-show="submitting" x-cloak>Enregistrement...</span>
                </button>

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