<x-app-layout>

@can('view payments')

<div x-data="{ loaded: false }"
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
                <h1 class="text-2xl font-bold text-gray-900">Détail Paiement</h1>
                <p class="text-sm text-gray-500 mt-1">N° {{ $payment->payment_number }}</p>
            </div>
        </div>

        {{-- ACTIONS --}}
        <div class="flex gap-3 flex-wrap">

            <a href="{{ route('admin.payments.index') }}"
               class="px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-100 transition">
                Retour
            </a>

            @can('print payments')
            <a href="{{ route('admin.payments.pdf', $payment) }}"
               target="_blank"
               rel="noopener noreferrer"
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-700 text-white rounded-xl hover:bg-black transition">
                <x-heroicon-o-printer class="w-4 h-4"/>
                PDF
            </a>

            <a href="{{ route('admin.payments.printer', $payment) }}"
               target="_blank"
               rel="noopener noreferrer"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition">
                <x-heroicon-o-receipt-percent class="w-4 h-4"/>
                Ticket
            </a>
            @endcan

            @can('cancel payments')
            @if($payment->canBeCancelled())
            <form method="POST"
                  action="{{ route('admin.payments.cancel', $payment) }}"
                  onsubmit="return confirm('Annuler ce paiement ?')">
                @csrf
                <button type="submit"
                        class="px-4 py-2 bg-yellow-600 text-white rounded-xl hover:bg-yellow-700 transition">
                    Annuler
                </button>
            </form>
            @endif
            @endcan

        </div>
    </div>


    {{-- ================= STATUT ================= --}}
    <div x-show="loaded" x-transition>
        {!! $payment->statusBadge ?? '' !!}
    </div>


    {{-- ================= RÉSUMÉ ================= --}}
    <div x-show="loaded"
         x-transition.duration.500ms
         class="grid md:grid-cols-2 gap-6">

        {{-- Type + Montant --}}
        <div class="bg-white rounded-2xl shadow border p-6 space-y-4">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500">Type</span>
                @if($payment->type === 'in')
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

        {{-- Méthode + Date --}}
        <div class="bg-white rounded-2xl shadow border p-6 space-y-4">
            <div>
                <p class="text-sm text-gray-500">Méthode</p>
                <p class="font-semibold">
                    {{-- ✅ SÉCURISÉ : e() + remplacement underscore --}}
                    {{ e(str_replace('_', ' ', $payment->payment_method_label ?? $payment->payment_method)) }}
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


    {{-- ================= DÉTAILS ================= --}}
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
            <div class="bg-blue-50 p-5 rounded-xl space-y-2 text-sm">
                <p>
                    <strong>Nom :</strong>
                    {{ e($payment->sale->customer?->name ?? '-') }}
                </p>
                <p>
                    <strong>Vente :</strong>
                    @can('view sales')
                    <a href="{{ route('admin.sales.show', $payment->sale) }}"
                       class="text-indigo-600 hover:underline">
                        {{ $payment->sale->reference }}
                    </a>
                    @else
                        {{ $payment->sale->reference }}
                    @endcan
                </p>
                <p>
                    <strong>Total vente :</strong>
                    {{ number_format($payment->sale->total_amount, 2) }} {{ company()?->currency }}
                </p>
                <p>
                    <strong>Reste dû :</strong>
                    <span class="{{ $payment->sale->due_amount > 0 ? 'text-red-600 font-semibold' : 'text-green-600' }}">
                        {{ number_format($payment->sale->due_amount, 2) }} {{ company()?->currency }}
                    </span>
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
            <div class="bg-red-50 p-5 rounded-xl space-y-2 text-sm">
                <p>
                    <strong>Nom :</strong>
                    {{ e($payment->purchase->supplier?->name ?? '-') }}
                </p>
                <p>
                    <strong>Achat :</strong>
                    @can('view purchases')
                    <a href="{{ route('admin.purchases.show', $payment->purchase) }}"
                       class="text-indigo-600 hover:underline">
                        {{ $payment->purchase->reference }}
                    </a>
                    @else
                        {{ $payment->purchase->reference }}
                    @endcan
                </p>
                <p>
                    <strong>Total achat :</strong>
                    {{ number_format($payment->purchase->total_amount, 2) }} {{ company()?->currency }}
                </p>
                <p>
                    <strong>Reste dû :</strong>
                    <span class="{{ $payment->purchase->due_amount > 0 ? 'text-red-600 font-semibold' : 'text-green-600' }}">
                        {{ number_format($payment->purchase->due_amount, 2) }} {{ company()?->currency }}
                    </span>
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
            <div class="bg-green-50 p-5 rounded-xl space-y-2 text-sm">
                <p>
                    <strong>Caisse :</strong>
                    {{ e($payment->cashRegister?->name ?? '-') }}
                </p>
                <p>
                    <strong>Solde actuel :</strong>
                    {{ number_format($payment->cashRegister?->current_balance ?? 0, 2) }}
                    {{ company()?->currency }}
                </p>
                @if($payment->reference)
                <p>
                    <strong>Référence transaction :</strong>
                    {{ e($payment->reference) }}
                </p>
                @endif
            </div>
        </div>

        {{-- NOTES --}}
        @if($payment->notes)
        <div>
            <h3 class="font-semibold text-gray-700 mb-3">Notes</h3>
            <div class="bg-gray-50 p-4 rounded-xl text-gray-600 text-sm">
                {{-- ✅ SÉCURISÉ : nl2br + e() pour afficher les sauts de ligne sans XSS --}}
                {!! nl2br(e($payment->notes)) !!}
            </div>
        </div>
        @endif

        {{-- META --}}
        <div class="border-t pt-6 text-xs text-gray-400 space-y-1">
            <p>Créé le : {{ $payment->created_at?->format('d/m/Y H:i') }}</p>
            @if($payment->updated_at && $payment->updated_at->ne($payment->created_at))
            <p>Modifié le : {{ $payment->updated_at->format('d/m/Y H:i') }}</p>
            @endif
        </div>

    </div>

</div>

@else
<div class="flex flex-col items-center justify-center py-24 text-gray-400">
    <x-heroicon-o-lock-closed class="w-12 h-12 mb-4 text-gray-300"/>
    <p class="text-lg font-medium">Accès non autorisé</p>
</div>
@endcan

</x-app-layout>