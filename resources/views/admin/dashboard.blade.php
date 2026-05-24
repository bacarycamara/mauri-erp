<x-app-layout>
@php
    $user     = auth()->user();
    $role     = $role ?? ($user->roles->first()?->name ?? 'Admin');
    $currency = company()?->currency ?? 'MRU';

    $canFinancials = $canFinancials ?? $user->can('view dashboard.financials');

    $roleLower      = strtolower(trim($role));
    $isAdmin        = in_array($roleLower, ['admin', 'super admin']);
    $isGestionnaire = $roleLower === 'gestionnaire';
    $isCaissier     = $roleLower === 'caissier';
    $isComptable    = $roleLower === 'comptable';
    $isMagasinier   = $roleLower === 'magasinier';
    $isCommercial   = $roleLower === 'commercial';
    $isLivreur      = $roleLower === 'livreur';

    // ✅ Whitelist badge — pas de $roleLower dans class directement
    $badgeClass = match(true) {
        $isAdmin        => 'bg-indigo-100 text-indigo-700',
        $isGestionnaire => 'bg-blue-100 text-blue-700',
        $isCaissier     => 'bg-green-100 text-green-700',
        $isComptable    => 'bg-purple-100 text-purple-700',
        $isMagasinier   => 'bg-orange-100 text-orange-700',
        $isCommercial   => 'bg-yellow-100 text-yellow-700',
        $isLivreur      => 'bg-gray-100 text-gray-600',
        default         => 'bg-gray-100 text-gray-700',
    };

    $recentSales     = collect($recentSales     ?? []);
    $recentPurchases = collect($recentPurchases ?? []);
    $openCash        = $openCashRegister ?? null;
    $isPositive      = ($profit ?? 0) >= 0;
@endphp

<div class="space-y-8">

{{-- ══ HEADER ══════════════════════════════════════════════════════════ --}}
<div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
    <div class="flex items-center gap-4">
        <div class="p-3 bg-indigo-100 rounded-2xl">
            <x-heroicon-o-chart-bar class="w-8 h-8 text-indigo-600"/>
        </div>
        <div>
            <h1 class="text-3xl font-bold text-gray-800 tracking-tight">Tableau de bord</h1>
            <p class="text-gray-500 flex items-center gap-2 flex-wrap text-sm">
                Bienvenue,
                {{-- ✅ e() sur le nom utilisateur --}}
                <span class="font-semibold text-gray-700">{{ e($user->name) }}</span>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $badgeClass }}">
                    {{-- ✅ e() sur le rôle --}}
                    {{ e($role) }}
                </span>
            </p>
        </div>
    </div>
    <div class="bg-white px-4 py-2 rounded-xl shadow text-sm text-gray-600">
        📅 {{ now()->translatedFormat('d F Y') }}
    </div>
</div>


