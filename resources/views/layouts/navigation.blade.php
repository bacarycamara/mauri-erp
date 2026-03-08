<aside
    x-data="{
        open: window.innerWidth >= 1024,
        isMobile: window.innerWidth < 1024
    }"
    x-init="
        window.addEventListener('resize', () => {
            isMobile = window.innerWidth < 1024;
            if (!isMobile) open = true;
        })
    "
    id="app-sidebar"
    :class="open && !isMobile ? 'w-72' : (!open && !isMobile ? 'w-20' : 'w-72')"
    class="h-screen bg-gradient-to-b from-indigo-950 via-indigo-900 to-indigo-800
           text-white flex flex-col shadow-2xl transition-all duration-300 ease-in-out
           lg:relative lg:translate-x-0"
>

@php
    $company      = $company ?? (auth()->check() ? company() : null);
    $currentRoute = request()->route()?->getName() ?? '';
    $isActive     = fn ($pattern) => str_starts_with($currentRoute, $pattern);

    $permissions = auth()->check()
        ? auth()->user()->getAllPermissions()->pluck('name')->toArray()
        : [];

    $can = fn($p) => in_array($p, $permissions);
@endphp


{{-- ── HEADER ── --}}
<div class="px-5 py-5 border-b border-indigo-700/40 flex items-center justify-between flex-shrink-0">
    <div class="flex items-center gap-3 overflow-hidden">
        <div class="h-11 w-11 rounded-2xl bg-white/10 backdrop-blur flex items-center justify-center shadow-lg overflow-hidden flex-shrink-0">
            @if($company?->logo)
                <img src="{{ $company->logo_url }}" class="h-full w-full object-cover">
            @else
                <span class="font-bold text-lg">{{ strtoupper(substr($company->name ?? 'M', 0, 1)) }}</span>
            @endif
        </div>

        <div x-show="open || isMobile" x-transition.opacity class="overflow-hidden">
            <div class="font-semibold text-sm truncate">{{ $company->name ?? 'MauriERP' }}</div>
            <div class="text-xs text-indigo-300">{{ $company->currency ?? 'MRU' }}</div>
        </div>
    </div>

    <div class="flex items-center gap-2">
        {{-- Bouton collapse desktop --}}
        <button @click="open=!open"
            class="hidden lg:flex text-indigo-300 hover:text-white transition"
            :class="open ? 'hover:rotate-180' : ''">
            <x-heroicon-o-bars-3 class="h-6 w-6"/>
        </button>

        {{-- Bouton fermer mobile --}}
        <button onclick="closeSidebar()"
            class="lg:hidden text-indigo-300 hover:text-white transition p-1">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>


