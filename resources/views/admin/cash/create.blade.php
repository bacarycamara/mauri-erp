<x-app-layout>

<div class="max-w-3xl mx-auto space-y-8"
     x-data="{ previewBalance: 0 }"
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0">

    {{-- ================= HEADER ================= --}}
    <div class="flex justify-between items-center">

        <div class="flex items-center gap-3">
            <x-heroicon-o-banknotes class="w-7 h-7 text-indigo-600"/>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    Ouvrir une Nouvelle Caisse
                </h1>
                <p class="text-sm text-gray-500">
                    Initialiser une session de caisse
                </p>
            </div>
        </div>

        <a href="{{ route('admin.cash-registers.index') }}"
           class="flex items-center gap-2 px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-100 transition">
            <x-heroicon-o-arrow-left class="w-4 h-4"/>
            Retour
        </a>

    </div>


    {{-- ================= FORM ================= --}}
    <form action="{{ route('admin.cash-registers.open') }}"
          method="POST"
          class="bg-white p-8 rounded-3xl shadow-xl space-y-6 border border-gray-100">

        @csrf

        {{-- NOM --}}
        <div>
            <label class="flex items-center gap-2 text-sm font-medium mb-2">
                <x-heroicon-o-tag class="w-4 h-4 text-indigo-600"/>
                Nom de la caisse
            </label>

            <input type="text"
                   name="name"
                   required
                   class="w-full border rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                   placeholder="Ex: Caisse principale">

            @error('name')
                <p class="text-red-500 text-sm mt-1 flex items-center gap-1">
                    <x-heroicon-o-exclamation-circle class="w-4 h-4"/>
                    {{ $message }}
                </p>
            @enderror
        </div>


        {{-- SOLDE INITIAL --}}
        <div>
            <label class="flex items-center gap-2 text-sm font-medium mb-2">
                <x-heroicon-o-currency-dollar class="w-4 h-4 text-green-600"/>
                Solde initial
            </label>

            <input type="number"
                   name="opening_balance"
                   step="0.01"
                   min="0"
                   x-model="previewBalance"
                   required
                   class="w-full border rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                   placeholder="0.00">

            @error('opening_balance')
                <p class="text-red-500 text-sm mt-1 flex items-center gap-1">
                    <x-heroicon-o-exclamation-circle class="w-4 h-4"/>
                    {{ $message }}
                </p>
            @enderror
        </div>


        {{-- PREVIEW CARD --}}
        <div class="bg-indigo-50 border-l-4 border-indigo-400 p-5 rounded-xl">

            <div class="flex items-center gap-2 text-indigo-700 text-sm">
                <x-heroicon-o-eye class="w-4 h-4"/>
                Aperçu de la caisse
            </div>

            <p class="text-xl font-bold text-indigo-800 mt-3 flex items-center gap-2">
                <x-heroicon-o-banknotes class="w-5 h-5"/>
                Solde de départ :
                <span x-text="new Intl.NumberFormat().format(previewBalance)"></span>
                {{ company()?->currency }}
            </p>

        </div>


        {{-- INFO SECURITY --}}
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-5 rounded-xl text-sm text-yellow-800 flex items-start gap-3">
            <x-heroicon-o-exclamation-triangle class="w-5 h-5 mt-0.5"/>
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
                    class="flex items-center gap-2 px-6 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition hover:scale-105 shadow-md">
                <x-heroicon-o-lock-open class="w-4 h-4"/>
                Ouvrir la Caisse
            </button>

        </div>

    </form>

</div>

</x-app-layout>