{{-- ══ ACTIONS RAPIDES ═════════════════════════════════════════════════ --}}
@auth
<div class="grid grid-cols-2 md:grid-cols-4 gap-5">

    @can('create sales')
    <a href="{{ route('admin.sales.create') }}"
       class="group bg-white p-5 rounded-2xl shadow hover:shadow-xl transition transform
              hover:-translate-y-1 flex flex-col items-center gap-3">
        <div class="p-3 bg-green-100 rounded-xl group-hover:scale-110 transition">
            <x-heroicon-o-receipt-percent class="w-6 h-6 text-green-600"/>
        </div>
        <span class="font-medium text-gray-700 text-sm">Nouvelle Vente</span>
    </a>
    @endcan

    @can('create purchases')
    <a href="{{ route('admin.purchases.create') }}"
       class="group bg-white p-5 rounded-2xl shadow hover:shadow-xl transition transform
              hover:-translate-y-1 flex flex-col items-center gap-3">
        <div class="p-3 bg-red-100 rounded-xl group-hover:scale-110 transition">
            <x-heroicon-o-shopping-cart class="w-6 h-6 text-red-600"/>
        </div>
        <span class="font-medium text-gray-700 text-sm">Nouvel Achat</span>
    </a>
    @endcan

    @can('create products')
    <a href="{{ route('admin.products.create') }}"
       class="group bg-white p-5 rounded-2xl shadow hover:shadow-xl transition transform
              hover:-translate-y-1 flex flex-col items-center gap-3">
        <div class="p-3 bg-blue-100 rounded-xl group-hover:scale-110 transition">
            <x-heroicon-o-cube class="w-6 h-6 text-blue-600"/>
        </div>
        <span class="font-medium text-gray-700 text-sm">Nouveau Produit</span>
    </a>
    @endcan

    @can('create customers')
    <a href="{{ route('admin.customers.create') }}"
       class="group bg-white p-5 rounded-2xl shadow hover:shadow-xl transition transform
              hover:-translate-y-1 flex flex-col items-center gap-3">
        <div class="p-3 bg-indigo-100 rounded-xl group-hover:scale-110 transition">
            <x-heroicon-o-users class="w-6 h-6 text-indigo-600"/>
        </div>
        <span class="font-medium text-gray-700 text-sm">Nouveau Client</span>
    </a>
    @endcan

    @if($isCaissier)
    <a href="{{ route('admin.payments.create') }}"
       class="group bg-white p-5 rounded-2xl shadow hover:shadow-xl transition transform
              hover:-translate-y-1 flex flex-col items-center gap-3">
        <div class="p-3 bg-emerald-100 rounded-xl group-hover:scale-110 transition">
            <x-heroicon-o-banknotes class="w-6 h-6 text-emerald-600"/>
        </div>
        <span class="font-medium text-gray-700 text-sm">Nouveau Paiement</span>
    </a>
    @endif

    @can('create expenses')
    @if($isComptable)
    <a href="{{ route('admin.expenses.create') }}"
       class="group bg-white p-5 rounded-2xl shadow hover:shadow-xl transition transform
              hover:-translate-y-1 flex flex-col items-center gap-3">
        <div class="p-3 bg-purple-100 rounded-xl group-hover:scale-110 transition">
            <x-heroicon-o-banknotes class="w-6 h-6 text-purple-600"/>
        </div>
        <span class="font-medium text-gray-700 text-sm">Nouvelle Dépense</span>
    </a>
    @endif
    @endcan

    @can('view reports')
    @if($isComptable)
    <a href="{{ route('admin.reports.index') }}"
       class="group bg-white p-5 rounded-2xl shadow hover:shadow-xl transition transform
              hover:-translate-y-1 flex flex-col items-center gap-3">
        <div class="p-3 bg-indigo-100 rounded-xl group-hover:scale-110 transition">
            <x-heroicon-o-document-chart-bar class="w-6 h-6 text-indigo-600"/>
        </div>
        <span class="font-medium text-gray-700 text-sm">Rapports</span>
    </a>
    @endif
    @endcan

    @if($isMagasinier)
    <a href="{{ route('admin.stock-movements.index') }}"
       class="group bg-white p-5 rounded-2xl shadow hover:shadow-xl transition transform
              hover:-translate-y-1 flex flex-col items-center gap-3">
        <div class="p-3 bg-orange-100 rounded-xl group-hover:scale-110 transition">
            <x-heroicon-o-archive-box class="w-6 h-6 text-orange-600"/>
        </div>
        <span class="font-medium text-gray-700 text-sm">Gérer le stock</span>
    </a>
    @endif

</div>
@endauth


