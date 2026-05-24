@php
    $isEdit  = isset($product) && $product->exists;
    $p       = $product ?? null;
    $currency = company()?->currency ?? '';
@endphp

{{-- ✅ @csrf dans le form parent uniquement, pas ici --}}

<div x-data="productForm({
        purchase: {{ (float) old('purchase_price', $p?->purchase_price ?? 0) }},
        selling:  {{ (float) old('selling_price',  $p?->selling_price  ?? 0) }},
        vat:      {{ (float) old('vat_rate',        $p?->vat_rate       ?? 0) }}
     })"
     x-init="init()"
     class="space-y-6"
     x-cloak>


    {{-- ================= INFORMATIONS GÉNÉRALES ================= --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h2 class="text-base font-semibold text-gray-700 flex items-center gap-2 mb-4">
            <x-heroicon-o-cube class="w-4 h-4 text-indigo-600"/>
            Informations générales
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

            {{-- NOM --}}
            <div>
                <label for="name" class="block text-sm font-medium text-gray-600 mb-1">
                    Nom du produit <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="name"
                       name="name"
                       value="{{ old('name', $p?->name ?? '') }}"
                       required
                       maxlength="200"
                       class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500 text-sm
                              @error('name') border-red-400 @enderror">
                @error('name')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- SKU (lecture seule) --}}
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">SKU</label>
                <input type="text"
                       value="{{ $p?->sku ?? 'Généré automatiquement' }}"
                       readonly
                       class="w-full rounded-xl border-gray-300 bg-gray-50 text-gray-400 text-sm cursor-not-allowed">
                <p class="text-xs text-gray-400 mt-1">Généré automatiquement à la création.</p>
            </div>

            {{-- CATÉGORIE --}}
            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-600 mb-1">
                    Catégorie
                </label>
                <select id="category_id"
                        name="category_id"
                        class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500 text-sm">
                    <option value="">Sélectionner</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}"
                            @selected(old('category_id', $p?->category_id) == $category->id)>
                        {{ e($category->name) }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- TYPE --}}
            <div>
                <label for="type" class="block text-sm font-medium text-gray-600 mb-1">
                    Type <span class="text-red-500">*</span>
                </label>
                <select id="type"
                        name="type"
                        required
                        class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500 text-sm">
                    <option value="physical" @selected(old('type', $p?->type ?? 'physical') === 'physical')>
                        Produit physique
                    </option>
                    <option value="service"  @selected(old('type', $p?->type) === 'service')>
                        Service
                    </option>
                </select>
            </div>

            {{-- UNITÉ --}}
            <div>
                <label for="unit" class="block text-sm font-medium text-gray-600 mb-1">
                    Unité <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="unit"
                       name="unit"
                       value="{{ old('unit', $p?->unit ?? 'Pièce') }}"
                       required
                       maxlength="50"
                       class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>

        </div>
    </div>


    {{-- ================= TARIFICATION ================= --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h2 class="text-base font-semibold text-gray-700 flex items-center gap-2 mb-4">
            <x-heroicon-o-currency-dollar class="w-4 h-4 text-green-600"/>
            Tarification
            <span class="text-xs text-gray-400 font-normal ml-2">({{ $currency }})</span>
        </h2>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-5">

            <div>
                <label for="purchase_price" class="block text-sm font-medium text-gray-600 mb-1">
                    Prix d'achat
                </label>
                <input type="number"
                       id="purchase_price"
                       name="purchase_price"
                       step="0.01"
                       min="0"
                       x-model.number="purchase"
                       @input="calculate()"
                       class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>

            <div>
                <label for="selling_price" class="block text-sm font-medium text-gray-600 mb-1">
                    Prix HT <span class="text-red-500">*</span>
                </label>
                <input type="number"
                       id="selling_price"
                       name="selling_price"
                       step="0.01"
                       min="0"
                       x-model.number="selling"
                       @input="calculate()"
                       required
                       class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500 text-sm
                              @error('selling_price') border-red-400 @enderror">
                @error('selling_price')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="vat_rate" class="block text-sm font-medium text-gray-600 mb-1">
                    TVA (%)
                </label>
                <input type="number"
                       id="vat_rate"
                       name="vat_rate"
                       step="0.01"
                       min="0"
                       max="100"
                       x-model.number="vat"
                       @input="calculate()"
                       class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Prix TTC</label>
                <input type="number"
                       x-model="selling_ttc"
                       readonly
                       class="w-full rounded-xl border-gray-300 bg-gray-50 text-gray-400 text-sm cursor-not-allowed">
            </div>

        </div>

        <div class="mt-4 max-w-xs">
            <label class="block text-sm font-medium text-gray-600 mb-1">Marge (%)</label>
            <input type="number"
                   name="profit_margin"
                   x-model="margin"
                   readonly
                   class="w-full rounded-xl border-gray-300 bg-gray-50 text-gray-400 text-sm cursor-not-allowed">
        </div>
    </div>


    {{-- ================= STOCK ================= --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h2 class="text-base font-semibold text-gray-700 flex items-center gap-2 mb-4">
            <x-heroicon-o-archive-box class="w-4 h-4 text-purple-600"/>
            Stock
        </h2>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label for="stock_quantity" class="block text-sm font-medium text-gray-600 mb-1">
                    Quantité en stock
                </label>
                <input type="number"
                       id="stock_quantity"
                       name="stock_quantity"
                       value="{{ old('stock_quantity', $p?->stock_quantity ?? 0) }}"
                       min="0"
                       class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div>
                <label for="minimum_stock" class="block text-sm font-medium text-gray-600 mb-1">
                    Stock minimum
                </label>
                <input type="number"
                       id="minimum_stock"
                       name="minimum_stock"
                       value="{{ old('minimum_stock', $p?->minimum_stock ?? 0) }}"
                       min="0"
                       class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
        </div>
    </div>


    {{-- ================= DESCRIPTION + PHOTO ================= --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-base font-semibold text-gray-700 mb-3">Description</h2>
            <textarea id="description"
                      name="description"
                      rows="4"
                      maxlength="2000"
                      class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500 text-sm resize-none">{{ old('description', $p?->description ?? '') }}</textarea>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6"
             x-data="{ imageUrl: '' }">
            <h2 class="text-base font-semibold text-gray-700 flex items-center gap-2 mb-3">
                <x-heroicon-o-photo class="w-4 h-4 text-indigo-600"/>
                Photo
            </h2>

            {{-- ✅ accept limité aux images --}}
            <input type="file"
                   name="photo"
                   accept="image/jpeg,image/png,image/webp,image/gif"
                   @change="imageUrl = $event.target.files[0]
                       ? URL.createObjectURL($event.target.files[0])
                       : ''"
                   class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4
                          file:rounded-xl file:border-0 file:text-sm file:font-medium
                          file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">

            <div class="mt-4">
                {{-- Aperçu nouvelle photo --}}
                <img x-show="imageUrl"
                     x-cloak
                     :src="imageUrl"
                     alt="Aperçu"
                     class="h-24 rounded-2xl shadow object-cover">

                {{-- Photo existante en mode edit --}}
                @if($isEdit && $p?->photo)
                <img x-show="!imageUrl"
                     src="{{ asset('storage/' . $p->photo) }}"
                     alt="{{ e($p->name) }}"
                     class="h-24 rounded-2xl shadow object-cover"
                     onerror="this.src='https://placehold.co/96x96/e0e7ff/6366f1?text=Photo'">
                @else
                <div x-show="!imageUrl"
                     class="h-24 w-24 rounded-2xl bg-gray-100 flex items-center justify-center text-gray-400 text-xs">
                    Aucune photo
                </div>
                @endif
            </div>
        </div>

    </div>


    {{-- ================= STATUT ================= --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        {{-- ✅ Hidden input pour envoyer 0 si non coché --}}
        <input type="hidden" name="is_active" value="0">
        <label class="flex items-center gap-3 cursor-pointer">
            <input type="checkbox"
                   name="is_active"
                   value="1"
                   {{ old('is_active', $p?->is_active ?? true) ? 'checked' : '' }}
                   class="h-4 w-4 text-indigo-600 rounded">
            <div>
                <span class="text-sm font-medium text-gray-700">Produit actif</span>
                <p class="text-xs text-gray-400">Désactiver rend le produit invisible dans les ventes</p>
            </div>
        </label>
    </div>

</div>{{-- /x-data productForm --}}


@push('scripts')
<script>
function productForm({ purchase, selling, vat }) {
    return {
        purchase:    purchase    || 0,
        selling:     selling     || 0,
        vat:         vat         || 0,
        selling_ttc: 0,
        margin:      0,

        init() {
            this.calculate();
        },

        calculate() {
            const p = parseFloat(this.purchase) || 0;
            const s = parseFloat(this.selling)  || 0;
            const v = parseFloat(this.vat)      || 0;

            // Prix TTC
            this.selling_ttc = parseFloat((s * (1 + v / 100)).toFixed(2));

            // Marge %
            this.margin = p > 0
                ? parseFloat(((s - p) / p * 100).toFixed(2))
                : 0;
        }
    }
}
</script>
@endpush