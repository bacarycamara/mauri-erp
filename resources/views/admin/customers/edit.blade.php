<x-app-layout>

@can('edit customers')

<div class="max-w-5xl mx-auto space-y-10"
     x-data="{ loading: false }"
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 translate-y-6"
     x-transition:enter-end="opacity-100 translate-y-0">

    {{-- ================= HEADER ================= --}}
    <div class="flex justify-between items-start">

        <div class="flex items-start gap-3">
            <div class="p-3 bg-indigo-100 rounded-2xl">
                <x-heroicon-o-user-circle class="w-6 h-6 text-indigo-600"/>
            </div>
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl font-bold text-gray-800">Modifier Client</h1>

                    @if($customer->is_active)
                    <span class="inline-flex items-center gap-1 px-3 py-1 text-xs rounded-full bg-green-100 text-green-700 font-semibold">
                        <x-heroicon-o-check-circle class="w-3 h-3"/>
                        Actif
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1 px-3 py-1 text-xs rounded-full bg-gray-200 text-gray-600 font-semibold">
                        <x-heroicon-o-pause-circle class="w-3 h-3"/>
                        Inactif
                    </span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 mt-1">{{ e($customer->name) }}</p>
            </div>
        </div>

        <a href="{{ route('admin.customers.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 border rounded-xl hover:bg-gray-100 transition">
            <x-heroicon-o-arrow-left class="w-4 h-4"/>
            Retour
        </a>

    </div>


    {{-- ================= ALERTES ================= --}}
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


    {{-- ================= FORM ================= --}}
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-10">

        <form action="{{ route('admin.customers.update', $customer) }}"
              method="POST"
              class="space-y-10"
              @submit.prevent="loading = true; $el.submit()">
            @csrf
            @method('PUT')


            {{-- ================= INFOS GÉNÉRALES ================= --}}
            <div class="space-y-6">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-user class="w-5 h-5 text-indigo-600"/>
                    <h2 class="text-lg font-semibold text-gray-700">Informations Générales</h2>
                </div>

                <div class="grid md:grid-cols-2 gap-6">

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nom du client <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="name"
                               name="name"
                               value="{{ old('name', $customer->name) }}"
                               required
                               maxlength="150"
                               class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500
                                      @error('name') border-red-400 @enderror">
                        @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="contact_person" class="block text-sm font-medium text-gray-700 mb-2">
                            Personne de contact
                        </label>
                        <input type="text"
                               id="contact_person"
                               name="contact_person"
                               value="{{ old('contact_person', $customer->contact_person) }}"
                               maxlength="150"
                               class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email"
                               id="email"
                               name="email"
                               value="{{ old('email', $customer->email) }}"
                               maxlength="200"
                               class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500
                                      @error('email') border-red-400 @enderror">
                        @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
                        <input type="text"
                               id="phone"
                               name="phone"
                               value="{{ old('phone', $customer->phone) }}"
                               maxlength="30"
                               class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500">
                    </div>

                </div>
            </div>


            {{-- ================= INFORMATIONS FISCALES ================= --}}
            <div class="space-y-6">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-document-text class="w-5 h-5 text-indigo-600"/>
                    <h2 class="text-lg font-semibold text-gray-700">Informations Fiscales</h2>
                </div>
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label for="nif" class="block text-sm font-medium text-gray-700 mb-2">NIF</label>
                        <input type="text"
                               id="nif"
                               name="nif"
                               value="{{ old('nif', $customer->nif) }}"
                               maxlength="50"
                               class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="rc" class="block text-sm font-medium text-gray-700 mb-2">RC</label>
                        <input type="text"
                               id="rc"
                               name="rc"
                               value="{{ old('rc', $customer->rc) }}"
                               maxlength="50"
                               class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </div>


            {{-- ================= ADRESSE ================= --}}
            <div class="space-y-6">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-map-pin class="w-5 h-5 text-indigo-600"/>
                    <h2 class="text-lg font-semibold text-gray-700">Adresse</h2>
                </div>
                <div class="grid md:grid-cols-3 gap-6">
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Adresse</label>
                        <input type="text"
                               id="address"
                               name="address"
                               value="{{ old('address', $customer->address) }}"
                               maxlength="255"
                               class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-2">Ville</label>
                        <input type="text"
                               id="city"
                               name="city"
                               value="{{ old('city', $customer->city) }}"
                               maxlength="100"
                               class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="country" class="block text-sm font-medium text-gray-700 mb-2">Pays</label>
                        <input type="text"
                               id="country"
                               name="country"
                               value="{{ old('country', $customer->country) }}"
                               maxlength="100"
                               class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </div>


            {{-- ================= FINANCIER ================= --}}
            <div class="space-y-6">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-banknotes class="w-5 h-5 text-indigo-600"/>
                    <h2 class="text-lg font-semibold text-gray-700">Paramètres Financiers</h2>
                </div>
                <div class="grid md:grid-cols-2 gap-6">

                    <div>
                        <label for="opening_balance" class="block text-sm font-medium text-gray-700 mb-2">
                            Solde initial
                        </label>
                        <input type="number"
                               id="opening_balance"
                               name="opening_balance"
                               step="0.01"
                               min="0"
                               value="{{ old('opening_balance', $customer->opening_balance) }}"
                               class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500">
                        @error('opening_balance')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between mt-8 bg-gray-50 p-4 rounded-2xl">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Client actif</p>
                            <p class="text-xs text-gray-500">Désactiver rend le client invisible</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox"
                                   name="is_active"
                                   value="1"
                                   {{ old('is_active', $customer->is_active) ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer
                                        peer-checked:bg-indigo-600
                                        after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                        after:bg-white after:rounded-full after:h-5 after:w-5
                                        after:transition-all peer-checked:after:translate-x-full">
                            </div>
                        </label>
                    </div>

                </div>
            </div>


            {{-- ================= NOTES ================= --}}
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <textarea id="notes"
                          name="notes"
                          rows="3"
                          maxlength="1000"
                          class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500">{{ old('notes', $customer->notes) }}</textarea>
            </div>


            {{-- ================= ACTIONS ================= --}}
            <div class="flex justify-between items-center pt-8 border-t flex-wrap gap-4">

                {{-- Bouton suppression depuis l'édition --}}
                @can('delete customers')
                <form action="{{ route('admin.customers.destroy', $customer) }}"
                      method="POST"
                      onsubmit="return confirm('Supprimer définitivement ce client ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
                                   border border-red-300 text-red-600 hover:bg-red-50 transition">
                        <x-heroicon-o-trash class="w-4 h-4"/>
                        Supprimer
                    </button>
                </form>
                @endcannot
                @cannot('delete customers')
                <span></span>
                @endcannot

                <div class="flex gap-4">
                    <a href="{{ route('admin.customers.index') }}"
                       class="px-6 py-2.5 border rounded-xl hover:bg-gray-100 transition">
                        Annuler
                    </a>

                    <button type="submit"
                            :disabled="loading"
                            class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl
                                   hover:bg-indigo-700 shadow-md flex items-center gap-2
                                   disabled:opacity-70 disabled:cursor-not-allowed transition">

                        <x-heroicon-o-check class="w-4 h-4" x-show="!loading"/>

                        <svg x-show="loading"
                             x-cloak
                             class="animate-spin h-4 w-4 text-white"
                             xmlns="http://www.w3.org/2000/svg"
                             fill="none"
                             viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>

                        <span x-text="loading ? 'Mise à jour...' : 'Mettre à jour'"></span>

                    </button>
                </div>

            </div>

        </form>

    </div>

</div>

@else
<div class="flex flex-col items-center justify-center py-24 text-gray-400">
    <x-heroicon-o-lock-closed class="w-12 h-12 mb-4 text-gray-300"/>
    <p class="text-lg font-medium">Accès non autorisé</p>
</div>
@endcan

</x-app-layout>