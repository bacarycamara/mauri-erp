@php
$isEdit = isset($product) && $product->exists;
@endphp

@if($isEdit)
@can('edit products')
@else
@can('create products')
@endif

@csrf

<div 
    x-data="productForm({
        purchase: {{ old('purchase_price',$product->purchase_price ?? 0) }},
        selling: {{ old('selling_price',$product->selling_price ?? 0) }},
        vat: {{ old('vat_rate',$product->vat_rate ?? 0) }}
    })"
    x-init="init()"
    class="max-w-5xl mx-auto space-y-12"
    x-cloak
>

```
{{-- ================= HEADER ACTIONS ================= --}}
<div class="flex items-center justify-between">

    <a href="{{ route('admin.products.index') }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-300
              text-gray-600 hover:bg-gray-100 transition">
        <x-heroicon-o-arrow-left class="w-4 h-4"/>
        Retour à la liste
    </a>

    <button type="submit"
            class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700
                   text-white rounded-xl shadow-lg hover:scale-105 transition">
        <x-heroicon-o-check class="w-4 h-4"/>
        Enregistrer
    </button>
</div>


{{-- ================= INFORMATIONS ================= --}}
<div class="card">
    <h2 class="section-title flex items-center gap-2">
        <x-heroicon-o-cube class="w-5 h-5 text-indigo-600"/>
        Informations générales
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

        <div>
            <label class="label">Nom du produit</label>
            <input type="text"
                   name="name"
                   value="{{ old('name',$product->name ?? '') }}"
                   required
                   class="input-style">
        </div>

        <div>
            <label class="label">SKU</label>
            <input type="text"
                   value="{{ $product->sku ?? 'Généré automatiquement' }}"
                   readonly
                   class="input-style bg-gray-50">
        </div>

        <div>
            <label class="label">Catégorie</label>
            <select name="category_id" class="input-style">
                <option value="">Sélectionner</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}"
                        {{ old('category_id',$product->category_id ?? '')==$category->id?'selected':'' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="label">Type</label>
            <select name="type" required class="input-style">
                <option value="physical"
                    {{ old('type',$product->type ?? 'physical')=='physical'?'selected':'' }}>
                    Produit physique
                </option>
                <option value="service"
                    {{ old('type',$product->type ?? '')=='service'?'selected':'' }}>
                    Service
                </option>
            </select>
        </div>

        <div>
            <label class="label">Unité</label>
            <input type="text"
                   name="unit"
                   value="{{ old('unit',$product->unit ?? 'Pièce') }}"
                   required
                   class="input-style">
        </div>

    </div>
</div>


{{-- ================= TARIFICATION ================= --}}
<div class="card">
    <h2 class="section-title flex items-center gap-2">
        <x-heroicon-o-currency-dollar class="w-5 h-5 text-green-600"/>
        Tarification
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">

        <div>
            <label class="label">Prix d'achat</label>
            <input type="number" step="0.01"
                   name="purchase_price"
                   x-model="purchase"
                   @input="calculate()"
                   class="input-style">
        </div>

        <div>
            <label class="label">Prix HT</label>
            <input type="number" step="0.01"
                   name="selling_price"
                   x-model="selling"
                   @input="calculate()"
                   required
                   class="input-style">
        </div>

        <div>
            <label class="label">TVA (%)</label>
            <input type="number" step="0.01"
                   name="vat_rate"
                   x-model="vat"
                   @input="calculate()"
                   class="input-style">
        </div>

        <div>
            <label class="label">Prix TTC</label>
            <input type="number"
                   x-model="selling_ttc"
                   readonly
                   class="input-style bg-gray-50">
        </div>
    </div>

    <div class="mt-6">
        <label class="label">Marge (%)</label>
        <input type="number"
               name="profit_margin"
               x-model="margin"
               readonly
               class="input-style bg-gray-50">
    </div>
</div>


{{-- ================= STOCK ================= --}}
<div class="card">
    <h2 class="section-title flex items-center gap-2">
        <x-heroicon-o-archive-box class="w-5 h-5 text-purple-600"/>
        Gestion du stock
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

        <div>
            <label class="label">Quantité en stock</label>
            <input type="number"
                   name="stock_quantity"
                   value="{{ old('stock_quantity',$product->stock_quantity ?? 0) }}"
                   class="input-style">
        </div>

        <div>
            <label class="label">Stock minimum</label>
            <input type="number"
                   name="minimum_stock"
                   value="{{ old('minimum_stock',$product->minimum_stock ?? 0) }}"
                   class="input-style">
        </div>

    </div>
</div>


{{-- ================= DESCRIPTION ================= --}}
<div class="card">
    <h2 class="section-title">Description</h2>

    <textarea name="description"
              rows="4"
              class="input-style">{{ old('description',$product->description ?? '') }}</textarea>
</div>


{{-- ================= PHOTO ================= --}}
<div class="card">
    <h2 class="section-title flex items-center gap-2">
        <x-heroicon-o-photo class="w-5 h-5 text-indigo-600"/>
        Photo du produit
    </h2>

    <input type="file"
           name="photo"
           @change="previewImage"
           class="file-input">

    <div class="mt-6">
        <img x-show="imageUrl"
             :src="imageUrl"
             class="h-28 rounded-2xl shadow-lg object-cover">
    </div>
</div>


{{-- ================= STATUT ================= --}}
<div class="card">

    <input type="hidden" name="is_active" value="0">

    <label class="flex items-center gap-3">
        <input type="checkbox"
               name="is_active"
               value="1"
               {{ old('is_active',$product->is_active ?? true)?'checked':'' }}
               class="h-5 w-5 text-indigo-600 rounded">

        <span class="text-sm font-medium text-gray-700">
            Produit actif
        </span>
    </label>

</div>
```

</div>

@endcan
