<dialog id="movementModal"
        class="rounded-3xl p-0 w-full max-w-xl backdrop:bg-black/40">

<form method="POST"
      action="{{ route('admin.stock-movements.store') }}"
      class="bg-white rounded-3xl overflow-hidden">

@csrf


{{-- ================= HEADER ================= --}}
<div class="flex items-center justify-between px-6 py-5 border-b bg-indigo-50">

    <div class="flex items-center gap-3">

        <div class="p-2 rounded-xl bg-indigo-100 text-indigo-600">
            <x-heroicon-o-arrows-right-left class="w-5 h-5"/>
        </div>

        <div>
            <h2 class="font-semibold text-gray-800">
                Nouveau mouvement
            </h2>
            <p class="text-xs text-gray-500">
                Ajouter une entrée ou sortie de stock
            </p>
        </div>

    </div>

    <button type="button"
            onclick="movementModal.close()"
            class="p-2 rounded-lg hover:bg-gray-200 transition">
        <x-heroicon-o-x-mark class="w-5 h-5"/>
    </button>

</div>


{{-- ================= BODY ================= --}}
<div class="p-6 space-y-5">

    {{-- PRODUIT --}}
    <div class="space-y-1">
        <x-input-label value="Produit"/>

        <select name="product_id"
                class="input-erp w-full"
                required>

            <option value="">-- Sélectionner produit --</option>

            @foreach(\App\Models\Product::orderBy('name')->get() as $p)
                <option value="{{ $p->id }}">
                    {{ $p->name }}
                </option>
            @endforeach

        </select>
    </div>


    {{-- TYPE --}}
    <div class="space-y-1">
        <x-input-label value="Type de mouvement"/>

        <select name="type" class="input-erp w-full">

            <option value="purchase">⬆ Entrée (Approvisionnement)</option>
            <option value="sale">⬇ Sortie (Vente)</option>
            <option value="adjustment">⚙ Ajustement manuel</option>

        </select>
    </div>


    {{-- QUANTITE --}}
    <div class="space-y-1">
        <x-input-label value="Quantité"/>

        <input type="number"
               name="quantity"
               step="0.01"
               min="1"
               required
               class="input-erp w-full"
               placeholder="Ex: 10">
    </div>


    {{-- REFERENCE --}}
    <div class="space-y-1">
        <x-input-label value="Référence (optionnel)"/>

        <input type="text"
               name="reference"
               class="input-erp w-full"
               placeholder="Facture, Ajustement, etc">
    </div>

</div>


{{-- ================= FOOTER ================= --}}
<div class="flex justify-end gap-3 px-6 py-4 border-t bg-gray-50">

    <button type="button"
            onclick="movementModal.close()"
            class="px-4 py-2 rounded-xl border text-gray-600 hover:bg-gray-100 transition">
        Annuler
    </button>

    <button type="submit"
            class="flex items-center gap-2 bg-indigo-600 text-white px-5 py-2 rounded-xl hover:bg-indigo-700 transition shadow">

        <x-heroicon-o-check class="w-4 h-4"/>
        Enregistrer

    </button>

</div>

</form>

</dialog>