<x-app-layout>

<div class="max-w-3xl mx-auto space-y-8">

    {{-- ================= HEADER ================= --}}
    <div class="flex justify-between items-center">

        <div class="flex items-center gap-3">
            <x-heroicon-o-arrows-right-left class="w-8 h-8 text-indigo-600"/>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    Nouvelle Transaction
                </h1>
                <p class="text-gray-600">
                    Caisse :
                    <span class="font-semibold">{{ $cashRegister->name }}</span>
                </p>
            </div>
        </div>

        <a href="{{ route('admin.cash-transactions.index',$cashRegister) }}"
           class="flex items-center gap-2 px-4 py-2 border rounded-xl hover:bg-gray-100 transition">
            <x-heroicon-o-arrow-left class="w-4 h-4"/>
            Retour
        </a>

    </div>


    {{-- ================= ALERT CAISSE FERMÉE ================= --}}
    @if(!$cashRegister->isOpen())
        <div class="bg-red-100 border-l-4 border-red-600 p-5 rounded-xl shadow flex items-start gap-3">
            <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-red-700 mt-1"/>
            <div>
                <p class="text-red-900 font-semibold">
                    Cette caisse est fermée.
                </p>
                <p class="text-red-700 text-sm">
                    Impossible d'ajouter une transaction.
                </p>
            </div>
        </div>
    @endif


    {{-- ================= FORM ================= --}}
    @if($cashRegister->isOpen())
    <form method="POST"
          action="{{ route('admin.cash-transactions.store') }}"
          class="bg-white p-8 rounded-2xl shadow-xl space-y-6 border border-gray-100">
        @csrf

        <input type="hidden"
               name="cash_register_id"
               value="{{ $cashRegister->id }}">

        {{-- TYPE --}}
        <div>
            <label class="flex items-center gap-2 text-sm font-semibold mb-2">
                <x-heroicon-o-adjustments-horizontal class="w-4 h-4 text-indigo-600"/>
                Type de transaction
            </label>

            <select name="type"
                    required
                    class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                <option value="in">Entrée (Dépôt)</option>
                <option value="out">Sortie (Retrait)</option>
            </select>
        </div>


        {{-- MONTANT --}}
        <div>
            <label class="flex items-center gap-2 text-sm font-semibold mb-2">
                <x-heroicon-o-currency-dollar class="w-4 h-4 text-green-600"/>
                Montant
            </label>

            <input type="number"
                   step="0.01"
                   name="amount"
                   required
                   class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                   placeholder="0.00">

            <p class="text-sm text-gray-500 mt-2 flex items-center gap-2">
                <x-heroicon-o-banknotes class="w-4 h-4 text-indigo-600"/>
                Solde actuel :
                <span class="font-semibold text-indigo-700">
                    {{ number_format($cashRegister->current_balance,2) }}
                    {{ company()?->currency }}
                </span>
            </p>
        </div>


        {{-- DESCRIPTION --}}
        <div>
            <label class="flex items-center gap-2 text-sm font-semibold mb-2">
                <x-heroicon-o-chat-bubble-left-ellipsis class="w-4 h-4 text-indigo-600"/>
                Description
            </label>

            <textarea name="description"
                      rows="3"
                      class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                      placeholder="Motif de la transaction..."></textarea>
        </div>


        {{-- BOUTONS --}}
        <div class="flex justify-end gap-4 pt-4">

            <a href="{{ route('admin.cash-transactions.index',$cashRegister) }}"
               class="flex items-center gap-2 px-6 py-3 border rounded-xl hover:bg-gray-100 transition">
                <x-heroicon-o-x-mark class="w-4 h-4"/>
                Annuler
            </a>

            <button type="submit"
                    class="flex items-center gap-2 px-6 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition transform hover:scale-105 shadow-lg">
                <x-heroicon-o-check class="w-4 h-4"/>
                Enregistrer
            </button>

        </div>

    </form>
    @endif


    {{-- ================= INFO BOX ================= --}}
    <div class="bg-indigo-50 border-l-4 border-indigo-500 p-6 rounded-xl shadow">

        <h3 class="font-semibold text-indigo-900 mb-3 flex items-center gap-2">
            <x-heroicon-o-information-circle class="w-5 h-5"/>
            Informations
        </h3>

        <ul class="text-sm text-indigo-800 space-y-2">
            <li class="flex items-start gap-2">
                <x-heroicon-o-arrow-trending-up class="w-4 h-4 mt-0.5"/>
                Une entrée augmente le solde de la caisse.
            </li>
            <li class="flex items-start gap-2">
                <x-heroicon-o-arrow-trending-down class="w-4 h-4 mt-0.5"/>
                Une sortie diminue le solde.
            </li>
            <li class="flex items-start gap-2">
                <x-heroicon-o-shield-check class="w-4 h-4 mt-0.5"/>
                Le système bloque automatiquement les sorties si le solde est insuffisant.
            </li>
            <li class="flex items-start gap-2">
                <x-heroicon-o-clock class="w-4 h-4 mt-0.5"/>
                Toutes les transactions sont historisées.
            </li>
        </ul>

    </div>

</div>

</x-app-layout>