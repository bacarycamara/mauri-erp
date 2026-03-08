<x-app-layout>

<div class="max-w-7xl mx-auto space-y-10"
     x-data="cashShow()"
     x-init="init()"
     x-cloak>

    {{-- ================= HEADER ================= --}}
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-6">

        <div class="flex items-center gap-3">
            <x-heroicon-o-banknotes class="w-8 h-8 text-indigo-600"/>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    {{ $cashRegister->name }}
                </h1>
                <p class="text-sm text-gray-500">
                    Détail complet de la caisse
                </p>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">

            <a href="{{ route('admin.cash-registers.index') }}"
               class="flex items-center gap-2 px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-100 transition">
                <x-heroicon-o-arrow-left class="w-4 h-4"/>
                Retour
            </a>

            <a href="{{ route('admin.cash-registers.transactions.index',$cashRegister) }}"
               class="flex items-center gap-2 px-4 py-2 bg-indigo-100 text-indigo-700 rounded-xl hover:bg-indigo-200 transition">
                <x-heroicon-o-arrows-right-left class="w-4 h-4"/>
                Transactions
            </a>

            <a href="{{ route('admin.cash-registers.pdf',$cashRegister) }}"
               class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition">
                <x-heroicon-o-document-text class="w-4 h-4"/>
                Rapport PDF
            </a>

        </div>

    </div>


    {{-- ================= BADGE STATUT ================= --}}
    <div>
        <span class="inline-flex items-center gap-2 px-5 py-2 rounded-full text-sm font-semibold"
              :class="statusClass">
            <x-heroicon-o-check-circle class="w-4 h-4"/>
            <span x-text="statusLabel"></span>
        </span>
    </div>


    {{-- ================= SUMMARY CARDS ================= --}}
    <div class="grid md:grid-cols-4 gap-6">

        <div class="bg-white p-6 rounded-2xl shadow hover:shadow-xl transition">
            <div class="flex justify-between items-center">
                <p class="text-xs uppercase text-gray-500">Solde Initial</p>
                <x-heroicon-o-currency-dollar class="w-5 h-5 text-gray-400"/>
            </div>
            <p class="text-xl font-bold mt-2">
                {{ number_format($cashRegister->opening_balance,2) }}
                {{ company()?->currency }}
            </p>
        </div>

        <div class="bg-green-50 p-6 rounded-2xl shadow">
            <div class="flex justify-between items-center">
                <p class="text-xs uppercase text-green-700">Total Entrées</p>
                <x-heroicon-o-arrow-trending-up class="w-5 h-5 text-green-600"/>
            </div>
            <p class="text-xl font-bold mt-2 text-green-700"
               x-text="formatCurrency(animatedIn)">
            </p>
        </div>

        <div class="bg-red-50 p-6 rounded-2xl shadow">
            <div class="flex justify-between items-center">
                <p class="text-xs uppercase text-red-700">Total Sorties</p>
                <x-heroicon-o-arrow-trending-down class="w-5 h-5 text-red-600"/>
            </div>
            <p class="text-xl font-bold mt-2 text-red-700"
               x-text="formatCurrency(animatedOut)">
            </p>
        </div>

        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white p-6 rounded-2xl shadow">
            <div class="flex justify-between items-center">
                <p class="text-xs uppercase opacity-80">Solde Actuel</p>
                <x-heroicon-o-banknotes class="w-5 h-5 opacity-80"/>
            </div>
            <p class="text-3xl font-bold mt-2"
               x-text="formatCurrency(animatedBalance)">
            </p>
        </div>

    </div>


    {{-- ================= TIMELINE PREMIUM ================= --}}
    <div class="bg-white rounded-2xl shadow overflow-hidden">

        <div class="px-6 py-5 border-b flex justify-between items-center">
            <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                <x-heroicon-o-clock class="w-5 h-5"/>
                Historique des Transactions
            </h2>

            <span class="text-sm text-gray-500">
                {{ $cashRegister->transactions->count() }} mouvement(s)
            </span>
        </div>

        <div class="p-8 space-y-6 max-h-[550px] overflow-y-auto">

            @forelse($cashRegister->transactions->sortByDesc('created_at') as $transaction)

                <div class="flex items-start gap-5 group">

                    {{-- ICON --}}
                    <div class="w-12 h-12 flex items-center justify-center rounded-full
                        {{ $transaction->isIncoming()
                            ? 'bg-green-100 text-green-600'
                            : 'bg-red-100 text-red-600' }}
                        shadow-sm">

                        @if($transaction->isIncoming())
                            <x-heroicon-o-arrow-down class="w-6 h-6"/>
                        @else
                            <x-heroicon-o-arrow-up class="w-6 h-6"/>
                        @endif

                    </div>

                    {{-- CONTENT --}}
                    <div class="flex-1 bg-gray-50 p-5 rounded-2xl
                                group-hover:shadow-lg transition duration-200">

                        <div class="flex justify-between items-center">

                            <div>
                                <p class="font-semibold text-gray-800">
                                    {{ $transaction->source_label }}
                                </p>

                                <p class="text-xs text-gray-500">
                                    {{ $transaction->created_at->format('d/m/Y H:i') }}
                                </p>
                            </div>

                            <p class="text-lg font-bold
                                {{ $transaction->isIncoming()
                                    ? 'text-green-600'
                                    : 'text-red-600' }}">

                                {{ $transaction->isIncoming() ? '+' : '-' }}
                                {{ number_format($transaction->amount,2) }}
                                {{ company()?->currency }}

                            </p>

                        </div>

                        @if($transaction->description)
                            <p class="text-sm text-gray-600 mt-3 flex items-center gap-2">
                                <x-heroicon-o-chat-bubble-left-ellipsis class="w-4 h-4 text-gray-400"/>
                                {{ $transaction->description }}
                            </p>
                        @endif

                        @if($transaction->reference)
                            <p class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                                <x-heroicon-o-hashtag class="w-3 h-3"/>
                                Réf : {{ $transaction->reference }}
                            </p>
                        @endif

                    </div>

                </div>

            @empty

                <div class="text-center py-16 text-gray-500">
                    <x-heroicon-o-inbox class="w-10 h-10 mx-auto mb-3 text-gray-300"/>
                    Aucune transaction enregistrée
                </div>

            @endforelse

        </div>

    </div>

