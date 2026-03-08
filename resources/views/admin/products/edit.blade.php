<x-app-layout>

@can('edit products')

<div class="max-w-5xl mx-auto space-y-10"
     x-data="{ loading:false }"
     x-cloak>

```
{{-- ================= BREADCRUMB ================= --}}
<nav class="text-sm text-gray-500">
    <a href="{{ route('admin.products.index') }}"
       class="hover:text-indigo-600 transition">
        Produits
    </a>
    <span class="mx-2">/</span>
    <span class="text-gray-700 font-medium">
        Modifier
    </span>
</nav>


{{-- ================= HEADER ================= --}}
<div class="flex justify-between items-start">

    <div class="flex items-start gap-4">

        <div class="p-3 bg-indigo-100 rounded-2xl">
            <svg class="h-6 w-6 text-indigo-600"
                 fill="none" stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 4v16m8-8H4"/>
            </svg>
        </div>

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Modifier le produit
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Mise à jour :
                <span class="font-semibold text-gray-700">
                    {{ $product->name }}
                </span>
            </p>
        </div>

    </div>

    <a href="{{ route('admin.products.index') }}"
       class="px-4 py-2 rounded-xl border border-gray-300
              text-gray-600 hover:bg-gray-100 transition">
        Retour
    </a>

</div>


{{-- ================= GLOBAL ERRORS ================= --}}
@if ($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-2xl">
        <p class="font-semibold mb-2">Veuillez corriger les erreurs :</p>
        <ul class="list-disc list-inside text-sm space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


{{-- ================= FORM CARD ================= --}}
<div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">

    <form action="{{ route('admin.products.update',$product) }}"
          method="POST"
          enctype="multipart/form-data"
          class="space-y-10"
          @submit="loading = true">

        @csrf
        @method('PUT')

        {{-- IMPORTANT : false envoyé si checkbox décochée --}}
        <input type="hidden" name="is_active" value="0">

        {{-- Champs --}}
        @include('admin.products._form')

        {{-- ================= ACTIONS ================= --}}
        <div class="flex justify-end gap-4 pt-6 border-t">

            <a href="{{ route('admin.products.index') }}"
               class="px-6 py-2.5 rounded-2xl border border-gray-300
                      text-gray-600 hover:bg-gray-50 transition">
                Annuler
            </a>

            <button type="submit"
                    :disabled="loading"
                    class="px-6 py-2.5 rounded-2xl bg-indigo-600
                           hover:bg-indigo-700 text-white shadow-md
                           flex items-center gap-2 transition
                           disabled:opacity-70 disabled:cursor-not-allowed">

                <svg x-show="loading"
                     class="animate-spin h-4 w-4 text-white"
                     xmlns="http://www.w3.org/2000/svg"
                     fill="none"
                     viewBox="0 0 24 24">
                    <circle class="opacity-25"
                            cx="12" cy="12" r="10"
                            stroke="currentColor"
                            stroke-width="4"></circle>
                    <path class="opacity-75"
                          fill="currentColor"
                          d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>

                <span x-text="loading ? 'Mise à jour...' : 'Mettre à jour'"></span>

            </button>

        </div>

    </form>

</div>
```

</div>

@endcan

</x-app-layout>
