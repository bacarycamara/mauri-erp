<x-app-layout>

@can('create products')

<div class="max-w-5xl mx-auto space-y-10">

```
{{-- ================= BREADCRUMB ================= --}}
<nav class="text-sm text-gray-500">
    <a href="{{ route('admin.products.index') }}"
       class="hover:text-indigo-600 transition">
        Produits
    </a>
    <span class="mx-2">/</span>
    <span class="text-gray-700 font-medium">
        Nouveau produit
    </span>
</nav>


{{-- ================= HEADER ================= --}}
<div class="flex items-start gap-3">

    <div class="p-3 bg-indigo-100 rounded-2xl">
        <x-heroicon-o-cube class="w-6 h-6 text-indigo-600"/>
    </div>

    <div>
        <h1 class="text-2xl font-bold text-gray-900">
            Créer un produit
        </h1>
        <p class="text-sm text-gray-500 mt-1">
            Ajouter un nouveau produit ou service à votre catalogue
        </p>
    </div>

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
<div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100">

    <form action="{{ route('admin.products.store') }}"
          method="POST"
          enctype="multipart/form-data"
          class="space-y-10">

        @include('admin.products._form')

        {{-- ACTIONS --}}
        <div class="flex justify-end gap-4 pt-6 border-t">

            <a href="{{ route('admin.products.index') }}"
               class="px-6 py-2.5 rounded-2xl border border-gray-300
                      text-gray-600 hover:bg-gray-50 transition">
                Annuler
            </a>

            <button type="submit"
                    class="px-6 py-2.5 rounded-2xl bg-indigo-600
                           hover:bg-indigo-700 text-white shadow-md
                           hover:shadow-lg transition">
                Enregistrer le produit
            </button>

        </div>

    </form>

</div>
```

</div>

@endcan

</x-app-layout>