</div>


{{-- ================= SCRIPT ANIMATION ================= --}}
@push('scripts')
<script>
function cashShow() {
    return {

        totalIn: {{ $cashRegister->total_in }},
        totalOut: {{ $cashRegister->total_out }},
        balance: {{ $cashRegister->current_balance ?? $cashRegister->closing_balance }},

        animatedIn: 0,
        animatedOut: 0,
        animatedBalance: 0,

        statusLabel: "{{ $cashRegister->status === 'open' ? 'Caisse Ouverte' : 'Caisse Fermée' }}",
        statusClass: "{{ $cashRegister->status === 'open'
            ? 'bg-green-100 text-green-700'
            : 'bg-gray-200 text-gray-700' }}",

        init() {
            this.animate('animatedIn', this.totalIn);
            this.animate('animatedOut', this.totalOut);
            this.animate('animatedBalance', this.balance);
        },

        animate(property, target) {
            let start = 0;
            let duration = 800;
            let stepTime = 15;
            let steps = duration / stepTime;
            let increment = target / steps;

            let counter = setInterval(() => {
                start += increment;
                if (start >= target) {
                    start = target;
                    clearInterval(counter);
                }
                this[property] = start;
            }, stepTime);
        },

        formatCurrency(value) {
            return new Intl.NumberFormat().format(value.toFixed(2)) + ' {{ company()?->currency }}';
        }
    }
}
</script>
@endpush

</x-app-layout>