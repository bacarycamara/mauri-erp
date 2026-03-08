@csrf

<div class="space-y-10">

    {{-- ================= INFORMATIONS ================= --}}
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8 space-y-8">

        <div class="flex items-center gap-3">
            <x-heroicon-o-folder class="w-6 h-6 text-indigo-600"/>
            <h2 class="text-xl font-semibold text-gray-800">
                Informations générales
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

            {{-- NOM --}}
            <div>
                <label class="flex items-center gap-2 text-sm font-medium text-gray-600 mb-2">
                    <x-heroicon-o-tag class="w-4 h-4 text-gray-400"/>
                    Nom de la catégorie
                </label>

                <input type="text"
                       name="name"
                       value="{{ old('name', $category->name ?? '') }}"
                       class="input-style"
                       required>

                @error('name')
                    <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>

            {{-- SLUG --}}
            <div>
                <label class="flex items-center gap-2 text-sm font-medium text-gray-600 mb-2">
                    <x-heroicon-o-link class="w-4 h-4 text-gray-400"/>
                    Slug
                </label>

                <input type="text"
                       value="{{ $category->slug ?? 'Généré automatiquement après enregistrement' }}"
                       class="input-style bg-gray-50 cursor-not-allowed"
                       disabled>
            </div>

            {{-- PARENT --}}
            <div>
                <label class="flex items-center gap-2 text-sm font-medium text-gray-600 mb-2">
                    <x-heroicon-o-squares-2x2 class="w-4 h-4 text-gray-400"/>
                    Catégorie parent
                </label>

                <select name="parent_id" class="input-style">
                    <option value="">Aucune (catégorie principale)</option>

                    @foreach($parents ?? [] as $parent)
                        <option value="{{ $parent->id }}"
                            {{ old('parent_id', $category->parent_id ?? '') == $parent->id ? 'selected' : '' }}>
                            {{ $parent->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- POSITION --}}
            <div>
                <label class="flex items-center gap-2 text-sm font-medium text-gray-600 mb-2">
                    <x-heroicon-o-bars-3 class="w-4 h-4 text-gray-400"/>
                    Position
                </label>

                <input type="number"
                       name="position"
                       value="{{ old('position', $category->position ?? '') }}"
                       placeholder="Auto si vide"
                       class="input-style">
            </div>

        </div>

        {{-- DESCRIPTION --}}
        <div>
            <label class="flex items-center gap-2 text-sm font-medium text-gray-600 mb-2">
                <x-heroicon-o-document-text class="w-4 h-4 text-gray-400"/>
                Description
            </label>

            <textarea name="description"
                      rows="4"
                      class="input-style resize-none">{{ old('description', $category->description ?? '') }}</textarea>
        </div>

    </div>


    {{-- ================= IMAGE ================= --}}
    <div 
        x-data="{ imageUrl: '{{ !empty($category->image) ? asset('storage/'.$category->image) : '' }}' }"
        class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8 space-y-6"
    >

        <div class="flex items-center gap-3">
            <x-heroicon-o-photo class="w-6 h-6 text-indigo-600"/>
            <h2 class="text-xl font-semibold text-gray-800">
                Image de la catégorie
            </h2>
        </div>

        <div class="relative border-2 border-dashed border-gray-300 rounded-2xl p-6 text-center hover:border-indigo-400 transition">

            <x-heroicon-o-cloud-arrow-up class="w-10 h-10 mx-auto text-gray-400 mb-3"/>

            <input type="file"
                   name="image"
                   @change="imageUrl = URL.createObjectURL($event.target.files[0])"
                   class="absolute inset-0 opacity-0 cursor-pointer">

            <p class="text-sm text-gray-500">
                Cliquez ou glissez une image ici
            </p>
        </div>

        <div class="mt-6 flex justify-center" x-show="imageUrl">
            <img :src="imageUrl"
                 class="h-28 rounded-2xl shadow-lg object-cover transition duration-300">
        </div>

    </div>


    {{-- ================= STATUT ================= --}}
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">

        <div class="flex items-center gap-3 mb-6">
            <x-heroicon-o-cog-6-tooth class="w-6 h-6 text-indigo-600"/>
            <h2 class="text-xl font-semibold text-gray-800">
                Paramètres
            </h2>
        </div>

        <div class="flex items-center justify-between">

            <div>
                <p class="text-sm font-medium text-gray-700">
                    Catégorie active
                </p>
                <p class="text-xs text-gray-500">
                    Désactiver la catégorie la rend invisible
                </p>
            </div>

            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox"
                       name="is_active"
                       value="1"
                       class="sr-only peer"
                       {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}>

                <div class="w-12 h-6 bg-gray-200 rounded-full peer
                            peer-checked:bg-indigo-600
                            after:content-['']
                            after:absolute after:top-[2px] after:left-[2px]
                            after:bg-white after:rounded-full
                            after:h-5 after:w-5 after:transition-all
                            peer-checked:after:translate-x-full shadow-inner">
                </div>
            </label>

        </div>

    </div>

</div>


{{-- ================= INPUT STYLE ================= --}}
<style>
.input-style {
    @apply w-full rounded-2xl border-gray-200
    focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
    shadow-sm transition duration-200 px-4 py-2;
}
</style>