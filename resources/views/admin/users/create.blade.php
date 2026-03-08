<x-app-layout>

<div class="max-w-3xl mx-auto space-y-8"
     x-data
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0">

    {{-- HEADER --}}
    <div class="flex items-center gap-4">

        <div class="p-3 bg-indigo-100 rounded-2xl shadow-sm">
            <x-heroicon-o-user-plus class="w-7 h-7 text-indigo-600"/>
        </div>

        <div>
            <h1 class="text-3xl font-bold text-gray-800 tracking-tight">
                Créer un utilisateur
            </h1>
            <p class="text-gray-500 text-sm">
                Ajouter un nouveau compte au système ERP
            </p>
        </div>

    </div>


    {{-- CARD FORM --}}
    <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100">

        <form method="POST"
              action="{{ route('admin.users.store') }}"
              class="space-y-6">

            @csrf

            {{-- FORM PARTIAL --}}
            @include('admin.users._form', [
                'roles' => $roles
            ])

            {{-- ACTIONS --}}
            <div class="flex justify-between items-center pt-6 border-t border-gray-100">

                <a href="{{ route('admin.users.index') }}"
                   class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 transition">
                    <x-heroicon-o-arrow-left class="w-5 h-5"/>
                    Retour
                </a>

                <button type="submit"
                        class="inline-flex items-center gap-2 bg-indigo-600 text-white px-6 py-2.5 rounded-xl shadow hover:bg-indigo-700 hover:scale-105 transition">
                    <x-heroicon-o-check class="w-5 h-5"/>
                    Enregistrer
                </button>

            </div>

        </form>

    </div>

</div>

</x-app-layout>