{{-- ── NAVIGATION ── --}}
<nav class="flex-1 px-3 py-5 space-y-1 overflow-y-auto text-sm scrollbar-thin">

    {{-- DASHBOARD --}}
    @if($can('view dashboard'))
    <a href="{{ route('dashboard') }}"
       onclick="if(window.innerWidth < 1024) closeSidebar()"
       class="sidebar-link {{ $isActive('dashboard') ? 'sidebar-active' : '' }}">
        <x-heroicon-o-home class="w-5 h-5 shrink-0"/>
        <span x-show="open || isMobile" x-transition.opacity class="truncate">Dashboard</span>
    </a>
    @endif

    {{-- CATALOGUE --}}
    @if($can('view products') || $can('view categories'))
    @include('layouts.partials.sidebar-group', [
        'title'  => 'Catalogue',
        'icon'   => 'cube',
        'routes' => ['admin.products.*', 'admin.categories.*'],
        'links'  => [
            ['route' => 'admin.products.index',   'label' => 'Produits',    'permission' => 'view products'],
            ['route' => 'admin.categories.index', 'label' => 'Catégories', 'permission' => 'view categories'],
        ]
    ])
    @endif

    {{-- STOCK --}}
    @if($can('view stock_movements'))
    <a href="{{ route('admin.stock-movements.index') }}"
       onclick="if(window.innerWidth < 1024) closeSidebar()"
       class="sidebar-link {{ $isActive('admin.stock-movements') ? 'sidebar-active' : '' }}">
        <x-heroicon-o-arrows-right-left class="w-5 h-5 shrink-0"/>
        <span x-show="open || isMobile" x-transition.opacity class="truncate">Mouvements Stock</span>
    </a>
    @endif

    {{-- ACHATS --}}
    @if($can('view suppliers') || $can('view purchases'))
    @include('layouts.partials.sidebar-group', [
        'title'  => 'Achats',
        'icon'   => 'shopping-cart',
        'routes' => ['admin.suppliers.*', 'admin.purchases.*'],
        'links'  => [
            ['route' => 'admin.suppliers.index', 'label' => 'Fournisseurs', 'permission' => 'view suppliers'],
            ['route' => 'admin.purchases.index', 'label' => 'Achats',       'permission' => 'view purchases'],
        ]
    ])
    @endif

    {{-- VENTES --}}
    @if($can('view customers') || $can('view sales') || $can('view payments'))
    @include('layouts.partials.sidebar-group', [
        'title'  => 'Ventes',
        'icon'   => 'receipt-percent',
        'routes' => ['admin.customers.*', 'admin.sales.*', 'admin.payments.*'],
        'links'  => [
            ['route' => 'admin.customers.index', 'label' => 'Clients',    'permission' => 'view customers'],
            ['route' => 'admin.sales.index',     'label' => 'Ventes',     'permission' => 'view sales'],
            ['route' => 'admin.payments.index',  'label' => 'Paiements',  'permission' => 'view payments'],
        ]
    ])
    @endif

    {{-- FINANCE --}}
    @if($can('view cash_registers') || $can('view cash_transactions') || $can('view expenses'))
    @include('layouts.partials.sidebar-group', [
        'title'  => 'Finance',
        'icon'   => 'banknotes',
        'routes' => ['admin.cash-registers.*', 'admin.cash-transactions.*', 'admin.expenses.*'],
        'links'  => [
            ['route' => 'admin.cash-registers.index',    'label' => 'Caisses',      'permission' => 'view cash_registers'],
            ['route' => 'admin.cash-transactions.index', 'label' => 'Transactions', 'permission' => 'view cash_transactions'],
            ['route' => 'admin.expenses.index',          'label' => 'Dépenses',     'permission' => 'view expenses'],
        ]
    ])
    @endif

    {{-- RAPPORTS --}}
    @if($can('view reports'))
    <a href="{{ route('admin.reports.index') }}"
       onclick="if(window.innerWidth < 1024) closeSidebar()"
       class="sidebar-link {{ $isActive('admin.reports') ? 'sidebar-active' : '' }}">
        <x-heroicon-o-chart-bar class="w-5 h-5 shrink-0"/>
        <span x-show="open || isMobile" x-transition.opacity class="truncate">Rapports</span>
    </a>
    @endif

    <div class="border-t border-indigo-700/40 my-3"></div>

    {{-- UTILISATEURS --}}
    @if($can('view users'))
    <a href="{{ route('admin.users.index') }}"
       onclick="if(window.innerWidth < 1024) closeSidebar()"
       class="sidebar-link {{ $isActive('admin.users') ? 'sidebar-active' : '' }}">
        <x-heroicon-o-users class="w-5 h-5 shrink-0"/>
        <span x-show="open || isMobile" x-transition.opacity class="truncate">Utilisateurs</span>
    </a>
    @endif

    {{-- RÔLES --}}
    @if($can('view roles'))
    <a href="{{ route('admin.roles.index') }}"
       onclick="if(window.innerWidth < 1024) closeSidebar()"
       class="sidebar-link {{ $isActive('admin.roles') ? 'sidebar-active' : '' }}">
        <x-heroicon-o-lock-closed class="w-5 h-5 shrink-0"/>
        <span x-show="open || isMobile" x-transition.opacity class="truncate">Rôles</span>
    </a>
    @endif

    {{-- ENTREPRISE --}}
    @if($can('view company'))
    <a href="{{ route('admin.company.edit') }}"
       onclick="if(window.innerWidth < 1024) closeSidebar()"
       class="sidebar-link {{ $isActive('admin.company') ? 'sidebar-active' : '' }}">
        <x-heroicon-o-cog-6-tooth class="w-5 h-5 shrink-0"/>
        <span x-show="open || isMobile" x-transition.opacity class="truncate">Entreprise</span>
    </a>
    @endif

    {{-- SAUVEGARDES --}}
    @if($can('view settings'))
    <a href="{{ route('admin.backups.index') }}"
       onclick="if(window.innerWidth < 1024) closeSidebar()"
       class="sidebar-link {{ $isActive('admin.backups') ? 'sidebar-active' : '' }}">
        <x-heroicon-o-archive-box-arrow-down class="w-5 h-5 shrink-0"/>
        <span x-show="open || isMobile" x-transition.opacity class="truncate">Sauvegardes</span>
    </a>
    @endif

    {{-- LOGS --}}
    @if($can('view audit_logs'))
    <a href="{{ route('admin.audit-logs.index') }}"
       onclick="if(window.innerWidth < 1024) closeSidebar()"
       class="sidebar-link {{ $isActive('admin.audit-logs') ? 'sidebar-active' : '' }}">
        <x-heroicon-o-document-text class="w-5 h-5 shrink-0"/>
        <span x-show="open || isMobile" x-transition.opacity class="truncate">Logs</span>
    </a>
    @endif

</nav>


{{-- ── FOOTER ── --}}
<div class="border-t border-indigo-700/40 p-4 text-xs text-indigo-300 text-center flex-shrink-0">
    <div x-show="open || isMobile" x-transition.opacity>
        <div class="font-semibold text-white">MauriERP</div>
        <div>Powered by Bacary Camara</div>
        <div class="text-[10px] opacity-70">© {{ date('Y') }}</div>
    </div>

    <div x-show="!open && !isMobile" class="flex justify-center">
        <span class="text-[10px] font-bold">ME</span>
    </div>
</div>

</aside>