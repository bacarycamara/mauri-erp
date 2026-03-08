<x-app-layout>

<div class="max-w-7xl mx-auto space-y-8">

{{-- =====================================================
HEADER
===================================================== --}}
<div class="flex items-center justify-between">

    <div class="flex items-center gap-4">

        <div class="header-icon">
            <x-heroicon-o-arrows-right-left class="icon"/>
        </div>

        <div>
            <h1 class="page-title">
                Mouvements de Stock
            </h1>

            <p class="page-subtitle">
                Historique des entrées et sorties de stock
            </p>
        </div>

    </div>

    {{-- BUTTON OPEN MODAL --}}
    <button
        type="button"
        onclick="document.getElementById('movementModal')?.showModal()"
        class="btn-create group">

        <x-heroicon-o-plus class="icon rotate-hover"/>
        Nouveau mouvement

    </button>

</div>


{{-- =====================================================
TABLE
===================================================== --}}
<div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
    @include('admin.stock_movements.partials.table')
</div>

</div>


{{-- =====================================================
MODAL
===================================================== --}}
@include('admin.stock_movements.partials.form')


{{-- =====================================================
MAURIERP SAFE STYLE
===================================================== --}}
<style>

/* ICONS */
.icon{
    width:20px;
    height:20px;
}

/* HEADER ICON */
.header-icon{
    padding:12px;
    background:#e0e7ff;
    color:#4f46e5;
    border-radius:14px;
    transition:.25s ease;
}

.header-icon:hover{
    transform:scale(1.08) rotate(6deg);
}

/* TITLES */
.page-title{
    font-size:20px;
    font-weight:600;
    color:#1f2937;
}

.page-subtitle{
    font-size:12px;
    color:#6b7280;
}

/* CREATE BUTTON */
.btn-create{
    display:flex;
    align-items:center;
    gap:8px;
    background:#4f46e5;
    color:white;
    padding:10px 18px;
    border-radius:12px;
    font-size:14px;
    transition:.25s ease;
    box-shadow:0 2px 6px rgba(79,70,229,.15);
}

.btn-create:hover{
    background:#4338ca;
    transform:translateY(-1px);
    box-shadow:0 6px 14px rgba(79,70,229,.25);
}

/* ICON ROTATION */
.rotate-hover{
    transition:transform .3s;
}

.btn-create:hover .rotate-hover{
    transform:rotate(90deg);
}

</style>

</x-app-layout>