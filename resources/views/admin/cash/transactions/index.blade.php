<x-app-layout>

@can('view cash_transactions')

@php
    $currency = company()?->currency ?? '';
@endphp

<div class="max-w-7xl mx-auto space-y-8"
     x-data="cashPage()"
     x-init="init()"
     x-cloak>

    {{-- ================= HEADER ================= --}}
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-6">

        <div>
            <div class="flex items-center gap-4 flex-wrap">
                <x-heroicon-o-arrows-right-left class="w-8 h-8 text-indigo-600 flex-shrink-0"/>
                <h1 class="text-3xl font-bold text-gray-900">
                    @if($cashRegister)
                        Transactions — {{ e($cashRegister->name) }}
                    @else
                        Toutes les Transactions
                    @endif
                </h1>

                @if($cashRegister)
                <span class="inline-flex items-center gap-2 px-3 py-1 text-xs font-semibold rounded-full"
                      :class="statusClass">
                    <x-heroicon-o-check-circle class="w-3 h-3"/>
                    <span x-text="statusLabel"></span>
                </span>
                @endif
            </div>
            <p class="text-gray-500 text-sm mt-1">Gestion des mouvements de caisse</p>
        </div>

        <div class="flex flex-wrap gap-3">

            @if($cashRegister)

                @can('export cash_transactions')
                <a href="{{ route('admin.cash-transactions.pdf', $cashRegister) }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl shadow hover:bg-indigo-700 transition hover:scale-105">
                    <x-heroicon-o-document-text class="w-4 h-4"/>
                    PDF
                </a>
                @endcan

                @can('print cash_transactions')
                <a href="{{ route('admin.cash-transactions.print', $cashRegister) }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="flex items-center gap-2 px-4 py-2 bg-gray-800 text-white rounded-xl shadow hover:bg-black transition hover:scale-105">
                    <x-heroicon-o-printer class="w-4 h-4"/>
                    Imprimer
                </a>
                @endcan

            @endif

            @can('create cash_transactions')
            @if($cashRegister && $cashRegister->isOpen())
            <button @click="showModal = true"
                    class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-xl shadow hover:bg-green-700 transition hover:scale-105">
                <x-heroicon-o-plus class="w-4 h-4"/>
                Nouvelle Transaction
            </button>
            @endif
            @endcan

        </div>
    </div>


    {{-- ================= STATS ================= --}}
    <div class="grid md:grid-cols-3 gap-6">

        <div class="bg-green-50 border border-green-200 p-6 rounded-2xl shadow">
            <p class="text-sm font-semibold text-green-700">Total Entrées</p>
            <p class="text-2xl font-bold text-green-900 mt-2"
               x-text="formatCurrency(totalIn)"></p>
        </div>

        <div class="bg-red-50 border border-red-200 p-6 rounded-2xl shadow">
            <p class="text-sm font-semibold text-red-700">Total Sorties</p>
            <p class="text-2xl font-bold text-red-900 mt-2"
               x-text="formatCurrency(totalOut)"></p>
        </div>

        <div class="bg-indigo-700 text-white p-6 rounded-2xl shadow">
            <p class="text-sm opacity-80">Solde Actuel</p>
            <p class="text-3xl font-bold mt-2"
               x-text="formatCurrency(animatedBalance)"></p>
        </div>

    </div>


    {{-- ================= TABLE ================= --}}
    <div class="bg-white rounded-2xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">

                <thead class="bg-gray-100 text-gray-800 font-semibold text-xs uppercase">
                <tr>
                    @if(!$cashRegister)
                    <th class="px-6 py-3 text-left">Caisse</th>
                    @endif
                    <th class="px-6 py-3 text-left">Référence</th>
                    <th class="px-6 py-3 text-left">Type</th>
                    <th class="px-6 py-3 text-left">Montant</th>
                    <th class="px-6 py-3 text-left">Source</th>
                    <th class="px-6 py-3 text-left">Description</th>
                    <th class="px-6 py-3 text-left">Date</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                @forelse($transactions as $transaction)
                <tr class="hover:bg-gray-50 transition">

                    @if(!$cashRegister)
                    <td class="px-6 py-4 text-gray-600">
                        {{ e($transaction->cashRegister?->name ?? '-') }}
                    </td>
                    @endif

                    <td class="px-6 py-4 font-semibold text-indigo-600">
                        {{ e($transaction->reference ?? '-') }}
                    </td>

                    <td class="px-6 py-4">
                        @if($transaction->type === 'in')
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                            Entrée
                        </span>
                        @else
                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">
                            Sortie
                        </span>
                        @endif
                    </td>

                    <td class="px-6 py-4 font-bold">
                        {{ $transaction->formatted_amount }}
                    </td>

                    <td class="px-6 py-4 text-gray-600">
                        {{ e($transaction->source_label ?? '-') }}
                    </td>

                    <td class="px-6 py-4 text-gray-600">
                        {{ e($transaction->description ?? '-') }}
                    </td>

                    <td class="px-6 py-4 text-gray-600">
                        {{ $transaction->created_at?->format('d/m/Y H:i') ?? '-' }}
                    </td>

                    <td class="px-6 py-4 text-right">
                        @can('delete cash_transactions')
                        @if($transaction->cashRegister?->isOpen())
                        <form method="POST"
                              action="{{ route('admin.cash-transactions.destroy', $transaction) }}"
                              onsubmit="return confirm('Supprimer cette transaction ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="text-red-600 hover:text-red-800 transition"
                                    title="Supprimer">
                                <x-heroicon-o-trash class="w-5 h-5"/>
                            </button>
                        </form>
                        @endif
                        @endcan
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="{{ $cashRegister ? 7 : 8 }}"
                        class="text-center py-12 text-gray-400">
                        <x-heroicon-o-inbox class="w-10 h-10 mx-auto mb-3 text-gray-300"/>
                        Aucune transaction trouvée
                    </td>
                </tr>
                @endforelse

                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $transactions->withQueryString()->links() }}
    </div>


    {{-- ================= MODAL NOUVELLE TRANSACTION ================= --}}
    @if($cashRegister && $cashRegister->isOpen())
    @can('create cash_transactions')
    <div x-show="showModal"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
         @keydown.escape.window="showModal = false">

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.outside="showModal = false">

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-5 border-b">
                <div class="flex items-center gap-3">
                    <x-heroicon-o-arrows-right-left class="w-6 h-6 text-indigo-600"/>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Nouvelle Transaction</h2>
                        <p class="text-xs text-gray-500">Caisse : <span class="font-semibold">{{ e($cashRegister->name) }}</span></p>
                    </div>
                </div>
                <button @click="showModal = false"
                        class="text-gray-400 hover:text-gray-600 transition rounded-lg p-1 hover:bg-gray-100">
                    <x-heroicon-o-x-mark class="w-5 h-5"/>
                </button>
            </div>

            {{-- Alertes --}}
            @if(session('error'))
            <div class="mx-6 mt-4 bg-red-50 border-l-4 border-red-400 p-3 rounded-xl text-red-700 text-sm">
                {{ session('error') }}
            </div>
            @endif

            @if($errors->any())
            <div class="mx-6 mt-4 bg-red-50 border-l-4 border-red-400 p-3 rounded-xl">
                <ul class="text-red-700 text-sm space-y-1">
                    @foreach($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Formulaire --}}
            <form method="POST"
                  action="{{ route('admin.cash-transactions.store') }}"
                  x-data="{ submitting: false }"
                  @submit.prevent="submitting = true; $el.submit()"
                  class="px-6 py-5 space-y-5">
                @csrf
                <input type="hidden" name="cash_register_id" value="{{ $cashRegister->id }}">

                {{-- TYPE --}}
                <div>
                    <label for="modal_type"
                           class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
                        <x-heroicon-o-adjustments-horizontal class="w-4 h-4 text-indigo-600"/>
                        Type de transaction <span class="text-red-500">*</span>
                    </label>
                    <select id="modal_type"
                            name="type"
                            required
                            class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:outline-none @error('type') border-red-400 @enderror">
                        <option value="in"  @selected(old('type') === 'in')>Entrée (Dépôt)</option>
                        <option value="out" @selected(old('type') === 'out')>Sortie (Retrait)</option>
                    </select>
                    @error('type')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- MONTANT --}}
                <div>
                    <label for="modal_amount"
                           class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
                        <x-heroicon-o-currency-dollar class="w-4 h-4 text-green-600"/>
                        Montant <span class="text-red-500">*</span>
                    </label>
                    <input type="number"
                           id="modal_amount"
                           name="amount"
                           step="0.01"
                           min="0.01"
                           required
                           value="{{ old('amount') }}"
                           placeholder="0.00"
                           class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:outline-none @error('amount') border-red-400 @enderror">
                    @error('amount')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                        <x-heroicon-o-banknotes class="w-4 h-4 text-indigo-600"/>
                        Solde actuel :
                        <span class="font-semibold text-indigo-700">
                            {{ number_format($cashRegister->current_balance ?? 0, 2) }} {{ $currency }}
                        </span>
                    </p>
                </div>

                {{-- DESCRIPTION --}}
                <div>
                    <label for="modal_description"
                           class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
                        <x-heroicon-o-chat-bubble-left-ellipsis class="w-4 h-4 text-indigo-600"/>
                        Description
                    </label>
                    <textarea id="modal_description"
                              name="description"
                              rows="3"
                              maxlength="500"
                              placeholder="Motif de la transaction..."
                              class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:outline-none @error('description') border-red-400 @enderror">{{ old('description') }}</textarea>
                    @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ACTIONS --}}
                <div class="flex justify-end gap-3 pt-2 border-t">
                    <button type="button"
                            @click="showModal = false"
                            class="flex items-center gap-2 px-5 py-2.5 border rounded-xl hover:bg-gray-100 transition text-sm font-medium">
                        <x-heroicon-o-x-mark class="w-4 h-4"/>
                        Annuler
                    </button>
                    <button type="submit"
                            :disabled="submitting"
                            class="flex items-center gap-2 px-5 py-2.5 bg-green-600 text-white font-semibold rounded-xl
                                   hover:bg-green-700 transition shadow
                                   disabled:opacity-50 disabled:cursor-not-allowed text-sm">
                        <x-heroicon-o-check class="w-4 h-4"/>
                        <span x-show="!submitting">Enregistrer</span>
                        <span x-show="submitting" x-cloak>Enregistrement...</span>
                    </button>
                </div>

            </form>
        </div>
    </div>
    @endcan
    @endif

