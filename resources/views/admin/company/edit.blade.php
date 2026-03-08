<x-app-layout>

<div class="max-w-6xl mx-auto space-y-12"
     x-data="{ preview: null }"
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 translate-y-6"
     x-transition:enter-end="opacity-100 translate-y-0">

    {{-- ================= HEADER ================= --}}
    <div class="flex items-center gap-4">
        <div class="p-4 bg-indigo-600 rounded-2xl shadow-lg">
            <x-heroicon-o-building-office class="w-8 h-8 text-white"/>
        </div>
        <div>
            <h1 class="text-3xl font-bold text-gray-800">
                Paramètres Entreprise
            </h1>
            <p class="text-gray-500 text-sm">
                Configuration générale de votre ERP
            </p>
        </div>
    </div>

    {{-- SUCCESS --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-2xl shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST"
          action="{{ route('admin.company.update') }}"
          enctype="multipart/form-data"
          class="space-y-10">

        @csrf
        @method('PUT')

        {{-- ================= CARD PRINCIPALE ================= --}}
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-10 space-y-12">

            {{-- ================= LOGO ================= --}}
            <div class="flex items-center gap-8">

                <div class="relative">
                    <img :src="preview ?? '{{ $company->logo_url }}'"
                         class="w-32 h-32 object-cover rounded-2xl shadow-lg border">

                    <div class="absolute bottom-2 right-2 bg-indigo-600 text-white p-2 rounded-full shadow">
                        <x-heroicon-o-camera class="w-4 h-4"/>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">
                        Logo entreprise
                    </label>

                    <input type="file"
                           name="logo"
                           @change="preview = URL.createObjectURL($event.target.files[0])"
                           class="border rounded-xl px-4 py-2 text-sm">

                    <p class="text-xs text-gray-400 mt-1">
                        Format PNG/JPG — Max 2MB
                    </p>
                </div>

            </div>

            {{-- ================= INFORMATIONS GENERALES ================= --}}
            <div class="space-y-6">
                <h2 class="text-lg font-semibold text-gray-700 border-b pb-2">
                    Informations générales
                </h2>

                <div class="grid md:grid-cols-2 gap-6">

                    <x-input label="Nom entreprise" name="name" :value="$company->name" required />
                    <x-input label="Email" name="email" type="email" :value="$company->email" />
                    <x-input label="Téléphone" name="phone" :value="$company->phone" />
                    <x-input label="Site Web" name="website" :value="$company->website" />
                    <x-input label="Adresse" name="address" :value="$company->address" />
                    <x-input label="Ville" name="city" :value="$company->city" />
                    <x-input label="Pays" name="country" :value="$company->country" />

                </div>
            </div>

            {{-- ================= FISCAL ================= --}}
            <div class="space-y-6">
                <h2 class="text-lg font-semibold text-gray-700 border-b pb-2">
                    Informations fiscales
                </h2>

                <div class="grid md:grid-cols-3 gap-6">
                    <x-input label="NIF" name="nif" :value="$company->nif" />
                    <x-input label="RC" name="rc" :value="$company->rc" />
                    <x-input label="TVA par défaut (%)" 
                             name="default_vat"
                             type="number"
                             step="0.01"
                             :value="$company->default_vat" />
                </div>
            </div>

            {{-- ================= FACTURATION ================= --}}
            <div class="space-y-6">
                <h2 class="text-lg font-semibold text-gray-700 border-b pb-2">
                    Paramètres de facturation
                </h2>

                <div class="grid md:grid-cols-3 gap-6">
                    <x-input label="Devise" name="currency" :value="$company->currency" />
                    <x-input label="Préfixe facture" name="invoice_prefix" :value="$company->invoice_prefix" />
                    <x-input label="Format facture" name="invoice_format" :value="$company->invoice_format" />
                </div>

                <div class="grid md:grid-cols-2 gap-6 items-end">

                    <x-input label="Compteur actuel"
                             name="invoice_counter"
                             :value="$company->invoice_counter"
                             readonly />

                    <form method="POST"
                          action="{{ route('admin.company.reset-invoice') }}">
                        @csrf
                        <button type="submit"
                                onclick="return confirm('Réinitialiser le compteur de factures ?')"
                                class="bg-yellow-500 text-white px-6 py-3 rounded-xl hover:bg-yellow-600 transition shadow">
                            Réinitialiser compteur
                        </button>
                    </form>

                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Pied de page facture
                    </label>

                    <textarea name="invoice_footer"
                              class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                              rows="3">{{ $company->invoice_footer }}</textarea>
                </div>

            </div>

            {{-- ================= BANQUE ================= --}}
            <div class="space-y-6">
                <h2 class="text-lg font-semibold text-gray-700 border-b pb-2">
                    Informations bancaires
                </h2>

                <x-input label="Compte bancaire"
                         name="bank_account"
                         :value="$company->bank_account" />
            </div>

            {{-- ================= STATUS ================= --}}
            <div class="flex items-center justify-between border-t pt-6">

                <div class="flex items-center gap-3">
                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           {{ $company->is_active ? 'checked' : '' }}
                           class="w-5 h-5 text-indigo-600 rounded">

                    <label class="text-sm font-medium text-gray-700">
                        Entreprise active
                    </label>
                </div>

                <button type="submit"
                        class="bg-indigo-600 text-white px-8 py-3 rounded-xl shadow-lg hover:bg-indigo-700 transition">
                    Enregistrer les modifications
                </button>

            </div>

        </div>

    </form>

</div>

</x-app-layout>