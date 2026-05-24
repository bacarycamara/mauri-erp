<x-app-layout>

@can('view customers')

@php
    $currency = company()?->currency ?? '';
@endphp

<div class="max-w-7xl mx-auto space-y-8"
     x-data
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0">

    {{-- ================= ALERTS ================= --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-2xl flex items-center gap-3">
        <x-heroicon-o-check-circle class="w-5 h-5 text-green-600 flex-shrink-0"/>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-2xl flex items-center gap-3">
        <x-heroicon-o-x-circle class="w-5 h-5 text-red-600 flex-shrink-0"/>
        {{ session('error') }}
    </div>
    @endif


    {{-- ================= HEADER ================= --}}
    <div class="flex justify-between items-center gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <x-heroicon-o-users class="w-6 h-6 text-indigo-600"/>
                Gestion des Clients
            </h1>
            <p class="text-sm text-gray-500">{{ $customers->total() }} client(s)</p>
        </div>

        @can('create customers')
        <a href="{{ route('admin.customers.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700
                  text-white rounded-xl shadow-lg transition hover:scale-105">
            <x-heroicon-o-plus class="w-4 h-4"/>
            Nouveau client
        </a>
        @endcan
    </div>


    {{-- ================= STATS ================= --}}
    <div class="grid md:grid-cols-4 gap-6">

        <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500">Total Clients</span>
                <x-heroicon-o-users class="w-5 h-5 text-indigo-500"/>
            </div>
            <div class="text-2xl font-bold text-indigo-600 mt-2">
                {{ $stats['total_clients'] ?? 0 }}
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500">Clients Actifs</span>
                <x-heroicon-o-check-circle class="w-5 h-5 text-green-500"/>
            </div>
            <div class="text-2xl font-bold text-green-600 mt-2">
                {{ $stats['active_clients'] ?? 0 }}
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500">Clients Débiteurs</span>
                <x-heroicon-o-exclamation-circle class="w-5 h-5 text-red-500"/>
            </div>
            <div class="text-2xl font-bold text-red-600 mt-2">
                {{ $stats['debtors'] ?? 0 }}
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500">Dette Totale</span>
                <x-heroicon-o-banknotes class="w-5 h-5 text-red-500"/>
            </div>
            <div class="text-2xl font-bold text-red-600 mt-2">
                {{ number_format($stats['total_debt'] ?? 0, 2) }} {{ $currency }}
            </div>
        </div>

    </div>


    {{-- ================= FILTRES ================= --}}
    <div class="bg-white p-6 rounded-2xl shadow">
        <form method="GET" class="grid md:grid-cols-4 gap-4">

            <div class="relative">
                <x-heroicon-o-magnifying-glass class="w-4 h-4 absolute left-3 top-3 text-gray-400"/>
                <input type="text"
                       name="search"
                       value="{{ e(request('search')) }}"
                       maxlength="100"
                       placeholder="Rechercher nom, email..."
                       class="pl-9 rounded-xl border-gray-300 focus:ring-indigo-500 w-full">
            </div>

            <select name="status" class="rounded-xl border-gray-300 focus:ring-indigo-500">
                <option value="">Tous statuts</option>
                <option value="active"   @selected(request('status') === 'active')>Actifs</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactifs</option>
            </select>

            <select name="debt" class="rounded-xl border-gray-300 focus:ring-indigo-500">
                <option value="">Tous</option>
                <option value="yes" @selected(request('debt') === 'yes')>Débiteurs uniquement</option>
            </select>

            <div class="flex gap-2">
                <button type="submit"
                        class="flex-1 bg-indigo-600 text-white rounded-xl px-4 py-2 hover:bg-indigo-700 transition">
                    Filtrer
                </button>
                <a href="{{ route('admin.customers.index') }}"
                   class="flex-1 text-center border rounded-xl px-4 py-2 hover:bg-gray-100 transition">
                    Reset
                </a>
            </div>

        </form>
    </div>


    {{-- ================= TABLE ================= --}}
    <div class="bg-white rounded-2xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">

                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3 text-left">Client</th>
                    <th class="px-6 py-3 text-left">Contact</th>
                    <th class="px-6 py-3 text-left">Ville</th>
                    <th class="px-6 py-3 text-left">Ventes</th>
                    <th class="px-6 py-3 text-left">Solde</th>
                    <th class="px-6 py-3 text-left">Statut</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                @forelse($customers as $customer)
                <tr class="hover:bg-gray-50 transition">

                    <td class="px-6 py-4">
                        <div class="font-semibold text-gray-800 flex items-center gap-2">
                            <x-heroicon-o-user class="w-4 h-4 text-gray-400 flex-shrink-0"/>
                            {{ e($customer->name) }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ e($customer->email ?? '-') }}
                        </div>
                    </td>

                    <td class="px-6 py-4 text-gray-600">
                        {{ e($customer->phone ?? '-') }}
                    </td>

                    <td class="px-6 py-4 text-gray-600">
                        {{ e($customer->city ?? '-') }}
                    </td>

                    <td class="px-6 py-4">
                        <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-semibold">
                            {{ $customer->sales_count ?? 0 }}
                        </span>
                    </td>

                    <td class="px-6 py-4">
                        @if(($customer->current_balance ?? 0) > 0)
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                            {{ $customer->formatted_balance }}
                        </span>
                        @else
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                            0.00 {{ $currency }}
                        </span>
                        @endif
                    </td>

                    <td class="px-6 py-4">
                        {{-- ✅ Whitelist classes CSS — pas de $customer->status_badge direct dans class --}}
                        @if($customer->is_active)
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                            Actif
                        </span>
                        @else
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-600">
                            Inactif
                        </span>
                        @endif
                    </td>

                    <td class="px-6 py-4">
                        <div class="flex justify-end items-center gap-4">

                            @can('view customers')
                            <a href="{{ route('admin.customers.show', $customer) }}"
                               title="Voir"
                               class="text-indigo-600 hover:text-indigo-800 transition">
                                <x-heroicon-o-eye class="w-5 h-5"/>
                            </a>
                            @endcan

                            @can('edit customers')
                            <a href="{{ route('admin.customers.edit', $customer) }}"
                               title="Modifier"
                               class="text-blue-600 hover:text-blue-800 transition">
                                <x-heroicon-o-pencil-square class="w-5 h-5"/>
                            </a>
                            @endcan

                            @can('delete customers')
                            <form action="{{ route('admin.customers.destroy', $customer) }}"
                                  method="POST"
                                  onsubmit="return confirm('Supprimer ce client ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        title="Supprimer"
                                        class="text-red-500 hover:text-red-700 transition">
                                    <x-heroicon-o-trash class="w-5 h-5"/>
                                </button>
                            </form>
                            @endcan

                        </div>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-16 text-gray-400">
                        <x-heroicon-o-user-group class="w-10 h-10 mx-auto mb-3 text-gray-300"/>
                        Aucun client trouvé
                    </td>
                </tr>
                @endforelse

                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $customers->withQueryString()->links() }}
    </div>

</div>

@else
<div class="flex flex-col items-center justify-center py-24 text-gray-400">
    <x-heroicon-o-lock-closed class="w-12 h-12 mb-4 text-gray-300"/>
    <p class="text-lg font-medium">Accès non autorisé</p>
</div>
@endcan

</x-app-layout>