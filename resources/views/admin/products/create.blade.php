<x-app-layout>

@can('create products')

<div class="max-w-4xl mx-auto space-y-6"
     x-data="{ submitting: false }">

    {{-- ================= BREADCRUMB ================= --}}
    <nav class="text-sm text-gray-500 flex items-center gap-2">
        <x-heroicon-o-cube class="w-4 h-4"/>
        <a href="{{ route('admin.products.index') }}"
           class="hover:text-indigo-600 transition">
            Produits
        </a>
        <x-heroicon-o-chevron-right class="w-4 h-4 text-gray-400"/>
        <span class="text-gray-700 font-medium">Nouveau produit</span>
    </nav>


    {{-- ================= HEADER ================= --}}
    <div class="flex items-center gap-3">
        <div class="p-2.5 bg-indigo-100 rounded-2xl">
            <x-heroicon-o-cube class="w-5 h-5 text-indigo-600"/>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Créer un produit</h1>
            <p class="text-sm text-gray-500">Ajouter un nouveau produit ou service au catalogue</p>
        </div>
    </div>


    {{-- ================= ERREURS ================= --}}
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-3 rounded-2xl text-sm">
        <p class="font-semibold mb-1">Veuillez corriger les erreurs :</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif


    {{-- ================= FORM ================= --}}
    <div class="bg-white p-6 rounded-3xl shadow-xl border border-gray-100">

        <form action="{{ route('admin.products.store') }}"
              method="POST"
              enctype="multipart/form-data"
              @submit.prevent="submitting = true; $el.submit()"
              class="space-y-8">
            @csrf

            @include('admin.products._form')

            <div class="flex justify-end gap-3 pt-4 border-t">

                <a href="{{ route('admin.products.index') }}"
                   class="px-5 py-2 rounded-2xl border border-gray-300 text-gray-600 hover:bg-gray-50 transition text-sm">
                    Annuler
                </a>

                <button type="submit"
                        :disabled="submitting"
                        class="px-5 py-2 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white
                               shadow-md transition text-sm
                               disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!submitting">Enregistrer le produit</span>
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