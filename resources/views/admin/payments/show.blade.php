<x-app-layout>

<style>
[x-cloak]{ display:none !important; }
</style>

<div x-data="{ loaded:false }"
     x-init="setTimeout(() => loaded = true, 120)"
     class="max-w-5xl mx-auto space-y-8"
     x-cloak>

    {{-- ================= HEADER ================= --}}
    <div x-show="loaded"
         x-transition.opacity.duration.500ms
         class="flex flex-col md:flex-row md:justify-between md:items-start gap-6">

        <div class="flex items-start gap-3">

            <div class="p-3 bg-indigo-100 rounded-2xl">
                <x-heroicon-o-banknotes class="w-6 h-6 text-indigo-600"/>
            </div>

            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Détail Paiement
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    N° {{ $payment->payment_number }}
                </p>
            </div>

        </div>

        {{-- ACTIONS --}}
        <div class="flex gap-3 flex-wrap">

            <a href="{{ route('admin.payments.index') }}"
               class="px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-100 transition">
                Retour
            </a>

            <a href="{{ route('admin.payments.pdf',$payment) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-700 text-white rounded-xl hover:bg-black transition">
                <x-heroicon-o-printer class="w-4 h-4"/>
                PDF
            </a>

            {{-- Ticket thermique --}}
            <a href="{{ route('admin.payments.printer',$payment) }}"
               target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition">
                <x-heroicon-o-receipt-percent class="w-4 h-4"/>
                Ticket
            </a>

            @if($payment->canBeCancelled())
                <form method="POST"
                      action="{{ route('admin.payments.cancel',$payment) }}">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Annuler ce paiement ?')"
                            class="px-4 py-2 bg-yellow-600 text-white rounded-xl hover:bg-yellow-700 transition">
                        Annuler
                    </button>
                </form>
            @endif

        </div>
    </div>


    {{-- ================= STATUS ================= --}}
    <div x-show="loaded" x-transition>
        {!! $payment->statusBadge ?? '' !!}
    </div>


    {{-- ================= SUMMARY ================= --}}
    <div x-show="loaded"
         x-transition.duration.500ms
         class="grid md:grid-cols-2 gap-6">

        <div class="bg-white rounded-2xl shadow border p-6 space-y-3">

            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500">Type</span>

                @if($payment->isIncoming())
                    <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">
                        Entrée
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-semibold">
                        Sortie
                    </span>
                @endif
            </div>

            <div>
                <p class="text-sm text-gray-500">Montant</p>
                <p class="text-2xl font-bold text-indigo-600">
                    {{ $payment->formatted_amount }}
                </p>
            </div>

        </div>


        <div class="bg-white rounded-2xl shadow border p-6 space-y-3">

            <div>
                <p class="text-sm text-gray-500">Méthode</p>
                <p class="font-semibold capitalize">
                    {{ str_replace('_',' ',$payment->payment_method) }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Date</p>
                <p class="font-semibold">
                    {{ $payment->payment_date?->format('d/m/Y') }}
                </p>
            </div>

        </div>

    </div>


    {{-- ================= DETAILS ================= --}}
    <div x-show="loaded"
         x-transition.duration.500ms
         class="bg-white rounded-2xl shadow border p-8 space-y-8">

        {{-- CLIENT --}}
        @if($payment->sale)
            <div>
                <h3 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <x-heroicon-o-user class="w-5 h-5 text-blue-600"/>
                    Client
                </h3>

                <div class="bg-blue-50 p-5 rounded-xl space-y-2">
                    <p><strong>Nom :</strong> {{ $payment->sale?->customer?->name }}</p>

                    <p>
                        <strong>Vente :</strong>
                        <a href="{{ route('admin.sales.show',$payment->sale) }}"
                           class="text-indigo-600 hover:underline">
                            {{ $payment->sale->reference }}
                        </a>
                    </p>
                </div>
            </div>
        @endif


        {{-- FOURNISSEUR --}}
        @if($payment->purchase)
            <div>
                <h3 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <x-heroicon-o-building-storefront class="w-5 h-5 text-red-600"/>
                    Fournisseur
                </h3>

                <div class="bg-red-50 p-5 rounded-xl space-y-2">
                    <p><strong>Nom :</strong> {{ $payment->purchase?->supplier?->name }}</p>

                    <p>
                        <strong>Achat :</strong>
                        <a href="{{ route('admin.purchases.show',$payment->purchase) }}"
                           class="text-indigo-600 hover:underline">
                            {{ $payment->purchase->reference }}
                        </a>
                    </p>
                </div>
            </div>
        @endif


        {{-- CAISSE --}}
        <div>
            <h3 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <x-heroicon-o-wallet class="w-5 h-5 text-green-600"/>
                Caisse
            </h3>

            <div class="bg-green-50 p-5 rounded-xl space-y-2">
                <p><strong>Caisse :</strong> {{ $payment->cashRegister?->name ?? '-' }}</p>

                <p>
                    <strong>Solde actuel :</strong>
                    {{ number_format($payment->cashRegister?->current_balance ?? 0,2) }}
                    {{ company()?->currency }}
                </p>

                @if($payment->transaction)
                    <p class="text-sm text-gray-500 mt-2">
                        Transaction liée :
                        {{ $payment->transaction->reference ?? 'Auto' }}
                    </p>
                @endif
            </div>
        </div>


        {{-- NOTES --}}
        @if($payment->notes)
            <div>
                <h3 class="font-semibold text-gray-700 mb-3">Notes</h3>

                <div class="bg-gray-50 p-4 rounded-xl text-gray-600">
                    {{ $payment->notes }}
                </div>
            </div>
        @endif


        {{-- META --}}
        <div class="border-t pt-6 text-xs text-gray-400 space-y-1">
            <p>Créé le : {{ $payment->created_at->format('d/m/Y H:i') }}</p>

            @if($payment->updated_at != $payment->created_at)
                <p>Dernière modification :
                    {{ $payment->updated_at->format('d/m/Y H:i') }}
                </p>
            @endif
        </div>

    </div>

</div>

</x-app-layout>