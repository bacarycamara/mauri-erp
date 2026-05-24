<x-app-layout>

@can('create cash_transactions')

<div class="max-w-3xl mx-auto space-y-8">

    {{-- ================= HEADER ================= --}}
    <div class="flex justify-between items-center">
        <div class="flex items-center gap-3">
            <x-heroicon-o-arrows-right-left class="w-8 h-8 text-indigo-600"/>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Nouvelle Transaction</h1>
                <p class="text-gray-600">
                    Caisse :
                    <span class="font-semibold">{{ e($cashRegister->name) }}</span>
                </p>
            </div>
        </div>

        <a href="{{ route('admin.cash-transactions.index', ['cash_register_id' => $cashRegister->id]) }}"
           class="flex items-center gap-2 px-4 py-2 border rounded-xl hover:bg-gray-100 transition">
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

    {{-- ================= ALERT CAISSE FERMÉE ================= --}}
    @if(!$cashRegister->isOpen())
    <div class="bg-red-100 border-l-4 border-red-600 p-5 rounded-xl shadow flex items-start gap-3">
        <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-red-700 mt-1 flex-shrink-0"/>
        <div>
            <p class="text-red-900 font-semibold">Cette caisse est fermée.</p>
            <p class="text-red-700 text-sm">Impossible d'ajouter une transaction.</p>
        </div>
    </div>
    @endif


    {{-- ================= FORM ================= --}}
    @if($cashRegister->isOpen())
    <form method="POST"
          action="{{ route('admin.cash-transactions.store') }}"
          x-data="{ submitting: false }"
          @submit.prevent="submitting = true; $el.submit()"
          class="bg-white p-8 rounded-2xl shadow-xl space-y-6 border border-gray-100">
        @csrf

        {{-- ✅ ID caisse en hidden — ne jamais faire confiance au client mais valider côté serveur --}}
        <input type="hidden" name="cash_register_id" value="{{ $cashRegister->id }}">

        {{-- TYPE --}}
        <div>
            <label for="type"
                   class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
                <x-heroicon-o-adjustments-horizontal class="w-4 h-4 text-indigo-600"/>
                Type de transaction <span class="text-red-500">*</span>
            </label>
            <select id="type"
                    name="type"
                    required
                    class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:outline-none
                           @error('type') border-red-400 @enderror">
                <option value="in"  @selected(old('type') === 'in')>Entrée (Dépôt)</option>
                <option value="out" @selected(old('type') === 'out')>Sortie (Retrait)</option>
            </select>
            @error('type')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>


        {{-- MONTANT --}}
        <div>
            <label for="amount"
                   class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
                <x-heroicon-o-currency-dollar class="w-4 h-4 text-green-600"/>
                Montant <span class="text-red-500">*</span>
            </label>
            <input type="number"
                   id="amount"
                   name="amount"
                   step="0.01"
                   min="0.01"
                   required
                   value="{{ old('amount') }}"
                   placeholder="0.00"
                   class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:outline-none
                          @error('amount') border-red-400 @enderror">
            @error('amount')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror

            <p class="text-sm text-gray-500 mt-2 flex items-center gap-2">
                <x-heroicon-o-banknotes class="w-4 h-4 text-indigo-600"/>
                Solde actuel :
                <span class="font-semibold text-indigo-700">
                    {{ number_format($cashRegister->current_balance ?? 0, 2) }}
                    {{ company()?->currency }}
                </span>
            </p>
        </div>


        {{-- DESCRIPTION --}}
        <div>
            <label for="description"
                   class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
                <x-heroicon-o-chat-bubble-left-ellipsis class="w-4 h-4 text-indigo-600"/>
                Description
            </label>
            <textarea id="description"
                      name="description"
                      rows="3"
                      maxlength="500"
                      placeholder="Motif de la transaction..."
                      class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:outline-none
                             @error('description') border-red-400 @enderror">{{ old('description') }}</textarea>
            @error('description')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>


        {{-- ACTIONS --}}
        <div class="flex justify-end gap-4 pt-4">
            <a href="{{ route('admin.cash-transactions.index', ['cash_register_id' => $cashRegister->id]) }}"
               class="flex items-center gap-2 px-6 py-3 border rounded-xl hover:bg-gray-100 transition">
                <x-heroicon-o-x-mark class="w-4 h-4"/>
                Annuler
            </a>

            <button type="submit"
                    :disabled="submitting"
                    class="flex items-center gap-2 px-6 py-3 bg-green-600 text-white font-semibold rounded-xl
                           hover:bg-green-700 transition hover:scale-105 shadow-lg
                           disabled:opacity-50 disabled:cursor-not-allowed disabled:scale-100">
                <x-heroicon-o-check class="w-4 h-4"/>
                <span x-show="!submitting">Enregistrer</span>
                <span x-show="submitting" x-cloak>Enregistrement...</span>
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
                <x-heroicon-o-arrow-trending-up class="w-4 h-4 mt-0.5 flex-shrink-0"/>
                Une entrée augmente le solde de la caisse.
            </li>
            <li class="flex items-start gap-2">
                <x-heroicon-o-arrow-trending-down class="w-4 h-4 mt-0.5 flex-shrink-0"/>
                Une sortie diminue le solde.
            </li>
            <li class="flex items-start gap-2">
                <x-heroicon-o-shield-check class="w-4 h-4 mt-0.5 flex-shrink-0"/>
                Le système bloque automatiquement les sorties si le solde est insuffisant.
            </li>
            <li class="flex items-start gap-2">
                <x-heroicon-o-clock class="w-4 h-4 mt-0.5 flex-shrink-0"/>
                Toutes les transactions sont historisées.
            </li>
        </ul>
    </div>

</div>

@else
<div class="flex flex-col items-center justify-center py-24 text-gray-400">
    <x-heroicon-o-lock-closed class="w-12 h-12 mb-4 text-gray-300"/>
    <p class="text-lg font-medium">Accès non autorisé</p>
</div>
@endcan

</x-app-layout>