<x-app-layout>

@can('create suppliers')

<div class="max-w-4xl mx-auto space-y-6"
     x-data="{ submitting: false }">

    {{-- ================= HEADER ================= --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-3">
            <div class="p-2.5 bg-indigo-100 rounded-2xl">
                <x-heroicon-o-building-storefront class="w-5 h-5 text-indigo-600"/>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-900">Nouveau Fournisseur</h1>
                <p class="text-sm text-gray-500">Ajouter un nouveau fournisseur au système</p>
            </div>
        </div>
        <a href="{{ route('admin.suppliers.index') }}"
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
    <div class="bg-white p-8 rounded-2xl shadow border border-gray-100">

        <form action="{{ route('admin.suppliers.store') }}"
              method="POST"
              @submit.prevent="submitting = true; $el.submit()">
            @csrf

            @include('admin.suppliers._form')

            <div class="mt-6 flex justify-end gap-4 pt-4 border-t">
                <a href="{{ route('admin.suppliers.index') }}"
                   class="px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-100 transition text-sm">
                    Annuler
                </a>
                <button type="submit"
                        :disabled="submitting"
                        class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl
                               transition shadow text-sm
                               disabled:opacity-50 disabled:cursor-not-allowed">
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