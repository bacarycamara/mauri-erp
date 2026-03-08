<x-app-layout>

@can('view cash_registers')

<div class="max-w-7xl mx-auto space-y-8"
     x-data="cashDashboard()"
     x-init="init()"
     x-cloak>

```
{{-- ================= HEADER ================= --}}
<div class="flex flex-col md:flex-row md:justify-between md:items-center gap-6">

    <div class="flex items-center gap-3">
        <x-heroicon-o-banknotes class="w-8 h-8 text-indigo-600"/>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                Gestion des Caisses
            </h1>
            <p class="text-gray-500 text-sm">
                Module Finance ERP
            </p>
        </div>
    </div>

    <div class="flex items-center gap-3">

        @if(!$current)

            @can('open cash_registers')
            <button
                @click="showOpenModal = true"
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
                    {{ $current->name }}
                </span>

                @can('close cash_registers')
                <form method="POST"
                      action="{{ route('admin.cash-registers.close',$current) }}"
                      onsubmit="return confirm('Fermer la caisse ?');">
                    @csrf
                    <button
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
<div class="grid md:grid-cols-4 gap-6">

    <x-cash.stat title="Total Caisses" :value="$registers->total()" />

    <x-cash.stat
        title="Ouvertes"
        :value="$registers->where('status','open')->count()"
        color="green" />

    <x-cash.stat
        title="Fermées"
        :value="$registers->where('status','closed')->count()" />

    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white p-6 rounded-2xl shadow">
        <p class="text-xs uppercase opacity-80">Solde Global</p>
        <p class="text-2xl font-bold mt-2"
           x-text="formatCurrency(animatedGlobal)">
        </p>
    </div>

</div>


{{-- ================= TABLE ================= --}}
<div class="bg-white rounded-2xl shadow overflow-hidden">

    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs uppercase text-gray-600">
            <tr>
                <th class="px-6 py-3 text-left">Nom</th>
                <th class="px-6 py-3">Ouverture</th>
                <th class="px-6 py-3">Fermeture</th>
                <th class="px-6 py-3">Solde final</th>
                <th class="px-6 py-3">Statut</th>
                <th class="px-6 py-3 text-right">Actions</th>
            </tr>
        </thead>

        <tbody class="divide-y">

        @forelse($registers as $register)
            <tr class="hover:bg-gray-50 transition">

                <td class="px-6 py-4 font-semibold">
                    {{ $register->name }}
                </td>

                <td class="px-6 py-4">
                    {{ optional($register->opened_at)->format('d/m/Y H:i') }}
                </td>

                <td class="px-6 py-4">
                    {{ optional($register->closed_at)->format('d/m/Y H:i') ?? '-' }}
                </td>

                <td class="px-6 py-4 font-semibold">
                    {{ number_format($register->closing_balance,2) }}
                    {{ company()?->currency }}
                </td>

                <td class="px-6 py-4">
                    @if($register->status === 'open')
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                            Ouverte
                        </span>
                    @else
                        <span class="px-3 py-1 bg-gray-200 rounded-full text-xs">
                            Fermée
                        </span>
                    @endif
                </td>

                <td class="px-6 py-4 text-right space-x-3">

                    <a href="{{ route('admin.cash-registers.show',$register) }}">
                        <x-heroicon-o-eye class="w-5 h-5 text-blue-600 inline"/>
                    </a>

                    <a href="{{ route('admin.cash-transactions.index',[
                        'cash_register_id'=>$register->id
                    ]) }}">
                        <x-heroicon-o-arrows-right-left class="w-5 h-5 text-indigo-600 inline"/>
                    </a>

                    <a href="{{ route('admin.cash-registers.pdf',$register) }}">
                        <x-heroicon-o-printer class="w-5 h-5 text-gray-700 inline"/>
                    </a>

                </td>

            </tr>

        @empty
            <tr>
                <td colspan="6" class="text-center py-12 text-gray-400">
                    Aucune caisse enregistrée
                </td>
            </tr>
        @endforelse

        </tbody>
    </table>

</div>

{{ $registers->links() }}


{{-- ================= MODAL OPEN ================= --}}
@can('open cash_registers')
<div x-show="showOpenModal"
     x-transition.opacity
     class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

    <div @click.away="showOpenModal=false"
         class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-6">

        <h2 class="text-lg font-bold mb-6 flex items-center gap-2">
            <x-heroicon-o-lock-open class="w-5 h-5 text-green-600"/>
            Ouvrir une caisse
        </h2>

        <form method="POST" action="{{ route('admin.cash-registers.open') }}">
            @csrf

            <div class="mb-4">
                <label class="text-sm font-medium">Nom</label>
                <input type="text" name="name" required
                    class="mt-1 w-full rounded-xl border-gray-300">
            </div>

            <div class="mb-6">
                <label class="text-sm font-medium">Solde initial</label>
                <input type="number" step="0.01"
                       name="opening_balance" value="0"
                       class="mt-1 w-full rounded-xl border-gray-300">
            </div>

            <div class="flex justify-end gap-3">
                <button type="button"
                    @click="showOpenModal=false"
                    class="px-4 py-2 bg-gray-100 rounded-xl">
                    Annuler
                </button>

                <button type="submit"
                    class="px-5 py-2 bg-green-600 text-white rounded-xl">
                    Ouvrir
                </button>
            </div>
        </form>

    </div>
</div>
@endcan
```

</div>

@push('scripts')

<script>
function cashDashboard(){
    return{
        showOpenModal:false,
        globalBalance: {{ $registers->sum('closing_balance') ?? 0 }},
        animatedGlobal:0,

        init(){
            this.animate(this.globalBalance)
        },

        animate(target){
            let start=0
            let steps=40
            let inc=target/steps

            let timer=setInterval(()=>{
                start+=inc
                if(start>=target){
                    start=target
                    clearInterval(timer)
                }
                this.animatedGlobal=start
            },20)
        },

        formatCurrency(v){
            return new Intl.NumberFormat().format(
                Number(v).toFixed(2)
            ) + ' {{ company()?->currency }}'
        }
    }
}
</script>

@endpush

@endcan

</x-app-layout>