{{-- ══ KPI FINANCIERS GLOBAUX ══════════════════════════════════════════ --}}
@if($canFinancials)
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

    <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-6 rounded-3xl shadow-lg hover:scale-105 transition">
        <div class="flex justify-between items-center">
            <span class="text-xs uppercase opacity-80">Total Ventes</span>
            <x-heroicon-o-receipt-percent class="w-5 h-5 opacity-80"/>
        </div>
        <p class="text-2xl font-bold mt-3">{{ number_format((float)($totalSales ?? 0), 2) }} {{ $currency }}</p>
        <p class="text-xs opacity-70 mt-1">Ce mois : {{ number_format((float)($monthlySales ?? 0), 2) }} {{ $currency }}</p>
    </div>

    <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 text-white p-6 rounded-3xl shadow-lg hover:scale-105 transition">
        <div class="flex justify-between items-center">
            <span class="text-xs uppercase opacity-80">Total Achats</span>
            <x-heroicon-o-shopping-cart class="w-5 h-5 opacity-80"/>
        </div>
        <p class="text-2xl font-bold mt-3">{{ number_format((float)($totalPurchases ?? 0), 2) }} {{ $currency }}</p>
        <p class="text-xs opacity-70 mt-1">Ce mois : {{ number_format((float)($monthPurchases ?? 0), 2) }} {{ $currency }}</p>
    </div>

    <div class="bg-gradient-to-br from-red-500 to-red-600 text-white p-6 rounded-3xl shadow-lg hover:scale-105 transition">
        <div class="flex justify-between items-center">
            <span class="text-xs uppercase opacity-80">Dépenses</span>
            <x-heroicon-o-banknotes class="w-5 h-5 opacity-80"/>
        </div>
        <p class="text-2xl font-bold mt-3">{{ number_format((float)($totalExpenses ?? 0), 2) }} {{ $currency }}</p>
        <p class="text-xs opacity-70 mt-1">Ce mois : {{ number_format((float)($monthlyExpenses ?? 0), 2) }} {{ $currency }}</p>
    </div>

    <div class="bg-gradient-to-br {{ $isPositive ? 'from-green-500 to-green-600' : 'from-red-600 to-red-700' }} text-white p-6 rounded-3xl shadow-lg hover:scale-105 transition">
        <div class="flex justify-between items-center">
            <span class="text-xs uppercase opacity-80">Bénéfice Net</span>
            @if($isPositive)
                <x-heroicon-o-arrow-trending-up class="w-5 h-5 opacity-80"/>
            @else
                <x-heroicon-o-arrow-trending-down class="w-5 h-5 opacity-80"/>
            @endif
        </div>
        <p class="text-2xl font-bold mt-3">{{ number_format((float)($profit ?? 0), 2) }} {{ $currency }}</p>
        <p class="text-xs opacity-70 mt-1">{{ $isPositive ? '📈 Bénéfice' : '📉 Déficit' }}</p>
    </div>

</div>
@endif


{{-- ══ PERFORMANCE DÉTAILLÉE (Admin + Gestionnaire) ════════════════════ --}}
@if($isAdmin || $isGestionnaire)
<div class="grid grid-cols-2 md:grid-cols-4 gap-6">
    <div class="bg-white p-5 rounded-2xl shadow">
        <p class="text-gray-500 text-xs uppercase tracking-wide">Ventes aujourd'hui</p>
        <p class="text-xl font-bold text-green-600 mt-2">{{ number_format((float)($todaySales ?? 0), 2) }} {{ $currency }}</p>
    </div>
    <div class="bg-white p-5 rounded-2xl shadow">
        <p class="text-gray-500 text-xs uppercase tracking-wide">Ventes semaine</p>
        <p class="text-xl font-bold text-indigo-600 mt-2">{{ number_format((float)($weekSales ?? 0), 2) }} {{ $currency }}</p>
    </div>
    <div class="bg-white p-5 rounded-2xl shadow">
        <p class="text-gray-500 text-xs uppercase tracking-wide">Ventes ce mois</p>
        <p class="text-xl font-bold text-blue-600 mt-2">{{ number_format((float)($monthlySales ?? 0), 2) }} {{ $currency }}</p>
    </div>
    <div class="bg-white p-5 rounded-2xl shadow">
        <p class="text-gray-500 text-xs uppercase tracking-wide">Achats ce mois</p>
        <p class="text-xl font-bold text-red-500 mt-2">{{ number_format((float)($monthPurchases ?? 0), 2) }} {{ $currency }}</p>
    </div>
</div>
@endif


