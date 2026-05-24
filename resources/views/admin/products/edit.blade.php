<x-app-layout>

@can('edit products')

<div class="max-w-4xl mx-auto space-y-6"
     x-data="{ loading: false }">

    {{-- ================= BREADCRUMB ================= --}}
    <nav class="text-sm text-gray-500 flex items-center gap-2">
        <x-heroicon-o-cube class="w-4 h-4"/>
        <a href="{{ route('admin.products.index') }}"
           class="hover:text-indigo-600 transition">
            Produits
        </a>
        <x-heroicon-o-chevron-right class="w-4 h-4 text-gray-400"/>
        <span class="text-gray-700 font-medium">Modifier</span>
    </nav>


    {{-- ================= HEADER ================= --}}
    <div class="flex justify-between items-center flex-wrap gap-4">
        <div class="flex items-center gap-3">
            <div class="p-2.5 bg-indigo-100 rounded-2xl">
                <x-heroicon-o-pencil-square class="w-5 h-5 text-indigo-600"/>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-900">Modifier le produit</h1>
                <p class="text-sm text-gray-500">
                    <span class="font-semibold text-gray-700">{{ e($product->name) }}</span>
                </p>
            </div>
        </div>
        <a href="{{ route('admin.products.index') }}"
           class="px-4 py-2 rounded-xl border border-gray-300 text-gray-600 hover:bg-gray-100 transition text-sm">
            Retour
        </a>
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
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-6">

        <form action="{{ route('admin.products.update', $product) }}"
              method="POST"
              enctype="multipart/form-data"
              class="space-y-8"
              @submit.prevent="loading = true; $el.submit()">
            @csrf
            @method('PUT')

            @include('admin.products._form')

            {{-- ================= ACTIONS ================= --}}
            <div class="flex justify-between items-center pt-4 border-t flex-wrap gap-4">

                {{-- Bouton suppression depuis l'édition --}}
                @can('delete products')
                <form action="{{ route('admin.products.destroy', $product) }}"
                      method="POST"
                      onsubmit="return confirm('Supprimer définitivement ce produit ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2 rounded-2xl
                                   border border-red-300 text-red-600 hover:bg-red-50 transition text-sm">
                        <x-heroicon-o-trash class="w-4 h-4"/>
                        Supprimer
                    </button>
                </form>
                @endcan

                @cannot('delete products')
                <span></span>
                @endcannot

                <div class="flex gap-3">
                    <a href="{{ route('admin.products.index') }}"
                       class="px-5 py-2 rounded-2xl border border-gray-300 text-gray-600 hover:bg-gray-50 transition text-sm">
                        Annuler
                    </a>

                    <button type="submit"
                            :disabled="loading"
                            class="px-5 py-2 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-md
                                   flex items-center gap-2 transition text-sm
                                   disabled:opacity-70 disabled:cursor-not-allowed">

                        <svg x-show="loading"
                             x-cloak
                             class="animate-spin h-4 w-4 text-white"
                             xmlns="http://www.w3.org/2000/svg"
                             fill="none"
                             viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>

                        <span x-text="loading ? 'Mise à jour...' : 'Mettre à jour'"></span>

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