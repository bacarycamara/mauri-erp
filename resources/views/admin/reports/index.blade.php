<x-app-layout>

@can('view reports')

<div class="max-w-6xl mx-auto space-y-6 py-6">

    {{-- ================= HEADER ================= --}}
    <div class="flex justify-between items-center flex-wrap gap-4">
        <div>
            <h1 class="flex items-center gap-3 text-xl font-semibold text-gray-800">
                <div class="p-2 bg-indigo-100 rounded-xl flex items-center justify-center">
                    <x-heroicon-o-chart-bar class="w-5 h-5 text-indigo-600"/>
                </div>
                Centre des Rapports
            </h1>
            <p class="text-xs text-gray-500 mt-1 max-w-lg">
                Analyse stratégique des performances financières, commerciales
                et opérationnelles de votre entreprise.
            </p>
        </div>
        <div class="hidden md:flex items-center gap-1 bg-indigo-50 text-indigo-700
                    px-3 py-1.5 rounded-full text-xs font-semibold">
            <x-heroicon-o-sparkles class="w-3.5 h-3.5"/>
            MauriERP Analytics
        </div>
    </div>


    {{-- ================= GRILLE RAPPORTS ================= --}}
    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">

        {{-- FINANCIER --}}
        <a href="{{ route('admin.reports.financial') }}"
           class="group bg-white border border-indigo-100 rounded-2xl p-5 shadow-sm
                  hover:shadow-md hover:-translate-y-1 transition duration-200">
            <div class="flex items-center gap-3 mb-3">
                <div class="p-2.5 bg-indigo-100 rounded-xl">
                    <x-heroicon-o-chart-bar class="w-5 h-5 text-indigo-600"/>
                </div>
                <h2 class="font-semibold text-gray-800">Rapport Financier</h2>
            </div>
            <p class="text-xs text-gray-500">Bénéfice net, encaissements et performance globale.</p>
        </a>

        {{-- VENTES --}}
        <a href="{{ route('admin.reports.sales') }}"
           class="group bg-white border border-green-100 rounded-2xl p-5 shadow-sm
                  hover:shadow-md hover:-translate-y-1 transition duration-200">
            <div class="flex items-center gap-3 mb-3">
                <div class="p-2.5 bg-green-100 rounded-xl">
                    <x-heroicon-o-receipt-percent class="w-5 h-5 text-green-600"/>
                </div>
                <h2 class="font-semibold text-gray-800">Rapport Ventes</h2>
            </div>
            <p class="text-xs text-gray-500">Analyse du chiffre d'affaires et des clients.</p>
        </a>

        {{-- ACHATS --}}
        <a href="{{ route('admin.reports.purchases') }}"
           class="group bg-white border border-red-100 rounded-2xl p-5 shadow-sm
                  hover:shadow-md hover:-translate-y-1 transition duration-200">
            <div class="flex items-center gap-3 mb-3">
                <div class="p-2.5 bg-red-100 rounded-xl">
                    <x-heroicon-o-shopping-cart class="w-5 h-5 text-red-600"/>
                </div>
                <h2 class="font-semibold text-gray-800">Rapport Achats</h2>
            </div>
            <p class="text-xs text-gray-500">Suivi fournisseurs et approvisionnements.</p>
        </a>

        {{-- DÉPENSES --}}
        <a href="{{ route('admin.reports.expenses') }}"
           class="group bg-white border border-orange-100 rounded-2xl p-5 shadow-sm
                  hover:shadow-md hover:-translate-y-1 transition duration-200">
            <div class="flex items-center gap-3 mb-3">
                <div class="p-2.5 bg-orange-100 rounded-xl">
                    <x-heroicon-o-banknotes class="w-5 h-5 text-orange-600"/>
                </div>
                <h2 class="font-semibold text-gray-800">Rapport Dépenses</h2>
            </div>
            <p class="text-xs text-gray-500">Impact des dépenses sur la rentabilité.</p>
        </a>

        {{-- STOCK --}}
        <a href="{{ route('admin.reports.stock') }}"
           class="group bg-white border border-blue-100 rounded-2xl p-5 shadow-sm
                  hover:shadow-md hover:-translate-y-1 transition duration-200">
            <div class="flex items-center gap-3 mb-3">
                <div class="p-2.5 bg-blue-100 rounded-xl">
                    <x-heroicon-o-archive-box class="w-5 h-5 text-blue-600"/>
                </div>
                <h2 class="font-semibold text-gray-800">Rapport Stock</h2>
            </div>
            <p class="text-xs text-gray-500">Produits en rupture et niveaux de stock.</p>
        </a>

        {{-- DASHBOARD GLOBAL --}}
        <a href="{{ route('admin.reports.dashboard') }}"
           class="relative overflow-hidden bg-gradient-to-br from-indigo-600 to-indigo-900
                  text-white rounded-2xl p-5 shadow-md hover:shadow-xl
                  hover:-translate-y-1 transition duration-200 group">
            <div class="absolute inset-0 opacity-0 group-hover:opacity-10 bg-white transition"></div>
            <div class="flex items-center gap-3 mb-3">
                <x-heroicon-o-presentation-chart-line class="w-6 h-6 opacity-90"/>
                <h2 class="font-semibold text-base">Dashboard Global ERP</h2>
            </div>
            <p class="text-xs opacity-80">Vue consolidée des performances globales.</p>
        </a>

    </div>

</div>

@else
<div class="flex flex-col items-center justify-center py-24 text-gray-400">
    <x-heroicon-o-lock-closed class="w-12 h-12 mb-4 text-gray-300"/>
    <p class="text-lg font-medium">Accès non autorisé</p>
</div>
@endcan

</x-app-layout>