{{-- ══ KPI CAISSIER ════════════════════════════════════════════════════ --}}
@if($isCaissier)
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    <div class="bg-gradient-to-br from-green-500 to-green-600 text-white p-6 rounded-3xl shadow-lg hover:scale-105 transition">
        <div class="flex justify-between items-center">
            <span class="text-xs uppercase opacity-80">Ventes aujourd'hui</span>
            <x-heroicon-o-receipt-percent class="w-5 h-5 opacity-80"/>
        </div>
        <p class="text-2xl font-bold mt-3">{{ number_format((float)($todaySales ?? 0), 2) }} {{ $currency }}</p>
    </div>

    <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-6 rounded-3xl shadow-lg hover:scale-105 transition">
        <div class="flex justify-between items-center">
            <span class="text-xs uppercase opacity-80">Ventes semaine</span>
            <x-heroicon-o-calendar class="w-5 h-5 opacity-80"/>
        </div>
        <p class="text-2xl font-bold mt-3">{{ number_format((float)($weekSales ?? 0), 2) }} {{ $currency }}</p>
    </div>

    <div class="bg-gradient-to-br {{ $openCash ? 'from-emerald-500 to-emerald-600' : 'from-gray-400 to-gray-500' }} text-white p-6 rounded-3xl shadow-lg hover:scale-105 transition">
        <div class="flex justify-between items-center">
            <span class="text-xs uppercase opacity-80">Caisse active</span>
            <x-heroicon-o-banknotes class="w-5 h-5 opacity-80"/>
        </div>
        @if($openCash)
        <p class="text-2xl font-bold mt-3">{{ number_format((float)($openCash['current_balance'] ?? 0), 2) }} {{ $currency }}</p>
        {{-- ✅ e() sur le nom de la caisse --}}
        <p class="text-xs opacity-70 mt-1">{{ e($openCash['name'] ?? 'Caisse') }}</p>
        @else
        <p class="text-lg font-bold mt-3">Aucune caisse ouverte</p>
        @endif
    </div>

</div>

<div class="bg-white p-5 rounded-2xl shadow border-l-4 border-orange-400 max-w-xs">
    <p class="text-gray-500 text-xs uppercase">Factures impayées</p>
    <p class="text-2xl font-bold text-orange-600 mt-2">{{ (int)($unpaidSales ?? 0) }}</p>
</div>
@endif


{{-- ══ KPI MAGASINIER ══════════════════════════════════════════════════ --}}
@if($isMagasinier)
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white p-6 rounded-3xl shadow-lg hover:scale-105 transition">
        <div class="flex justify-between items-center">
            <span class="text-xs uppercase opacity-80">Stock faible</span>
            <x-heroicon-o-exclamation-triangle class="w-5 h-5 opacity-80"/>
        </div>
        <p class="text-2xl font-bold mt-3">{{ (int)($lowStockProducts ?? 0) }} produits</p>
        <p class="text-xs opacity-70 mt-1">Sous le minimum</p>
    </div>
    <div class="bg-gradient-to-br from-red-500 to-red-600 text-white p-6 rounded-3xl shadow-lg hover:scale-105 transition">
        <div class="flex justify-between items-center">
            <span class="text-xs uppercase opacity-80">Rupture stock</span>
            <x-heroicon-o-archive-box-x-mark class="w-5 h-5 opacity-80"/>
        </div>
        <p class="text-2xl font-bold mt-3">{{ (int)($outOfStockProducts ?? 0) }} produits</p>
        <p class="text-xs opacity-70 mt-1">Stock à zéro</p>
    </div>
    <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white p-6 rounded-3xl shadow-lg hover:scale-105 transition">
        <div class="flex justify-between items-center">
            <span class="text-xs uppercase opacity-80">Achats impayés</span>
            <x-heroicon-o-shopping-cart class="w-5 h-5 opacity-80"/>
        </div>
        <p class="text-2xl font-bold mt-3">{{ (int)($unpaidPurchases ?? 0) }}</p>
    </div>
</div>

