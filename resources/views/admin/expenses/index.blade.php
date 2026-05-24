<x-app-layout>

@can('view expenses')

@php
    $currency = company()?->currency ?? '';
@endphp

<div class="max-w-7xl mx-auto space-y-8"
     x-data
     x-cloak>

    {{-- ================= HEADER ================= --}}
    <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-6">

        <div class="flex items-start gap-3">
            <div class="p-3 bg-red-100 rounded-2xl">
                <x-heroicon-o-receipt-percent class="w-6 h-6 text-red-600"/>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gestion des Dépenses</h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $expenses->total() }} dépense(s) enregistrée(s)
                </p>
            </div>
        </div>

        @can('create expenses')
        <a href="{{ route('admin.expenses.create') }}"
           class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-red-600 to-red-700
                  text-white rounded-xl shadow-lg transition hover:scale-105 hover:shadow-xl">
            <x-heroicon-o-plus class="w-4 h-4"/>
            Nouvelle Dépense
        </a>
        @endcan

    </div>


    {{-- ================= KPI ================= --}}
    {{-- ✅ Totaux globaux viennent du controller (requêtes DB) — pas de la page courante --}}

    <div class="grid md:grid-cols-4 gap-6">

        <div class="bg-yellow-50 p-6 rounded-2xl shadow hover:shadow-lg transition">
            <div class="flex justify-between items-center">
                <span class="text-xs uppercase text-yellow-700">En attente</span>
                <x-heroicon-o-clock class="w-4 h-4 text-yellow-600"/>
            </div>
            <p class="text-xl font-bold mt-3 text-yellow-700">
                {{ number_format($totalPending, 2) }} {{ $currency }}
            </p>
        </div>

        <div class="bg-green-50 p-6 rounded-2xl shadow hover:shadow-lg transition">
            <div class="flex justify-between items-center">
                <span class="text-xs uppercase text-green-700">Approuvées</span>
                <x-heroicon-o-check-circle class="w-4 h-4 text-green-600"/>
            </div>
            <p class="text-xl font-bold mt-3 text-green-700">
                {{ number_format($totalApproved, 2) }} {{ $currency }}
            </p>
        </div>

        <div class="bg-red-50 p-6 rounded-2xl shadow hover:shadow-lg transition">
            <div class="flex justify-between items-center">
                <span class="text-xs uppercase text-red-700">Annulées</span>
                <x-heroicon-o-x-circle class="w-4 h-4 text-red-600"/>
            </div>
            <p class="text-xl font-bold mt-3 text-red-700">
                {{ number_format($totalCancelled, 2) }} {{ $currency }}
            </p>
        </div>

        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white p-6 rounded-2xl shadow">
            <div class="flex justify-between items-center opacity-90">
                <span class="text-xs uppercase">Total global</span>
                <x-heroicon-o-banknotes class="w-4 h-4"/>
            </div>
            <p class="text-xl font-bold mt-3">
                {{ number_format($totalGlobal, 2) }} {{ $currency }}
            </p>
        </div>

    </div>


    {{-- ================= FILTRES ================= --}}
    <form method="GET" class="bg-white p-6 rounded-2xl shadow space-y-6">

        <div class="grid md:grid-cols-6 gap-4">

            <div class="relative col-span-2">
                <x-heroicon-o-magnifying-glass class="w-4 h-4 absolute left-3 top-3 text-gray-400"/>
                <input type="text"
                       name="search"
                       value="{{ e(request('search')) }}"
                       maxlength="100"
                       placeholder="Référence / Catégorie..."
                       class="pl-9 w-full rounded-xl border-gray-300 focus:ring-indigo-500">
            </div>

            <select name="status" class="rounded-xl border-gray-300 focus:ring-indigo-500">
                <option value="">Statut</option>
                <option value="pending"   @selected(request('status') === 'pending')>En attente</option>
                <option value="approved"  @selected(request('status') === 'approved')>Approuvée</option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>Annulée</option>
            </select>

            <select name="cash_register_id" class="rounded-xl border-gray-300 focus:ring-indigo-500">
                <option value="">Caisse</option>
                @foreach($cashRegisters as $cash)
                <option value="{{ $cash->id }}"
                        @selected(request('cash_register_id') == $cash->id)>
                    {{ e($cash->name) }}
                </option>
                @endforeach
            </select>

            <input type="date"
                   name="from"
                   value="{{ request('from') }}"
                   class="rounded-xl border-gray-300 focus:ring-indigo-500">

            <input type="date"
                   name="to"
                   value="{{ request('to') }}"
                   class="rounded-xl border-gray-300 focus:ring-indigo-500">

        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.expenses.index') }}"
               class="px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-100 transition">
                Reset
            </a>
            <button type="submit"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition">
                Filtrer
            </button>
        </div>

    </form>


    {{-- ================= TABLE ================= --}}
    <div class="bg-white rounded-2xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">

                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3 text-left">Référence</th>
                    <th class="px-6 py-3 text-left">Catégorie</th>
                    <th class="px-6 py-3 text-left">Caisse</th>
                    <th class="px-6 py-3 text-left">Montant</th>
                    <th class="px-6 py-3 text-left">Statut</th>
                    <th class="px-6 py-3 text-left">Date</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                @forelse($expenses as $expense)
                <tr class="hover:bg-gray-50 transition">

                    <td class="px-6 py-4 font-semibold text-indigo-600">
                        {{ $expense->reference }}
                    </td>

                    <td class="px-6 py-4 text-gray-600">
                        {{ e($expense->category ?? '-') }}
                    </td>

                    <td class="px-6 py-4 text-gray-600">
                        {{ e($expense->cashRegister?->name ?? '-') }}
                    </td>

                    <td class="px-6 py-4 font-semibold text-red-600">
                        {{ $expense->formatted_amount }}
                    </td>

                    <td class="px-6 py-4">
                        {{-- ✅ status_badge généré côté model — HTML de confiance --}}
                        {!! $expense->status_badge !!}
                    </td>

                    <td class="px-6 py-4 text-gray-600">
                        {{ $expense->expense_date?->format('d/m/Y') ?? '-' }}
                    </td>

                    <td class="px-6 py-4">
                        <div class="flex justify-end items-center gap-4">

                            @can('view expenses')
                            <a href="{{ route('admin.expenses.show', $expense) }}"
                               title="Voir"
                               class="text-blue-600 hover:text-blue-800 transition">
                                <x-heroicon-o-eye class="w-5 h-5"/>
                            </a>
                            @endcan

                            @can('approve expenses')
                            @if($expense->status === 'pending')
                            <form action="{{ route('admin.expenses.approve', $expense) }}"
                                  method="POST"
                                  onsubmit="return confirm('Approuver cette dépense ?')">
                                @csrf
                                <button type="submit"
                                        title="Approuver"
                                        class="text-green-600 hover:text-green-800 transition">
                                    <x-heroicon-o-check class="w-5 h-5"/>
                                </button>
                            </form>
                            @endif
                            @endcan

                            @can('cancel expenses')
                            @if($expense->status !== 'cancelled')
                            <form action="{{ route('admin.expenses.cancel', $expense) }}"
                                  method="POST"
                                  onsubmit="return confirm('Annuler cette dépense ?')">
                                @csrf
                                <button type="submit"
                                        title="Annuler"
                                        class="text-red-600 hover:text-red-800 transition">
                                    <x-heroicon-o-x-mark class="w-5 h-5"/>
                                </button>
                            </form>
                            @endif
                            @endcan

                        </div>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-16 text-gray-400">
                        <x-heroicon-o-document-text class="w-10 h-10 mx-auto mb-3 text-gray-300"/>
                        Aucune dépense trouvée
                    </td>
                </tr>
                @endforelse

                </tbody>
            </table>
        </div>
    </div>


    {{-- ================= PAGINATION ================= --}}
    <div>
        {{ $expenses->withQueryString()->links() }}
    </div>

</div>

@else
<div class="flex flex-col items-center justify-center py-24 text-gray-400">
    <x-heroicon-o-lock-closed class="w-12 h-12 mb-4 text-gray-300"/>
    <p class="text-lg font-medium">Accès non autorisé</p>
</div>
@endcan

</x-app-layout>