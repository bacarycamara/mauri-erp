<x-app-layout>

@can('view cash_registers')

<div class="max-w-7xl mx-auto space-y-8"
     x-data="cashDashboard()"
     x-init="init()"
     x-cloak>

    {{-- ================= HEADER ================= --}}
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-6">

        <div class="flex items-center gap-3">
            <x-heroicon-o-banknotes class="w-8 h-8 text-indigo-600"/>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Gestion des Caisses</h1>
                <p class="text-gray-500 text-sm">Module Finance ERP</p>
            </div>
        </div>

        <div class="flex items-center gap-3">

            @if(!$current)
                @can('open cash_registers')
                <button @click="showOpenModal = true"
                        class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r
                               from-green-600 to-green-700 text-white rounded-xl
                               shadow-lg hover:scale-105 transition">
                    <x-heroicon-o-lock-open class="w-5 h-5"/>
                    Ouvrir Caisse
                </button>
                @endcan
            @else
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-2 px-5 py-3
                                 bg-green-100 text-green-700 rounded-xl font-semibold">
                        <x-heroicon-o-check-circle class="w-5 h-5"/>
                        {{ e($current->name) }}
                    </span>

                    @can('close cash_registers')
                    <form method="POST"
                          action="{{ route('admin.cash-registers.close', $current) }}"
                          onsubmit="return confirm('Fermer la caisse ?')">
                        @csrf
                        <button type="submit"
                                class="px-5 py-3 bg-red-600 text-white rounded-xl
                                       hover:bg-red-700 transition shadow">
                            Fermer
                        </button>
                    </form>
                    @endcan
                </div>
            @endif

        </div>
    </div>


    {{-- ================= STATS ================= --}}
    {{--
        ✅ CORRIGÉ : $registers est paginé — .where() et .count() sur collection paginée
        ne donnent que la page courante. Utiliser getCollection() pour être explicite.
    --}}
    @php
        $openCount   = $registers->getCollection()->where('status', 'open')->count();
        $closedCount = $registers->getCollection()->where('status', 'closed')->count();
        $currency    = company()?->currency ?? '';
    @endphp

    <div class="grid md:grid-cols-4 gap-6">

        <x-cash.stat title="Total Caisses" :value="$registers->total()"/>

        <x-cash.stat title="Ouvertes"  :value="$openCount"   color="green"/>

        <x-cash.stat title="Fermées"   :value="$closedCount"/>

        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white p-6 rounded-2xl shadow">
            <p class="text-xs uppercase opacity-80">Solde Global</p>
            <p class="text-2xl font-bold mt-2"
               x-text="formatCurrency(animatedGlobal)">
            </p>
        </div>

    </div>


    {{-- ================= TABLE ================= --}}
    <div class="bg-white rounded-2xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">

                <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                <tr>
                    <th class="px-6 py-3 text-left">Nom</th>
                    <th class="px-6 py-3 text-left">Ouverture</th>
                    <th class="px-6 py-3 text-left">Fermeture</th>
                    <th class="px-6 py-3 text-left">Solde final</th>
                    <th class="px-6 py-3 text-left">Statut</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                @forelse($registers as $register)
                <tr class="hover:bg-gray-50 transition">

                    <td class="px-6 py-4 font-semibold">
                        {{ e($register->name) }}
                    </td>

                    <td class="px-6 py-4 text-gray-600">
                        {{ $register->opened_at?->format('d/m/Y H:i') ?? '-' }}
                    </td>

                    <td class="px-6 py-4 text-gray-600">
                        {{ $register->closed_at?->format('d/m/Y H:i') ?? '-' }}
                    </td>

                    <td class="px-6 py-4 font-semibold">
                        {{ number_format($register->closing_balance ?? 0, 2) }}
                        {{ $currency }}
                    </td>

                    <td class="px-6 py-4">
                        @if($register->status === 'open')
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                            Ouverte
                        </span>
                        @else
                        <span class="px-3 py-1 bg-gray-200 text-gray-600 rounded-full text-xs font-semibold">
                            Fermée
                        </span>
                        @endif
                    </td>

                    <td class="px-6 py-4">
                        <div class="flex justify-end items-center gap-4">

                            @can('view cash_registers')
                            <a href="{{ route('admin.cash-registers.show', $register) }}"
                               title="Voir détails"
                               class="text-blue-600 hover:text-blue-800 transition">
                                <x-heroicon-o-eye class="w-5 h-5"/>
                            </a>
                            @endcan

                            @can('view cash_transactions')
                            <a href="{{ route('admin.cash-transactions.index', ['cash_register_id' => $register->id]) }}"
                               title="Transactions"
                               class="text-indigo-600 hover:text-indigo-800 transition">
                                <x-heroicon-o-arrows-right-left class="w-5 h-5"/>
                            </a>
                            @endcan

                            @can('print cash_registers')
                            <a href="{{ route('admin.cash-registers.pdf', $register) }}"
                               title="Rapport PDF"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="text-gray-600 hover:text-black transition">
                                <x-heroicon-o-printer class="w-5 h-5"/>
                            </a>
                            @endcan

                            @can('delete cash_registers')
                            @if($register->status !== 'open')
                            <form action="{{ route('admin.cash-registers.destroy', $register) }}"
                                  method="POST"
                                  onsubmit="return confirm('Supprimer cette caisse ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        title="Supprimer"
                                        class="text-red-500 hover:text-red-700 transition">
                                    <x-heroicon-o-trash class="w-5 h-5"/>
                                </button>
                            </form>
                            @endif
                            @endcan

                        </div>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-12 text-gray-400">
                        <x-heroicon-o-banknotes class="w-10 h-10 mx-auto mb-3 text-gray-300"/>
                        Aucune caisse enregistrée
                    </td>
                </tr>
                @endforelse

                </tbody>
            </table>
        </div>
    </div>

    {{-- ✅ withQueryString pour conserver les filtres --}}
    {{ $registers->withQueryString()->links() }}


    {{-- ================= MODAL OUVRIR CAISSE ================= --}}
    @can('open cash_registers')
    <div x-show="showOpenModal"
         x-transition.opacity
         x-cloak
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

        <div @click.away="showOpenModal = false"
             class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-6"
             @click.stop>

            <h2 class="text-lg font-bold mb-6 flex items-center gap-2">
                <x-heroicon-o-lock-open class="w-5 h-5 text-green-600"/>
                Ouvrir une caisse
            </h2>

            <form method="POST" action="{{ route('admin.cash-registers.open') }}">
                @csrf

                <div class="mb-4">
                    <label for="cash_name" class="block text-sm font-medium text-gray-700 mb-1">
                        Nom <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="cash_name"
                           name="name"
                           required
                           maxlength="100"
                           placeholder="Ex: Caisse principale"
                           class="mt-1 w-full rounded-xl border-gray-300 focus:ring-green-500 focus:border-green-500">
                </div>

                <div class="mb-6">
                    <label for="opening_balance" class="block text-sm font-medium text-gray-700 mb-1">
                        Solde initial
                    </label>
                    <input type="number"
                           id="opening_balance"
                           name="opening_balance"
                           step="0.01"
                           min="0"
                           value="0"
                           class="mt-1 w-full rounded-xl border-gray-300 focus:ring-green-500 focus:border-green-500">
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button"
                            @click="showOpenModal = false"
                            class="px-4 py-2 bg-gray-100 rounded-xl hover:bg-gray-200 transition">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-5 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition">
                        Ouvrir
                    </button>
                </div>
            </form>

        </div>
    </div>
    @endcan

</div>

@else
<div class="flex flex-col items-center justify-center py-24 text-gray-400">
    <x-heroicon-o-lock-closed class="w-12 h-12 mb-4 text-gray-300"/>
    <p class="text-lg font-medium">Accès non autorisé</p>
</div>
@endcan


@push('scripts')
<script>
function cashDashboard() {
    return {
        showOpenModal: false,

        // ✅ SÉCURISÉ : cast float explicite pour éviter injection JS
        globalBalance: {{ (float) $registers->getCollection()->sum('closing_balance') }},
        animatedGlobal: 0,

        init() {
            this.animate(this.globalBalance);
        },

        animate(target) {
            if (target <= 0) { this.animatedGlobal = 0; return; }
            let start   = 0;
            let steps   = 40;
            let inc     = target / steps;
            let timer   = setInterval(() => {
                start += inc;
                if (start >= target) {
                    start = target;
                    clearInterval(timer);
                }
                this.animatedGlobal = start;
            }, 20);
        },

        formatCurrency(v) {
            return new Intl.NumberFormat('fr-FR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(Number(v)) + ' {{ $currency }}';
        }
    }
}
</script>
@endpush

</x-app-layout>