<div class="bg-white p-6 rounded-3xl shadow">
    <div class="flex items-center justify-between mb-3">
        <h3 class="font-semibold text-gray-700 flex items-center gap-2">
            <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-yellow-500"/>
            Alertes stock
        </h3>
        <a href="{{ route('admin.stock-movements.index') }}" class="text-xs text-indigo-600 hover:underline">Gérer →</a>
    </div>
    <div class="flex flex-wrap gap-3 mt-3">
        <a href="{{ route('admin.products.index') }}"
           class="inline-flex items-center gap-2 bg-orange-100 text-orange-700 px-4 py-2 rounded-xl text-sm font-medium hover:bg-orange-200 transition">
            <x-heroicon-o-cube class="w-4 h-4"/>
            Voir les produits
        </a>
        <a href="{{ route('admin.stock-movements.index') }}"
           class="inline-flex items-center gap-2 bg-blue-100 text-blue-700 px-4 py-2 rounded-xl text-sm font-medium hover:bg-blue-200 transition">
            <x-heroicon-o-archive-box class="w-4 h-4"/>
            Mouvements stock
        </a>
    </div>
</div>
@endif


{{-- ══ KPI COMMERCIAL ══════════════════════════════════════════════════ --}}
@if($isCommercial)
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white p-6 rounded-3xl shadow-lg hover:scale-105 transition">
        <div class="flex justify-between items-center">
            <span class="text-xs uppercase opacity-80">Ventes aujourd'hui</span>
            <x-heroicon-o-receipt-percent class="w-5 h-5 opacity-80"/>
        </div>
        <p class="text-2xl font-bold mt-3">{{ number_format((float)($todaySales ?? 0), 2) }} {{ $currency }}</p>
    </div>
    <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white p-6 rounded-3xl shadow-lg hover:scale-105 transition">
        <div class="flex justify-between items-center">
            <span class="text-xs uppercase opacity-80">Factures impayées</span>
            <x-heroicon-o-document-text class="w-5 h-5 opacity-80"/>
        </div>
        <p class="text-2xl font-bold mt-3">{{ (int)($unpaidSales ?? 0) }}</p>
    </div>
    <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white p-6 rounded-3xl shadow-lg hover:scale-105 transition">
        <div class="flex justify-between items-center">
            <span class="text-xs uppercase opacity-80">Clients débiteurs</span>
            <x-heroicon-o-user-group class="w-5 h-5 opacity-80"/>
        </div>
        <p class="text-2xl font-bold mt-3">{{ (int)($debtCustomers ?? 0) }}</p>
    </div>
</div>
@endif


{{-- ══ KPI LIVREUR ═════════════════════════════════════════════════════ --}}
@if($isLivreur)
<div class="bg-white p-6 rounded-3xl shadow text-center">
    <x-heroicon-o-truck class="w-12 h-12 text-gray-400 mx-auto mb-3"/>
    <p class="text-gray-600 font-medium">
        Bonjour {{ e($user->name) }}, vos livraisons du jour.
    </p>
    <a href="{{ route('admin.sales.index') }}"
       class="mt-4 inline-flex items-center gap-2 bg-indigo-100 text-indigo-700
              px-5 py-2 rounded-xl text-sm font-medium hover:bg-indigo-200 transition">
        <x-heroicon-o-clipboard-document-list class="w-4 h-4"/>
        Voir les commandes
    </a>
</div>
@endif


