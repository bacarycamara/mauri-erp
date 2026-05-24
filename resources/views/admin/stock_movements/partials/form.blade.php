<dialog id="movementModal"
        class="rounded-3xl p-0 w-full max-w-xl backdrop:bg-black/40"
        x-data="{ submitting: false }">

    <form method="POST"
          action="{{ route('admin.stock-movements.store') }}"
          class="bg-white rounded-3xl overflow-hidden"
          @submit.prevent="submitting = true; $el.submit()">
        @csrf

        {{-- ================= HEADER ================= --}}
        <div class="flex items-center justify-between px-6 py-5 border-b bg-indigo-50">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-indigo-100 text-indigo-600">
                    <x-heroicon-o-arrows-right-left class="w-5 h-5"/>
                </div>
                <div>
                    <h2 class="font-semibold text-gray-800">Nouveau mouvement</h2>
                    <p class="text-xs text-gray-500">Ajouter une entrée ou sortie de stock</p>
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
                <label for="movement_product_id"
                       class="block text-sm font-medium text-gray-700">
                    Produit <span class="text-red-500">*</span>
                </label>
                {{--
                    ✅ CORRIGÉ : plus de requête DB dans la vue.
                    Les produits doivent être passés par le controller
                    via $products. En mode partial inclus depuis l'index,
                    $products est déjà disponible.
                --}}
                <select id="movement_product_id"
                        name="product_id"
                        class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500 text-sm"
                        required>
                    <option value="">-- Sélectionner produit --</option>
                    @foreach($products ?? \App\Models\Product::orderBy('name')->get() as $p)
                    <option value="{{ $p->id }}">
                        {{ e($p->name) }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- TYPE --}}
            <div class="space-y-1">
                <label for="movement_type"
                       class="block text-sm font-medium text-gray-700">
                    Type de mouvement <span class="text-red-500">*</span>
                </label>
                <select id="movement_type"
                        name="type"
                        class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500 text-sm"
                        required>
                    {{-- ✅ Pas d'emojis dans les values — ils peuvent casser les comparaisons PHP --}}
                    <option value="purchase">Entrée (Approvisionnement)</option>
                    <option value="sale">Sortie (Vente)</option>
                    <option value="adjustment">Ajustement manuel</option>
                    <option value="return">Retour</option>
                </select>
            </div>

            {{-- QUANTITÉ --}}
            <div class="space-y-1">
                <label for="movement_quantity"
                       class="block text-sm font-medium text-gray-700">
                    Quantité <span class="text-red-500">*</span>
                </label>
                <input type="number"
                       id="movement_quantity"
                       name="quantity"
                       step="0.01"
                       min="0.01"
                       required
                       placeholder="Ex: 10"
                       class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>

            {{-- RÉFÉRENCE --}}
            <div class="space-y-1">
                <label for="movement_reference"
                       class="block text-sm font-medium text-gray-700">
                    Référence <span class="text-xs text-gray-400">(optionnel)</span>
                </label>
                <input type="text"
                       id="movement_reference"
                       name="reference"
                       maxlength="100"
                       placeholder="Facture, Ajustement, etc."
                       class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>

        </div>


        {{-- ================= FOOTER ================= --}}
        <div class="flex justify-end gap-3 px-6 py-4 border-t bg-gray-50">

            <button type="button"
                    onclick="movementModal.close()"
                    class="px-4 py-2 rounded-xl border text-gray-600 hover:bg-gray-100 transition text-sm">
                Annuler
            </button>

            <button type="submit"
                    :disabled="submitting"
                    class="flex items-center gap-2 bg-indigo-600 text-white px-5 py-2
                           rounded-xl hover:bg-indigo-700 transition shadow text-sm
                           disabled:opacity-50 disabled:cursor-not-allowed">
                <x-heroicon-o-check class="w-4 h-4"/>
                <span x-show="!submitting">Enregistrer</span>
                <span x-show="submitting" x-cloak>Enregistrement...</span>
            </button>

        </div>

    </form>

</dialog>