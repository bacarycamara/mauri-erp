<x-app-layout>

<div class="max-w-5xl mx-auto space-y-8"
     x-data
     x-cloak>

    {{-- ================= HEADER ================= --}}
    <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-6">

        <div class="flex items-start gap-3">

            <div class="p-3 bg-red-100 rounded-2xl">
                <x-heroicon-o-receipt-percent class="w-6 h-6 text-red-600"/>
            </div>

            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Détail Dépense
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Référence : {{ $expense->reference }}
                </p>
            </div>

        </div>

        <div class="flex flex-wrap gap-3">

            <a href="{{ route('admin.expenses.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-100 transition">
                <x-heroicon-o-arrow-left class="w-4 h-4"/>
                Retour
            </a>

            @if($expense->isPending())
                <form method="POST"
                      action="{{ route('admin.expenses.approve',$expense) }}">
                    @csrf
                    <button onclick="return confirm('Approuver cette dépense ?')"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 shadow transition">
                        <x-heroicon-o-check class="w-4 h-4"/>
                        Approuver
                    </button>
                </form>
            @endif

            @if(!$expense->isCancelled())
                <form method="POST"
                      action="{{ route('admin.expenses.cancel',$expense) }}">
                    @csrf
                    <button onclick="return confirm('Annuler cette dépense ?')"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700 shadow transition">
                        <x-heroicon-o-x-mark class="w-4 h-4"/>
                        Annuler
                    </button>
                </form>
            @endif

        </div>

    </div>


    {{-- ================= STATUS BADGE ================= --}}
    <div>
        {!! $expense->status_badge !!}
    </div>


    {{-- ================= SUMMARY CARDS ================= --}}
    <div class="grid md:grid-cols-3 gap-6">

        <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
            <p class="text-xs uppercase text-gray-500">Montant</p>
            <p class="text-2xl font-bold mt-2 text-red-600 flex items-center gap-2">
                <x-heroicon-o-banknotes class="w-5 h-5"/>
                {{ $expense->formatted_amount }}
            </p>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
            <p class="text-xs uppercase text-gray-500">Date</p>
            <p class="text-lg font-semibold mt-2 flex items-center gap-2">
                <x-heroicon-o-calendar-days class="w-5 h-5 text-gray-500"/>
                {{ $expense->expense_date?->format('d/m/Y') }}
            </p>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
            <p class="text-xs uppercase text-gray-500">Méthode</p>
            <p class="text-lg font-semibold mt-2 capitalize flex items-center gap-2">
                <x-heroicon-o-credit-card class="w-5 h-5 text-gray-500"/>
                {{ str_replace('_',' ',$expense->payment_method) }}
            </p>
        </div>

    </div>


    {{-- ================= DETAILS ================= --}}
    <div class="bg-white rounded-2xl shadow p-8 space-y-8">

        <div class="grid md:grid-cols-2 gap-6 text-sm">

            <div>
                <p class="text-gray-500">Catégorie</p>
                <p class="font-semibold mt-1">
                    {{ $expense->category }}
                </p>
            </div>

            <div>
                <p class="text-gray-500">Caisse</p>
                <p class="font-semibold mt-1">
                    {{ $expense->cashRegister?->name ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-gray-500">Créée le</p>
                <p class="font-semibold mt-1">
                    {{ $expense->created_at->format('d/m/Y H:i') }}
                </p>
            </div>

            <div>
                <p class="text-gray-500">Dernière modification</p>
                <p class="font-semibold mt-1">
                    {{ $expense->updated_at->format('d/m/Y H:i') }}
                </p>
            </div>

        </div>


        {{-- APPROVAL INFO --}}
        @if($expense->approvedBy)
            <div class="bg-green-50 border border-green-200 p-4 rounded-2xl flex items-center gap-3">
                <x-heroicon-o-check-badge class="w-5 h-5 text-green-600"/>
                <span class="text-green-800 font-semibold">
                    Approuvée par {{ $expense->approvedBy->name }}
                </span>
            </div>
        @endif


        {{-- NOTES --}}
        @if($expense->notes)
            <div>
                <p class="text-sm text-gray-500 mb-2">Notes</p>
                <div class="bg-gray-50 p-4 rounded-2xl text-gray-700">
                    {{ $expense->notes }}
                </div>
            </div>
        @endif

    </div>


    {{-- ================= WORKFLOW ================= --}}
    <div class="bg-white rounded-2xl shadow p-8">

        <h3 class="font-semibold text-gray-700 mb-8 flex items-center gap-2">
            <x-heroicon-o-arrow-path class="w-5 h-5 text-indigo-600"/>
            Workflow
        </h3>

        <div class="flex items-center justify-between text-sm">

            {{-- Pending --}}
            <div class="flex flex-col items-center">
                <div class="w-12 h-12 flex items-center justify-center rounded-full
                    {{ $expense->status === 'pending'
                        ? 'bg-yellow-100 text-yellow-600'
                        : 'bg-gray-100 text-gray-400' }}">
                    1
                </div>
                <span class="mt-2">En attente</span>
            </div>

            <div class="flex-1 h-1 bg-gray-200 mx-6"></div>

            {{-- Approved --}}
            <div class="flex flex-col items-center">
                <div class="w-12 h-12 flex items-center justify-center rounded-full
                    {{ $expense->status === 'approved'
                        ? 'bg-green-100 text-green-600'
                        : 'bg-gray-100 text-gray-400' }}">
                    2
                </div>
                <span class="mt-2">Approuvée</span>
            </div>

            <div class="flex-1 h-1 bg-gray-200 mx-6"></div>

            {{-- Cancelled --}}
            <div class="flex flex-col items-center">
                <div class="w-12 h-12 flex items-center justify-center rounded-full
                    {{ $expense->status === 'cancelled'
                        ? 'bg-red-100 text-red-600'
                        : 'bg-gray-100 text-gray-400' }}">
                    3
                </div>
                <span class="mt-2">Annulée</span>
            </div>

        </div>

    </div>

</div>

</x-app-layout>