{{-- ══ ALERTES GLOBALES (Admin) ════════════════════════════════════════ --}}
@if($isAdmin)
<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-5">
    <div class="bg-white p-5 rounded-2xl shadow border-l-4 border-yellow-400">
        <p class="text-gray-500 text-xs uppercase">Stock faible</p>
        <p class="text-2xl font-bold text-yellow-600 mt-2">{{ (int)($lowStockProducts ?? 0) }}</p>
    </div>
    <div class="bg-white p-5 rounded-2xl shadow border-l-4 border-red-500">
        <p class="text-gray-500 text-xs uppercase">Rupture stock</p>
        <p class="text-2xl font-bold text-red-600 mt-2">{{ (int)($outOfStockProducts ?? 0) }}</p>
    </div>
    <div class="bg-white p-5 rounded-2xl shadow border-l-4 border-orange-400">
        <p class="text-gray-500 text-xs uppercase">Factures impayées</p>
        <p class="text-2xl font-bold text-orange-600 mt-2">{{ (int)($unpaidSales ?? 0) }}</p>
    </div>
    <div class="bg-white p-5 rounded-2xl shadow border-l-4 border-rose-500">
        <p class="text-gray-500 text-xs uppercase">Achats impayés</p>
        <p class="text-2xl font-bold text-rose-600 mt-2">{{ (int)($unpaidPurchases ?? 0) }}</p>
    </div>
    <div class="bg-white p-5 rounded-2xl shadow border-l-4 border-purple-400">
        <p class="text-gray-500 text-xs uppercase">Clients débiteurs</p>
        <p class="text-2xl font-bold text-purple-600 mt-2">{{ (int)($debtCustomers ?? 0) }}</p>
    </div>
    <div class="bg-white p-5 rounded-2xl shadow border-l-4 border-pink-400">
        <p class="text-gray-500 text-xs uppercase">Fournisseurs créditeurs</p>
        <p class="text-2xl font-bold text-pink-600 mt-2">{{ (int)($creditSuppliers ?? 0) }}</p>
    </div>
    <div class="bg-white p-5 rounded-2xl shadow border-l-4 border-indigo-400">
        <p class="text-gray-500 text-xs uppercase">Utilisateurs actifs</p>
        <p class="text-2xl font-bold text-indigo-600 mt-2">{{ (int)($activeUsers ?? 0) }}</p>
    </div>
</div>
@endif


{{-- ══ CAISSE OUVERTE (Comptable) ══════════════════════════════════════ --}}
@if($isComptable && $openCash)
<div class="bg-white rounded-2xl border border-emerald-200 shadow-sm p-5">
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
                <x-heroicon-o-banknotes class="h-5 w-5 text-emerald-500"/>
            </div>
            <div>
                <div class="font-semibold text-gray-800">
                    Caisse ouverte : {{ e($openCash['name'] ?? 'Caisse') }}
                </div>
                <div class="text-xs text-gray-400">
                    Entrées : {{ number_format((float)($openCash['total_in'] ?? 0), 2) }} {{ $currency }}
                    · Sorties : {{ number_format((float)($openCash['total_out'] ?? 0), 2) }} {{ $currency }}
                </div>
            </div>
        </div>
        <div class="text-right">
            <div class="text-lg font-bold text-emerald-600">
                {{ number_format((float)($openCash['current_balance'] ?? 0), 2) }} {{ $currency }}
            </div>
            @if(!empty($openCash['id']))
            <a href="{{ route('admin.cash-registers.show', (int) $openCash['id']) }}"
               class="text-xs text-indigo-500 hover:text-indigo-700">
                Voir détail →
            </a>
            @endif
        </div>
    </div>
</div>
@endif


{{-- ══ ACTIVITÉ RÉCENTE ════════════════════════════════════════════════ --}}
@if($user->can('view sales') || $user->can('view purchases'))
<div class="grid md:grid-cols-2 gap-6">

    @if($user->can('view sales') && $recentSales->count())
    <div class="bg-white p-6 rounded-3xl shadow">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-700 flex items-center gap-2">
                <x-heroicon-o-receipt-percent class="w-5 h-5 text-green-500"/>
                Dernières ventes
            </h3>
            <a href="{{ route('admin.sales.index') }}" class="text-xs text-indigo-600 hover:underline">Voir tout →</a>
        </div>
        @foreach($recentSales as $sale)
        <div class="flex justify-between items-center py-2.5 border-b last:border-none">
            <div>
                {{-- ✅ e() sur la référence --}}
                <span class="font-medium text-gray-700">{{ e($sale['reference'] ?? '—') }}</span>
                <p class="text-xs text-gray-400">
                    {{ isset($sale['created_at']) ? \Carbon\Carbon::parse($sale['created_at'])->diffForHumans() : '' }}
                </p>
            </div>
            <span class="font-bold text-indigo-600">
                {{ number_format((float)($sale['total_amount'] ?? 0), 2) }} {{ $currency }}
            </span>
        </div>
        @endforeach
    </div>
    @endif

    @if($user->can('view purchases') && $recentPurchases->count())
    <div class="bg-white p-6 rounded-3xl shadow">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-700 flex items-center gap-2">
                <x-heroicon-o-shopping-cart class="w-5 h-5 text-red-500"/>
                Derniers achats
            </h3>
            <a href="{{ route('admin.purchases.index') }}" class="text-xs text-indigo-600 hover:underline">Voir tout →</a>
        </div>
        @foreach($recentPurchases as $purchase)
        <div class="flex justify-between items-center py-2.5 border-b last:border-none">
            <div>
                {{-- ✅ e() sur la référence --}}
                <span class="font-medium text-gray-700">{{ e($purchase['reference'] ?? '—') }}</span>
                <p class="text-xs text-gray-400">
                    {{ isset($purchase['created_at']) ? \Carbon\Carbon::parse($purchase['created_at'])->diffForHumans() : '' }}
                </p>
            </div>
            <span class="font-bold text-red-500">
                {{ number_format((float)($purchase['total_amount'] ?? 0), 2) }} {{ $currency }}
            </span>
        </div>
        @endforeach
    </div>
    @endif

