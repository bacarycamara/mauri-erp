<x-app-layout>

@can('open cash_registers')

<div class="max-w-3xl mx-auto space-y-8"
     x-data="{ previewBalance: 0, submitting: false }"
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0">

    {{-- ================= HEADER ================= --}}
    <div class="flex justify-between items-center">

        <div class="flex items-center gap-3">
            <x-heroicon-o-banknotes class="w-7 h-7 text-indigo-600"/>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Ouvrir une Nouvelle Caisse</h1>
                <p class="text-sm text-gray-500">Initialiser une session de caisse</p>
            </div>
        </div>

        <a href="{{ route('admin.cash-registers.index') }}"
           class="flex items-center gap-2 px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-100 transition">
            <x-heroicon-o-arrow-left class="w-4 h-4"/>
            Retour
        </a>

    </div>


    {{-- ================= ALERTES ================= --}}
    @if(session('error'))
    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-xl text-red-700 text-sm">
        {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-xl">
        <ul class="text-red-700 text-sm space-y-1">
            @foreach($errors->all() as $error)
                <li>• {{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif


    {{-- ================= FORM ================= --}}
    <form action="{{ route('admin.cash-registers.open') }}"
          method="POST"
          @submit.prevent="submitting = true; $el.submit()"
          class="bg-white p-8 rounded-3xl shadow-xl space-y-6 border border-gray-100">

        @csrf

        {{-- NOM --}}
        <div>
            <label for="cash_name"
                   class="flex items-center gap-2 text-sm font-medium text-gray-700 mb-2">
                <x-heroicon-o-tag class="w-4 h-4 text-indigo-600"/>
                Nom de la caisse <span class="text-red-500">*</span>
            </label>
            <input type="text"
                   id="cash_name"
                   name="name"
                   required
                   maxlength="100"
                   value="{{ old('name') }}"
                   placeholder="Ex: Caisse principale"
                   class="w-full border rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none
                          @error('name') border-red-400 @enderror">
            @error('name')
            <p class="text-red-500 text-sm mt-1 flex items-center gap-1">
                <x-heroicon-o-exclamation-circle class="w-4 h-4"/>
                {{ $message }}
            </p>
            @enderror
        </div>


        {{-- SOLDE INITIAL --}}
        <div>
            <label for="opening_balance"
                   class="flex items-center gap-2 text-sm font-medium text-gray-700 mb-2">
                <x-heroicon-o-currency-dollar class="w-4 h-4 text-green-600"/>
                Solde initial <span class="text-red-500">*</span>
            </label>
            <input type="number"
                   id="opening_balance"
                   name="opening_balance"
                   step="0.01"
                   min="0"
                   required
                   x-model.number="previewBalance"
                   value="{{ old('opening_balance', 0) }}"
                   placeholder="0.00"
                   class="w-full border rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none
                          @error('opening_balance') border-red-400 @enderror">
            @error('opening_balance')
            <p class="text-red-500 text-sm mt-1 flex items-center gap-1">
                <x-heroicon-o-exclamation-circle class="w-4 h-4"/>
                {{ $message }}
            </p>
            @enderror
        </div>


        {{-- APERÇU --}}
        <div class="bg-indigo-50 border-l-4 border-indigo-400 p-5 rounded-xl">
            <div class="flex items-center gap-2 text-indigo-700 text-sm">
                <x-heroicon-o-eye class="w-4 h-4"/>
                Aperçu de la caisse
            </div>
            <p class="text-xl font-bold text-indigo-800 mt-3 flex items-center gap-2">
                <x-heroicon-o-banknotes class="w-5 h-5"/>
                Solde de départ :
                <span x-text="new Intl.NumberFormat('fr-FR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(previewBalance || 0)"></span>
                {{ company()?->currency }}
            </p>
        </div>


        {{-- AVERTISSEMENT --}}
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-5 rounded-xl text-sm text-yellow-800 flex items-start gap-3">
            <x-heroicon-o-exclamation-triangle class="w-5 h-5 mt-0.5 flex-shrink-0"/>
            <div>
                Une seule caisse peut être ouverte à la fois.
                Assurez-vous qu'aucune autre caisse n'est active.
            </div>
        </div>


        {{-- ACTIONS --}}
        <div class="flex justify-end gap-4 pt-4">

            <a href="{{ route('admin.cash-registers.index') }}"
               class="flex items-center gap-2 px-5 py-2 border rounded-xl hover:bg-gray-100 transition">
                <x-heroicon-o-x-mark class="w-4 h-4"/>
                Annuler
            </a>

            <button type="submit"
                    :disabled="submitting"
                    class="flex items-center gap-2 px-6 py-2 bg-green-600 text-white rounded-xl
                           hover:bg-green-700 transition hover:scale-105 shadow-md
                           disabled:opacity-50 disabled:cursor-not-allowed disabled:scale-100">
                <x-heroicon-o-lock-open class="w-4 h-4"/>
                <span x-show="!submitting">Ouvrir la Caisse</span>
                <span x-show="submitting" x-cloak>Ouverture...</span>
            </button>

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