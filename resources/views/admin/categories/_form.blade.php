{{-- ================= PARTIAL : _form.blade.php ================= --}}
{{-- Utilisé par categories/create.blade.php et categories/edit.blade.php --}}
{{-- @csrf doit être dans le formulaire parent, pas ici --}}

@php
    $cat      = $category ?? null;
    $isEdit   = isset($category) && $category->exists;
    // ✅ Image existante avec chemin sécurisé
    $existingImage = ($cat?->image) ? asset('storage/' . $cat->image) : '';
@endphp

<div class="space-y-10">

    {{-- ================= INFORMATIONS GÉNÉRALES ================= --}}
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8 space-y-8">

        <div class="flex items-center gap-3">
            <x-heroicon-o-folder class="w-6 h-6 text-indigo-600"/>
            <h2 class="text-xl font-semibold text-gray-800">Informations générales</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

            {{-- NOM --}}
            <div>
                <label for="cat_name"
                       class="flex items-center gap-2 text-sm font-medium text-gray-600 mb-2">
                    <x-heroicon-o-tag class="w-4 h-4 text-gray-400"/>
                    Nom de la catégorie <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="cat_name"
                       name="name"
                       value="{{ old('name', $cat?->name ?? '') }}"
                       required
                       maxlength="150"
                       class="input-style @error('name') border-red-400 @enderror">
                @error('name')
                <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>

            {{-- SLUG (lecture seule) --}}
            <div>
                <label class="flex items-center gap-2 text-sm font-medium text-gray-600 mb-2">
                    <x-heroicon-o-link class="w-4 h-4 text-gray-400"/>
                    Slug
                </label>
                <input type="text"
                       value="{{ $cat?->slug ?? 'Généré automatiquement après enregistrement' }}"
                       class="input-style bg-gray-50 cursor-not-allowed text-gray-400"
                       disabled
                       readonly>
                <p class="text-xs text-gray-400 mt-1">Généré automatiquement depuis le nom.</p>
            </div>

            {{-- CATÉGORIE PARENT --}}
            <div>
                <label for="parent_id"
                       class="flex items-center gap-2 text-sm font-medium text-gray-600 mb-2">
                    <x-heroicon-o-squares-2x2 class="w-4 h-4 text-gray-400"/>
                    Catégorie parent
                </label>
                <select id="parent_id"
                        name="parent_id"
                        class="input-style">
                    <option value="">Aucune (catégorie principale)</option>
                    @foreach($parents ?? [] as $parent)
                    {{-- ✅ Exclure la catégorie elle-même en mode édition --}}
                    @if(!$isEdit || $parent->id !== $cat?->id)
                    <option value="{{ $parent->id }}"
                            @selected(old('parent_id', $cat?->parent_id) == $parent->id)>
                        {{ e($parent->name) }}
                    </option>
                    @endif
                    @endforeach
                </select>
                @error('parent_id')
                <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>

            {{-- POSITION --}}
            <div>
                <label for="position"
                       class="flex items-center gap-2 text-sm font-medium text-gray-600 mb-2">
                    <x-heroicon-o-bars-3 class="w-4 h-4 text-gray-400"/>
                    Position
                </label>
                <input type="number"
                       id="position"
                       name="position"
                       value="{{ old('position', $cat?->position ?? '') }}"
                       min="0"
                       placeholder="Auto si vide"
                       class="input-style">
                @error('position')
                <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>

        </div>

        {{-- DESCRIPTION --}}
        <div>
            <label for="description"
                   class="flex items-center gap-2 text-sm font-medium text-gray-600 mb-2">
                <x-heroicon-o-document-text class="w-4 h-4 text-gray-400"/>
                Description
            </label>
            <textarea id="description"
                      name="description"
                      rows="4"
                      maxlength="1000"
                      class="input-style resize-none">{{ old('description', $cat?->description ?? '') }}</textarea>
            @error('description')
            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
            @enderror
        </div>

    </div>


    {{-- ================= IMAGE ================= --}}
    <div x-data="{ imageUrl: '{{ $existingImage }}' }"
         class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8 space-y-6">

        <div class="flex items-center gap-3">
            <x-heroicon-o-photo class="w-6 h-6 text-indigo-600"/>
            <h2 class="text-xl font-semibold text-gray-800">Image de la catégorie</h2>
        </div>

        {{-- ✅ Aperçu image existante --}}
        <div x-show="imageUrl" x-cloak class="flex justify-center mb-4">
            <img :src="imageUrl"
                 alt="Aperçu"
                 class="h-28 rounded-2xl shadow-lg object-cover">
        </div>

        <div class="relative border-2 border-dashed border-gray-300 rounded-2xl p-6 text-center
                    hover:border-indigo-400 transition">
            <x-heroicon-o-cloud-arrow-up class="w-10 h-10 mx-auto text-gray-400 mb-3"/>

            {{-- ✅ accept limité aux images --}}
            <input type="file"
                   name="image"
                   accept="image/jpeg,image/png,image/webp,image/gif"
                   @change="imageUrl = $event.target.files[0]
                       ? URL.createObjectURL($event.target.files[0])
                       : imageUrl"
                   class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">

            <p class="text-sm text-gray-500">
                Cliquez ou glissez une image ici
                <span class="text-xs text-gray-400 block mt-1">JPG, PNG, WEBP — max 2 Mo</span>
            </p>
        </div>

        @error('image')
        <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
        @enderror

    </div>


    {{-- ================= PARAMÈTRES ================= --}}
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">

        <div class="flex items-center gap-3 mb-6">
            <x-heroicon-o-cog-6-tooth class="w-6 h-6 text-indigo-600"/>
            <h2 class="text-xl font-semibold text-gray-800">Paramètres</h2>
        </div>

        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-700">Catégorie active</p>
                <p class="text-xs text-gray-500">Désactiver la catégorie la rend invisible</p>
            </div>

            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox"
                       name="is_active"
                       value="1"
                       class="sr-only peer"
                       {{ old('is_active', $cat?->is_active ?? true) ? 'checked' : '' }}>
                <div class="w-12 h-6 bg-gray-200 rounded-full peer
                            peer-checked:bg-indigo-600
                            after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                            after:bg-white after:rounded-full after:h-5 after:w-5
                            after:transition-all peer-checked:after:translate-x-full
                            shadow-inner">
                </div>
            </label>
        </div>

    </div>

</div>


{{-- ================= STYLES ================= --}}
<style>
.input-style {
    width: 100%;
    border-radius: 1rem;
    border: 1px solid #e5e7eb;
    padding: 0.5rem 1rem;
    transition: all 0.2s;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.input-style:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 2px rgba(99,102,241,0.2);
}
</style>