</div>
@endif


{{-- ══ CHARTS ══════════════════════════════════════════════════════════ --}}
@if($canFinancials || $isCaissier || $isCommercial)
<div class="grid md:grid-cols-2 gap-6">

    @if($user->can('view sales'))
    <div class="bg-white p-6 rounded-3xl shadow">
        <div class="flex items-center gap-2 mb-4">
            <x-heroicon-o-chart-bar class="w-5 h-5 text-blue-600"/>
            <h3 class="font-semibold text-gray-700">Évolution des ventes {{ now()->year }}</h3>
        </div>
        <canvas id="salesChart"></canvas>
    </div>
    @endif

    @if($canFinancials && $user->can('view purchases'))
    <div class="bg-white p-6 rounded-3xl shadow">
        <div class="flex items-center gap-2 mb-4">
            <x-heroicon-o-chart-bar class="w-5 h-5 text-indigo-600"/>
            <h3 class="font-semibold text-gray-700">Évolution des achats {{ now()->year }}</h3>
        </div>
        <canvas id="purchaseChart"></canvas>
    </div>
    @endif

</div>
@endif


</div>{{-- /space-y-8 --}}


@push('scripts')
@if($user->can('view sales') || ($canFinancials && $user->can('view purchases')))
{{-- ✅ SRI hash idéalement mais on garde le CDN pour compatibilité --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    const monthLabels = ['Jan','Fév','Mar','Avr','Mai','Juin','Juil','Aoû','Sep','Oct','Nov','Déc'];

    @if($user->can('view sales'))
    const salesRaw    = @json($canFinancials ? ($salesChart ?? []) : ($salesChartCaissier ?? []));
    const salesValues = monthLabels.map((_, i) => parseFloat(salesRaw[i + 1]) || 0);
    const salesCtx = document.getElementById('salesChart');
    if (salesCtx) {
        new Chart(salesCtx, {
            type: 'line',
            data: { labels: monthLabels, datasets: [{
                label: 'Ventes', data: salesValues,
                borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)',
                fill: true, tension: 0.4, pointBackgroundColor: '#3b82f6', pointRadius: 4,
            }]},
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true }, x: { grid: { display: false } } }
            }
        });
    }
    @endif

    @if($canFinancials && $user->can('view purchases'))
    const purchaseRaw    = @json($purchaseChart ?? []);
    const purchaseValues = monthLabels.map((_, i) => parseFloat(purchaseRaw[i + 1]) || 0);
    const purchaseCtx = document.getElementById('purchaseChart');
    if (purchaseCtx) {
        new Chart(purchaseCtx, {
            type: 'bar',
            data: { labels: monthLabels, datasets: [{
                label: 'Achats', data: purchaseValues,
                backgroundColor: 'rgba(99,102,241,0.75)', borderRadius: 6,
            }]},
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true }, x: { grid: { display: false } } }
            }
        });
    }
    @endif
}());
</script>
@endif
@endpush

</x-app-layout>