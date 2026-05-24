<div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">

    {{-- ================= TABLE ================= --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm">

            <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wider">
            <tr>
                <th class="px-6 py-4 text-left">Produit</th>
                <th class="px-4 py-4 text-center">Type</th>
                <th class="px-4 py-4 text-center">Quantité</th>
                <th class="px-4 py-4 text-center">Avant</th>
                <th class="px-4 py-4 text-center">Après</th>
                <th class="px-4 py-4 text-center">Référence</th>
                <th class="px-6 py-4 text-right">Date</th>
            </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">

            @forelse($movements as $m)
            @php
                // ✅ Whitelist type — évite affichage de valeurs inattendues
                $mType = $m->type ?? '';
                $typeConfig = match($mType) {
                    'purchase'   => ['label' => 'Entrée',      'class' => 'bg-green-100 text-green-700'],
                    'sale'       => ['label' => 'Sortie',      'class' => 'bg-red-100 text-red-700'],
                    'return'     => ['label' => 'Retour',      'class' => 'bg-indigo-100 text-indigo-700'],
                    'adjustment' => ['label' => 'Ajustement',  'class' => 'bg-yellow-100 text-yellow-700'],
                    default      => ['label' => 'Autre',       'class' => 'bg-gray-100 text-gray-600'],
                };

                $stockAfter  = (float) ($m->stock_after  ?? 0);
                $stockBefore = (float) ($m->stock_before ?? 0);
            @endphp

            <tr class="hover:bg-indigo-50/40 transition duration-200">

                {{-- PRODUIT --}}
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-indigo-50 text-indigo-600 rounded-xl flex-shrink-0">
                            <x-heroicon-o-cube class="w-4 h-4"/>
                        </div>
                        <span class="font-medium text-gray-800">
                            {{-- ✅ e() sur le nom du produit --}}
                            {{ e($m->product?->name ?? '-') }}
                        </span>
                    </div>
                </td>

                {{-- TYPE --}}
                <td class="px-4 py-4 text-center">
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full
                                 text-xs font-medium {{ $typeConfig['class'] }}">
                        @if($mType === 'purchase')
                            <x-heroicon-o-arrow-down-circle class="w-4 h-4"/>
                        @elseif($mType === 'sale')
                            <x-heroicon-o-arrow-up-circle class="w-4 h-4"/>
                        @elseif($mType === 'return')
                            <x-heroicon-o-arrow-uturn-left class="w-4 h-4"/>
                        @else
                            <x-heroicon-o-adjustments-horizontal class="w-4 h-4"/>
                        @endif
                        {{ $typeConfig['label'] }}
                    </span>
                </td>

                {{-- QUANTITE --}}
                <td class="px-4 py-4 text-center font-semibold text-gray-800">
                    {{ number_format($m->quantity ?? 0, 2) }}
                </td>

                {{-- STOCK AVANT --}}
                <td class="px-4 py-4 text-center text-gray-500">
                    {{ number_format($stockBefore, 2) }}
                </td>

                {{-- STOCK APRÈS --}}
                <td class="px-4 py-4 text-center">
                    <span class="font-bold
                        {{ $stockAfter > $stockBefore
                            ? 'text-green-600'
                            : ($stockAfter < $stockBefore ? 'text-red-600' : 'text-indigo-600') }}">
                        {{ number_format($stockAfter, 2) }}
                        {{ $stockAfter > $stockBefore ? '↑' : ($stockAfter < $stockBefore ? '↓' : '') }}
                    </span>
                </td>

                {{-- RÉFÉRENCE --}}
                <td class="px-4 py-4 text-center text-gray-500">
                    {{-- ✅ e() sur la référence --}}
                    {{ e($m->reference ?? '—') }}
                </td>

                {{-- DATE --}}
                <td class="px-6 py-4 text-right text-gray-500 whitespace-nowrap">
                    {{ $m->created_at?->format('d/m/Y') ?? '-' }}
                    <div class="text-xs text-gray-400">
                        {{ $m->created_at?->format('H:i') ?? '' }}
                    </div>
                </td>

            </tr>
            @empty
            <tr>
                <td colspan="7" class="py-16 text-center text-gray-400">
                    <div class="flex flex-col items-center gap-3">
                        <div class="p-4 bg-gray-100 rounded-full">
                            <x-heroicon-o-inbox class="w-6 h-6"/>
                        </div>
                        <p class="font-medium">Aucun mouvement enregistré</p>
                        <p class="text-xs">Les mouvements de stock apparaîtront ici</p>
                    </div>
                </td>
            </tr>
            @endforelse

            </tbody>
        </table>
    </div>

    {{-- ================= PAGINATION ================= --}}
    @if($movements->hasPages())
    <div class="px-6 py-4 border-t bg-gray-50">
        {{ $movements->withQueryString()->links() }}
    </div>
    @endif

</div>