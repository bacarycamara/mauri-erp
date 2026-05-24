<x-app-layout>

@can('view stock_movements')

@php
    $typeLabels = [
        'purchase'   => 'Entrée (Achat)',
        'sale'       => 'Sortie (Vente)',
        'adjustment' => 'Ajustement',
        'return'     => 'Retour',
    ];
@endphp

<div style="max-width:1280px; margin:0 auto; padding:2rem 1.25rem 3rem;
            display:flex; flex-direction:column; gap:1.5rem;
            font-family:'DM Sans',sans-serif;">

    {{-- ================= HEADER ================= --}}
    <div style="display:flex; align-items:center; justify-content:space-between;
                flex-wrap:wrap; gap:1rem;">

        <div style="display:flex; align-items:center; gap:1rem;">
            <div style="width:52px; height:52px; flex-shrink:0;
                        background:linear-gradient(135deg,#6366f1,#4338ca);
                        border-radius:16px; display:flex; align-items:center;
                        justify-content:center; color:white;
                        box-shadow:0 6px 18px rgba(79,70,229,.28);">
                <x-heroicon-o-arrows-right-left style="width:24px; height:24px;"/>
            </div>
            <div>
                <h1 style="font-size:22px; font-weight:800; color:#1e1b4b;
                           letter-spacing:-.4px; margin:0;">
                    Mouvements de Stock
                </h1>
                <p style="font-size:12px; color:#9ca3af; margin-top:3px;">
                    Historique en temps réel · MauriERP
                </p>
            </div>
        </div>

        @can('create stock_movements')
        <button type="button"
                onclick="document.getElementById('movementModal')?.showModal()"
                style="display:flex; align-items:center; gap:8px;
                       background:linear-gradient(135deg,#6366f1,#4338ca);
                       color:white; padding:11px 22px; border-radius:13px;
                       font-size:14px; font-weight:600; cursor:pointer;
                       border:none; box-shadow:0 4px 14px rgba(79,70,229,.28);
                       transition:.28s;">
            <x-heroicon-o-plus style="width:17px; height:17px;"/>
            Nouveau mouvement
        </button>
        @endcan

    </div>


    {{-- ================= STATS ================= --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(210px,1fr)); gap:1rem;">

        @php
            $statsConfig = [
                ['key' => null,         'label' => 'Mouvements', 'sub' => 'Total',              'value' => $movements->total(), 'color' => '#4f46e5', 'pct' => 100],
                ['key' => 'purchase',   'label' => 'Entrées',    'sub' => 'Entrées stock',       'value' => $stats['purchase']   ?? 0, 'color' => '#059669', 'pct' => $statsPercent['purchase']   ?? 0],
                ['key' => 'sale',       'label' => 'Ventes',     'sub' => 'Sorties stock',       'value' => $stats['sale']       ?? 0, 'color' => '#e11d48', 'pct' => $statsPercent['sale']       ?? 0],
                ['key' => 'adjustment', 'label' => 'Ajust.',     'sub' => 'Ajustements',         'value' => $stats['adjustment'] ?? 0, 'color' => '#d97706', 'pct' => $statsPercent['adjustment'] ?? 0],
            ];
        @endphp

        @foreach($statsConfig as $sc)
        <div style="background:#fff; border-radius:18px; padding:1.3rem 1.4rem;
                    border:1px solid #ede9fe; box-shadow:0 1px 12px rgba(79,70,229,.06);">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:.9rem;">
                <div style="width:36px; height:36px; border-radius:11px;
                            display:flex; align-items:center; justify-content:center;
                            background:#ede9fe; color:{{ $sc['color'] }};">
                    <x-heroicon-o-arrows-right-left style="width:15px; height:15px;"/>
                </div>
                <span style="font-size:11px; font-weight:700; padding:3px 9px;
                             border-radius:99px; background:#ede9fe; color:#4f46e5;">
                    {{ $sc['label'] }}
                </span>
            </div>
            <div style="font-size:36px; font-weight:800; color:#1e1b4b;
                        line-height:1; margin-bottom:4px; letter-spacing:-1px;"
                 class="smx-count"
                 data-target="{{ $sc['value'] }}">
                0
            </div>
            <div style="font-size:11px; color:#9ca3af; text-transform:uppercase;
                        letter-spacing:.6px; font-weight:500; margin-bottom:.85rem;">
                {{ $sc['sub'] }}
            </div>
            <div style="height:4px; background:#f3f4f6; border-radius:99px; overflow:hidden;">
                <div style="height:100%; width:{{ min((int)$sc['pct'], 100) }}%;
                            background:{{ $sc['color'] }}; border-radius:99px;
                            transition:width 1.5s ease;">
                </div>
            </div>
        </div>
        @endforeach

    </div>


    {{-- ================= FILTRES ================= --}}
    <div style="background:#fff; border-radius:18px; border:1px solid #ede9fe;
                box-shadow:0 1px 12px rgba(79,70,229,.06); overflow:hidden;">

        <div style="display:flex; align-items:center; gap:.5rem;
                    padding:.9rem 1.4rem; border-bottom:1px solid #f5f3ff; background:#fafafe;">
            <x-heroicon-o-funnel style="width:15px; height:15px; color:#9ca3af;"/>
            <span style="font-size:13px; font-weight:700; color:#1e1b4b;">Filtres</span>
            @if(request()->hasAny(['search','product_id','type']))
            <span style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:99px;
                         background:#ede9fe; color:#4f46e5; margin-left:4px;">
                ● Actifs
            </span>
            @endif
        </div>

        <form method="GET"
              style="display:flex; flex-wrap:wrap; gap:.7rem;
                     align-items:center; padding:1.1rem 1.4rem;">

            <div style="position:relative; flex:1.5; min-width:200px;">
                <x-heroicon-o-magnifying-glass style="position:absolute; left:11px; top:50%;
                    transform:translateY(-50%); color:#9ca3af; width:15px; height:15px;
                    pointer-events:none;"/>
                <input type="text"
                       name="search"
                       value="{{ e(request('search')) }}"
                       maxlength="100"
                       placeholder="Rechercher une référence..."
                       style="width:100%; padding:.58rem .9rem .58rem 36px;
                              border:1.5px solid #e8e4ff; border-radius:11px;
                              font-size:13.5px; color:#374151; background:white;
                              outline:none; font-family:'DM Sans',sans-serif;">
            </div>

            <select name="product_id"
                    style="flex:1; min-width:150px; padding:.58rem .9rem;
                           border:1.5px solid #e8e4ff; border-radius:11px;
                           font-size:13.5px; color:#374151; background:white;
                           outline:none; font-family:'DM Sans',sans-serif;">
                <option value="">Tous les produits</option>
                @foreach($products as $p)
                <option value="{{ $p->id }}" @selected(request('product_id') == $p->id)>
                    {{ e($p->name) }}
                </option>
                @endforeach
            </select>

            <select name="type"
                    style="flex:1; min-width:150px; padding:.58rem .9rem;
                           border:1.5px solid #e8e4ff; border-radius:11px;
                           font-size:13.5px; color:#374151; background:white;
                           outline:none; font-family:'DM Sans',sans-serif;">
                <option value="">Tous les types</option>
                @foreach($typeLabels as $val => $label)
                <option value="{{ $val }}" @selected(request('type') === $val)>
                    {{ $label }}
                </option>
                @endforeach
            </select>

            <div style="display:flex; gap:.5rem;">
                <button type="submit"
                        style="display:flex; align-items:center; gap:6px;
                               background:linear-gradient(135deg,#6366f1,#4338ca);
                               color:white; padding:.58rem 1.2rem; border-radius:11px;
                               font-size:13.5px; font-weight:600; border:none;
                               cursor:pointer; font-family:'DM Sans',sans-serif;">
                    <x-heroicon-o-magnifying-glass style="width:15px; height:15px;"/>
                    Filtrer
                </button>
                @if(request()->hasAny(['search','product_id','type']))
                <a href="{{ route('admin.stock-movements.index') }}"
                   style="display:flex; align-items:center; gap:6px;
                          background:#f5f3ff; color:#6b7280; padding:.58rem 1rem;
                          border-radius:11px; font-size:13.5px; text-decoration:none;
                          border:1.5px solid #e8e4ff; font-family:'DM Sans',sans-serif;">
                    <x-heroicon-o-x-mark style="width:15px; height:15px;"/>
                    Reset
                </a>
                @endif
            </div>

        </form>
    </div>


    {{-- ================= TABLE ================= --}}
    <div style="background:#fff; border-radius:18px; border:1px solid #ede9fe;
                box-shadow:0 1px 12px rgba(79,70,229,.06); overflow:hidden;">

        <div style="display:flex; align-items:center; gap:.5rem;
                    padding:.9rem 1.4rem; border-bottom:1px solid #f5f3ff; background:#fafafe;">
            <x-heroicon-o-table-cells style="width:15px; height:15px; color:#9ca3af;"/>
            <span style="font-size:13px; font-weight:700; color:#1e1b4b;">Historique complet</span>
            <span style="margin-left:auto; font-size:12px; font-weight:700; padding:3px 11px;
                         border-radius:99px; background:linear-gradient(135deg,#ede9fe,#ddd6fe);
                         color:#4f46e5;">
                {{ $movements->total() }} entrée{{ $movements->total() > 1 ? 's' : '' }}
            </span>
        </div>

        <div style="overflow-x:auto;">
            @include('admin.stock_movements.partials.table')
        </div>

    </div>

</div>

@else
<div class="flex flex-col items-center justify-center py-24 text-gray-400">
    <x-heroicon-o-lock-closed class="w-12 h-12 mb-4 text-gray-300"/>
    <p class="text-lg font-medium">Accès non autorisé</p>
</div>
@endcan


{{-- ================= MODAL ================= --}}
@can('create stock_movements')
@include('admin.stock_movements.partials.form')
@endcan


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.smx-count').forEach(el => {
        // ✅ parseInt avec radix 10 — sécurité
        const target = parseInt(el.dataset.target, 10) || 0;
        if (!target) { el.textContent = '0'; return; }

        let t0  = null;
        const dur  = 1400;
        const ease = p => 1 - Math.pow(1 - p, 4);

        const run = ts => {
            if (!t0) t0 = ts;
            const p = Math.min((ts - t0) / dur, 1);
            el.textContent = Math.floor(ease(p) * target).toLocaleString('fr-FR');
            if (p < 1) requestAnimationFrame(run);
            else el.textContent = target.toLocaleString('fr-FR');
        };

        setTimeout(() => requestAnimationFrame(run), 300);
    });
});
</script>
@endpush

</x-app-layout>