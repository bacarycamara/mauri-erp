<x-app-layout>

<div class="max-w-4xl mx-auto bg-white p-8 rounded-2xl shadow">

    <h1 class="text-xl font-bold mb-6">
        Modifier Fournisseur
    </h1>

    <form action="{{ route('admin.suppliers.update',$supplier) }}"
          method="POST">
        @method('PUT')
        @include('admin.suppliers._form')

        <div class="mt-6 flex justify-end gap-4">
            <a href="{{ route('admin.suppliers.index') }}"
               class="px-4 py-2 border rounded-xl">Annuler</a>

            <button class="px-5 py-2 bg-indigo-600 text-white rounded-xl">
                Mettre à jour
            </button>
        </div>
    </form>

</div>

</x-app-layout>