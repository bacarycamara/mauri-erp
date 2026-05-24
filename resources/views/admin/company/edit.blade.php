<x-app-layout>

@can('edit company')

<div class="max-w-6xl mx-auto space-y-12"
     x-data="{ preview: null, submitting: false }"
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 translate-y-6"
     x-transition:enter-end="opacity-100 translate-y-0">

    {{-- ================= HEADER ================= --}}
    <div class="flex items-center gap-4">
        <div class="p-4 bg-indigo-600 rounded-2xl shadow-lg">
            <x-heroicon-o-building-office class="w-8 h-8 text-white"/>
        </div>
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Paramètres Entreprise</h1>
            <p class="text-gray-500 text-sm">Configuration générale de votre ERP</p>
        </div>
    </div>


    {{-- ================= ALERTES ================= --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-2xl shadow-sm flex items-center gap-2">
        <x-heroicon-o-check-circle class="w-5 h-5 flex-shrink-0"/>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-2xl shadow-sm flex items-center gap-2">
        <x-heroicon-o-exclamation-circle class="w-5 h-5 flex-shrink-0"/>
        {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-2xl">
        <div class="flex items-center gap-2 mb-3">
            <x-heroicon-o-exclamation-triangle class="w-5 h-5 flex-shrink-0"/>
            <p class="font-semibold">Veuillez corriger les erreurs :</p>
        </div>
        <ul class="list-disc list-inside text-sm space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif


    {{-- ================= FORMULAIRE PRINCIPAL ================= --}}
    <form method="POST"
          action="{{ route('admin.company.update') }}"
          enctype="multipart/form-data"
          @submit.prevent="submitting = true; $el.submit()"
          class="space-y-10">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-10 space-y-12">

            {{-- ================= LOGO ================= --}}
            <div class="flex items-center gap-8 flex-wrap">

                <div class="relative flex-shrink-0">
                    {{-- ✅ :src avec fallback — jamais de chemin brut DB dans src --}}
                    <img :src="preview ?? '{{ $company->logo_url }}'"
                         alt="Logo entreprise"
                         class="w-32 h-32 object-cover rounded-2xl shadow-lg border">
                    <div class="absolute bottom-2 right-2 bg-indigo-600 text-white p-2 rounded-full shadow pointer-events-none">
                        <x-heroicon-o-camera class="w-4 h-4"/>
                    </div>
                </div>

                <div>
                    <label for="logo" class="block text-sm font-semibold mb-2">
                        Logo entreprise
                    </label>
                    {{-- ✅ accept limité aux images --}}
                    <input type="file"
                           id="logo"
                           name="logo"
                           accept="image/jpeg,image/png,image/webp"
                           @change="preview = $event.target.files[0]
                               ? URL.createObjectURL($event.target.files[0])
                               : preview"
                           class="border rounded-xl px-4 py-2 text-sm">
                    <p class="text-xs text-gray-400 mt-1">Format PNG/JPG/WEBP — Max 2 Mo</p>
                    @error('logo')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>


            {{-- ================= INFORMATIONS GÉNÉRALES ================= --}}
            <div class="space-y-6">
                <h2 class="text-lg font-semibold text-gray-700 border-b pb-2">
                    Informations générales
                </h2>
                <div class="grid md:grid-cols-2 gap-6">
                    <x-input label="Nom entreprise"  name="name"    :value="old('name',    $company->name)"    required/>
                    <x-input label="Email"           name="email"   :value="old('email',   $company->email)"   type="email"/>
                    <x-input label="Téléphone"       name="phone"   :value="old('phone',   $company->phone)"/>
                    <x-input label="Site Web"        name="website" :value="old('website', $company->website)"/>
                    <x-input label="Adresse"         name="address" :value="old('address', $company->address)"/>
                    <x-input label="Ville"           name="city"    :value="old('city',    $company->city)"/>
                    <x-input label="Pays"            name="country" :value="old('country', $company->country)"/>
                </div>
            </div>


            {{-- ================= FISCAL ================= --}}
            <div class="space-y-6">
                <h2 class="text-lg font-semibold text-gray-700 border-b pb-2">
                    Informations fiscales
                </h2>
                <div class="grid md:grid-cols-3 gap-6">
                    <x-input label="NIF"               name="nif"         :value="old('nif',         $company->nif)"/>
                    <x-input label="RC"                name="rc"          :value="old('rc',          $company->rc)"/>
                    <x-input label="TVA par défaut (%)"
                             name="default_vat"
                             type="number"
                             step="0.01"
                             min="0"
                             max="100"
                             :value="old('default_vat', $company->default_vat)"/>
                </div>
            </div>


            {{-- ================= FACTURATION ================= --}}
            <div class="space-y-6">
                <h2 class="text-lg font-semibold text-gray-700 border-b pb-2">
                    Paramètres de facturation
                </h2>

                <div class="grid md:grid-cols-3 gap-6">
                    <x-input label="Devise"          name="currency"       :value="old('currency',       $company->currency)"/>
                    <x-input label="Préfixe facture" name="invoice_prefix" :value="old('invoice_prefix', $company->invoice_prefix)"/>
                    <x-input label="Format facture"  name="invoice_format" :value="old('invoice_format', $company->invoice_format)"/>
                </div>

                <div class="grid md:grid-cols-2 gap-6 items-end">

                    <x-input label="Compteur actuel"
                             name="invoice_counter"
                             :value="$company->invoice_counter"
                             readonly/>

                    {{-- ✅ Formulaire imbriqué séparé — ne pas imbriquer dans le form principal --}}
                    @can('reset invoice_counter')
                    <div>
                        <form method="POST"
                              action="{{ route('admin.company.reset-invoice') }}"
                              onsubmit="return confirm('Réinitialiser le compteur de factures ? Cette action est irréversible.')">
                            @csrf
                            <button type="submit"
                                    class="w-full bg-yellow-500 text-white px-6 py-3 rounded-xl
                                           hover:bg-yellow-600 transition shadow">
                                Réinitialiser compteur
                            </button>
                        </form>
                    </div>
                    @endcan

                </div>

                <div>
                    <label for="invoice_footer" class="block text-sm font-medium text-gray-700 mb-1">
                        Pied de page facture
                    </label>
                    <textarea id="invoice_footer"
                              name="invoice_footer"
                              rows="3"
                              maxlength="500"
                              class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:outline-none">{{ old('invoice_footer', $company->invoice_footer) }}</textarea>
                    @error('invoice_footer')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>


            {{-- ================= BANQUE ================= --}}
            <div class="space-y-6">
                <h2 class="text-lg font-semibold text-gray-700 border-b pb-2">
                    Informations bancaires
                </h2>
                <x-input label="Compte bancaire"
                         name="bank_account"
                         :value="old('bank_account', $company->bank_account)"/>
            </div>


            {{-- ================= STATUT + SUBMIT ================= --}}
            <div class="flex items-center justify-between border-t pt-6 flex-wrap gap-4">

                <div class="flex items-center gap-3">
                    <input type="checkbox"
                           id="is_active"
                           name="is_active"
                           value="1"
                           {{ old('is_active', $company->is_active) ? 'checked' : '' }}
                           class="w-5 h-5 text-indigo-600 rounded">
                    <label for="is_active" class="text-sm font-medium text-gray-700">
                        Entreprise active
                    </label>
                </div>

                <button type="submit"
                        :disabled="submitting"
                        class="bg-indigo-600 text-white px-8 py-3 rounded-xl shadow-lg
                               hover:bg-indigo-700 transition
                               disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!submitting">Enregistrer les modifications</span>
                    <span x-show="submitting" x-cloak>Enregistrement...</span>
                </button>

            </div>

        </div>

    </form>

</div>

@else
<div class="flex flex-col items-center justify-center py-24 text-gray-400">
    <x-heroicon-o-lock-closed class="w-12 h-12 mb-4 text-gray-300"/>
    <p class="text-lg font-medium">Accès non autorisé</p>
</div>
@endcan

</x-app-layout>