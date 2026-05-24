{{-- ✅ @csrf dans le form parent — pas ici --}}

@php
    $s = $supplier ?? null;
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- NOM --}}
    <div>
        <label for="supplier_name"
               class="block text-sm font-medium text-gray-600 mb-2">
            Nom <span class="text-red-500">*</span>
        </label>
        <input type="text"
               id="supplier_name"
               name="name"
               value="{{ old('name', $s?->name ?? '') }}"
               required
               maxlength="150"
               class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500
                      @error('name') border-red-400 @enderror">
        @error('name')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- CONTACT --}}
    <div>
        <label for="contact_person"
               class="block text-sm font-medium text-gray-600 mb-2">
            Personne de contact
        </label>
        <input type="text"
               id="contact_person"
               name="contact_person"
               value="{{ old('contact_person', $s?->contact_person ?? '') }}"
               maxlength="150"
               class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    {{-- TÉLÉPHONE --}}
    <div>
        <label for="phone"
               class="block text-sm font-medium text-gray-600 mb-2">Téléphone</label>
        <input type="text"
               id="phone"
               name="phone"
               value="{{ old('phone', $s?->phone ?? '') }}"
               maxlength="30"
               class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    {{-- EMAIL --}}
    <div>
        <label for="email"
               class="block text-sm font-medium text-gray-600 mb-2">Email</label>
        <input type="email"
               id="email"
               name="email"
               value="{{ old('email', $s?->email ?? '') }}"
               maxlength="200"
               class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500
                      @error('email') border-red-400 @enderror">
        @error('email')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- NIF --}}
    <div>
        <label for="nif"
               class="block text-sm font-medium text-gray-600 mb-2">NIF</label>
        <input type="text"
               id="nif"
               name="nif"
               value="{{ old('nif', $s?->nif ?? '') }}"
               maxlength="50"
               class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    {{-- RC --}}
    <div>
        <label for="rc"
               class="block text-sm font-medium text-gray-600 mb-2">RC</label>
        <input type="text"
               id="rc"
               name="rc"
               value="{{ old('rc', $s?->rc ?? '') }}"
               maxlength="50"
               class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    {{-- ADRESSE --}}
    <div class="md:col-span-2">
        <label for="address"
               class="block text-sm font-medium text-gray-600 mb-2">Adresse</label>
        <input type="text"
               id="address"
               name="address"
               value="{{ old('address', $s?->address ?? '') }}"
               maxlength="255"
               class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    {{-- VILLE --}}
    <div>
        <label for="city"
               class="block text-sm font-medium text-gray-600 mb-2">Ville</label>
        <input type="text"
               id="city"
               name="city"
               value="{{ old('city', $s?->city ?? '') }}"
               maxlength="100"
               class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    {{-- SOLDE INITIAL --}}
    <div>
        <label for="opening_balance"
               class="block text-sm font-medium text-gray-600 mb-2">Solde initial</label>
        <input type="number"
               id="opening_balance"
               name="opening_balance"
               step="0.01"
               min="0"
               value="{{ old('opening_balance', $s?->opening_balance ?? 0) }}"
               class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
        @error('opening_balance')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- STATUT --}}
    <div class="md:col-span-2">
        {{-- ✅ hidden input pour envoyer 0 si non coché --}}
        <input type="hidden" name="is_active" value="0">
        <label class="flex items-center gap-3 cursor-pointer">
            <input type="checkbox"
                   name="is_active"
                   value="1"
                   {{ old('is_active', $s?->is_active ?? true) ? 'checked' : '' }}
                   class="h-4 w-4 text-indigo-600 rounded border-gray-300">
            <div>
                <span class="text-sm font-medium text-gray-700">Fournisseur actif</span>
                <p class="text-xs text-gray-400">Désactiver rend le fournisseur invisible</p>
            </div>
        </label>
    </div>

</div>