</div>

@else
<div class="flex flex-col items-center justify-center py-24 text-gray-400">
    <x-heroicon-o-lock-closed class="w-12 h-12 mb-4 text-gray-300"/>
    <p class="text-lg font-medium">Accès non autorisé</p>
</div>
@endcan


@push('scripts')
<script>
function cashPage() {
    return {
        showModal: {{ $errors->any() ? 'true' : 'false' }},

        totalIn:  {{ (float) ($totalIn  ?? 0) }},
        totalOut: {{ (float) ($totalOut ?? 0) }},
        balance:  {{ (float) ($cashRegister?->current_balance ?? 0) }},

        animatedBalance: 0,

        statusLabel: '{{ $cashRegister?->isOpen() ? "Ouverte" : "Fermée" }}',
        statusClass:  '{{ $cashRegister?->isOpen()
            ? "bg-green-100 text-green-700"
            : "bg-red-100 text-red-700" }}',

        init() {
            this.animateBalance();
        },

        animateBalance() {
            if (!this.balance || this.balance <= 0) {
                this.animatedBalance = 0;
                return;
            }
            let start = 0;
            let end   = this.balance;
            let step  = end / 40;
            let timer = setInterval(() => {
                start += step;
                if (start >= end) {
                    start = end;
                    clearInterval(timer);
                }
                this.animatedBalance = start;
            }, 20);
        },

        formatCurrency(v) {
            return new Intl.NumberFormat('fr-FR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(v || 0) + ' {{ $currency }}';
        }
    }
}
</script>
@endpush

</x-app-layout>