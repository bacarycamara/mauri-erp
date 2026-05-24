<x-app-layout>

@can('edit categories')

<div class="max-w-5xl mx-auto space-y-10"
     x-data="{ submitting: false }"
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
            <x-heroicon-o-pencil-square class="w-4 h-4"/>
            Modifier
        </span>
    </nav>

    {{-- ================= HEADER ================= --}}
    <div class="flex items-center justify-between">
        <div class="flex items-start gap-4">
            <div class="p-3 bg-indigo-100 rounded-2xl">
                <x-heroicon-o-pencil-square class="w-6 h-6 text-indigo-600"/>
            </div>
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl font-bold text-gray-800">Modifier la catégorie</h1>
                    @if($category->is_active)
                    <span class="inline-flex items-center gap-1 px-3 py-1 text-xs rounded-full bg-green-100 text-green-700 font-semibold">
                        <x-heroicon-o-check-circle class="w-3 h-3"/>
                        Actif
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1 px-3 py-1 text-xs rounded-full bg-gray-200 text-gray-600 font-semibold">
                        <x-heroicon-o-pause-circle class="w-3 h-3"/>
                        Inactif
                    </span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 mt-1">
                    Mise à jour de :
                    <span class="font-semibold text-gray-700">{{ e($category->name) }}</span>
                </p>
            </div>
        </div>
        <a href="{{ route('admin.categories.index') }}"
           class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition">
            <x-heroicon-o-arrow-left class="w-4 h-4"/>
            Retour
        </a>
    </div>

    {{-- ================= ERREURS DE VALIDATION ================= --}}
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-2xl">
        <div class="flex items-center gap-2 mb-3">
            <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-red-600 flex-shrink-0"/>
            <p class="font-semibold">Veuillez corriger les erreurs :</p>
        </div>
        <ul class="list-disc list-inside text-sm space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- ================= FORM MISE À JOUR ================= --}}
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
        <form id="form-update"
              action="{{ route('admin.categories.update', $category) }}"
              method="POST"
              enctype="multipart/form-data"
              @submit.prevent="submitting = true; $el.submit()"
              class="space-y-10">
            @csrf
            @method('PUT')

            @include('admin.categories._form')

            {{-- ACTIONS --}}
            <div class="flex justify-between items-center pt-6 border-t">

                {{-- Bouton suppression — HORS du form principal --}}
                @can('delete categories')
                @if(($category->products_count ?? $category->products()->count()) === 0)
                <button type="button"
                        onclick="document.getElementById('form-delete').submit()"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl
                               border border-red-300 text-red-600 hover:bg-red-50 transition"
                        onclick="return confirm('Supprimer définitivement cette catégorie ?')">
                    <x-heroicon-o-trash class="w-4 h-4"/>
                    Supprimer
                </button>
                @else
                <span class="text-xs text-gray-400 italic">
                    Suppression impossible — catégorie liée à {{ $category->products_count ?? $category->products()->count() }} produit(s)
                </span>
                @endif
                @endcan

                @cannot('delete categories')
                <span></span>
                @endcannot

                <div class="flex gap-4">
                    <a href="{{ route('admin.categories.index') }}"
                       class="inline-flex items-center gap-2 px-6 py-2.5 rounded-2xl border border-gray-300
                              text-gray-600 hover:bg-gray-50 transition">
                        <x-heroicon-o-x-mark class="w-4 h-4"/>
                        Annuler
                    </a>
                    <button type="submit"
                            :disabled="submitting"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-2xl bg-indigo-600
                                   hover:bg-indigo-700 text-white shadow-md hover:shadow-lg transition
                                   disabled:opacity-50 disabled:cursor-not-allowed">
                        <x-heroicon-o-check class="w-4 h-4"/>
                        <span x-show="!submitting">Mettre à jour</span>
                        <span x-show="submitting" x-cloak>Enregistrement...</span>
                    </button>
                </div>

            </div>
        </form>
    </div>

</div>

{{-- ================= FORM SUPPRESSION (hors du form update) ================= --}}
@can('delete categories')
@if(($category->products_count ?? $category->products()->count()) === 0)
<form id="form-delete"
      action="{{ route('admin.categories.destroy', $category) }}"
      method="POST"
      class="hidden"
      onsubmit="return confirm('Supprimer définitivement cette catégorie ?')">
    @csrf
    @method('DELETE')
</form>
@endif
@endcan

@else
<div class="flex flex-col items-center justify-center py-24 text-gray-400">
    <x-heroicon-o-lock-closed class="w-12 h-12 mb-4 text-gray-300"/>
    <p class="text-lg font-medium">Accès non autorisé</p>
</div>
@endcan

{{-- ================= TOASTS ================= --}}
@if(session('success'))
<div x-data="{ show: true }"
     x-show="show"
     x-cloak
     x-transition
     x-init="setTimeout(() => show = false, 4000)"
     class="fixed top-6 right-6 z-[9999] bg-green-600 text-white
            px-6 py-4 rounded-2xl shadow-xl">
    <div class="flex items-center gap-3">
        <x-heroicon-o-check-circle class="w-5 h-5 flex-shrink-0"/>
        <span class="text-sm font-medium">{{ session('success') }}</span>
        <button @click="show = false" class="ml-4 text-white/80 hover:text-white">
            <x-heroicon-o-x-mark class="w-4 h-4"/>
        </button>
    </div>
</div>
@endif

@if(session('error'))
<div x-data="{ show: true }"
     x-show="show"
     x-cloak
     x-transition
     x-init="setTimeout(() => show = false, 5000)"
     class="fixed top-6 right-6 z-[9999] bg-red-600 text-white
            px-6 py-4 rounded-2xl shadow-xl">
    <div class="flex items-center gap-3">
        <x-heroicon-o-x-circle class="w-5 h-5 flex-shrink-0"/>
        <span class="text-sm font-medium">{{ session('error') }}</span>
        <button @click="show = false" class="ml-4 text-white/80 hover:text-white">
            <x-heroicon-o-x-mark class="w-4 h-4"/>
        </button>
    </div>
</div>
@endif

</x-app-layout>