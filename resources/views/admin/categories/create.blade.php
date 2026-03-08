<x-app-layout>

<div class="max-w-5xl mx-auto space-y-10"
     x-data
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 translate-y-6"
     x-transition:enter-end="opacity-100 translate-y-0">

    {{-- ================= BREADCRUMB ================= --}}
    <nav class="flex items-center gap-2 text-sm text-gray-500">

        <x-heroicon-o-folder class="w-4 h-4"/>

        <a href="{{ route('admin.categories.index') }}"
           class="hover:text-indigo-600 transition">
            Catégories
        </a>

        <x-heroicon-o-chevron-right class="w-4 h-4 text-gray-400"/>

        <span class="text-gray-700 font-medium flex items-center gap-1">
            <x-heroicon-o-plus class="w-4 h-4"/>
            Nouvelle catégorie
        </span>
    </nav>


    {{-- ================= HEADER ================= --}}
    <div class="flex items-center justify-between">

        <div class="flex items-start gap-4">

            <div class="p-3 bg-indigo-100 rounded-2xl">
                <x-heroicon-o-folder-plus class="w-6 h-6 text-indigo-600"/>
            </div>

            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    Nouvelle catégorie
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Créer une nouvelle catégorie de produits
                </p>
            </div>

        </div>

        <a href="{{ route('admin.categories.index') }}"
           class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition">

            <x-heroicon-o-arrow-left class="w-4 h-4"/>

            Retour
        </a>

    </div>


    {{-- ================= VALIDATION ERRORS ================= --}}
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-2xl">

            <div class="flex items-center gap-2 mb-3">
                <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-red-600"/>
                <p class="font-semibold">Veuillez corriger les erreurs :</p>
            </div>

            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>
    @endif


    {{-- ================= FORM CARD ================= --}}
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">

        <form action="{{ route('admin.categories.store') }}"
              method="POST"
              enctype="multipart/form-data"
              class="space-y-10">

            @include('admin.categories._form')

            {{-- ACTIONS --}}
            <div class="flex justify-end gap-4 pt-6 border-t">

                <a href="{{ route('admin.categories.index') }}"
                   class="inline-flex items-center gap-2 px-6 py-2.5 rounded-2xl border border-gray-300
                          text-gray-600 hover:bg-gray-50 transition">

                    <x-heroicon-o-x-mark class="w-4 h-4"/>
                    Annuler
                </a>

                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-2xl bg-indigo-600
                               hover:bg-indigo-700 text-white shadow-md
                               hover:shadow-lg transition">

                    <x-heroicon-o-check class="w-4 h-4"/>
                    Enregistrer
                </button>

            </div>

        </form>

    </div>

</div>


{{-- ================= SUCCESS TOAST ================= --}}
@if(session('success'))
<div x-data="{ show: true }"
     x-show="show"
     x-transition
     x-init="setTimeout(() => show = false, 4000)"
     class="fixed top-6 right-6 bg-green-600 text-white px-6 py-4 rounded-2xl shadow-xl">

    <div class="flex items-center gap-3">

        <x-heroicon-o-check-circle class="w-5 h-5"/>

        <span class="text-sm font-medium">{{ session('success') }}</span>

        <button @click="show = false"
                class="ml-4 text-white/80 hover:text-white">
            <x-heroicon-o-x-mark class="w-4 h-4"/>
        </button>

    </div>

</div>
@endif


{{-- ================= ERROR TOAST ================= --}}
@if(session('error'))
<div x-data="{ show: true }"
     x-show="show"
     x-transition
     x-init="setTimeout(() => show = false, 5000)"
     class="fixed top-6 right-6 bg-red-600 text-white px-6 py-4 rounded-2xl shadow-xl">

    <div class="flex items-center gap-3">

        <x-heroicon-o-x-circle class="w-5 h-5"/>

        <span class="text-sm font-medium">{{ session('error') }}</span>

        <button @click="show = false"
                class="ml-4 text-white/80 hover:text-white">
            <x-heroicon-o-x-mark class="w-4 h-4"/>
        </button>

    </div>

</div>
@endif

</x-app-layout>