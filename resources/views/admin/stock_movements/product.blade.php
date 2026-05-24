<x-app-layout>

@can('view stock_movements')

@php
    // ✅ Whitelist stock_status pour les classes CSS
    $safeStockStatus = in_array($product->stock_status ?? '', ['rupture','faible','ok'])
        ? ($product->stock_status ?? 'ok')
        : 'ok';

    $statusColor = match($safeStockStatus) {
        'rupture' => 'rose',
        'faible'  => 'amber',
        default   => 'emerald',
    };

    $statusLabel = match($safeStockStatus) {
        'rupture' => 'Rupture',
        'faible'  => 'Faible',
        default   => 'OK',
    };

    $stockQty = (int) ($product->stock_quantity ?? 0);
    $minStock = (int) ($product->minimum_stock  ?? 0);
    $stockPct = ($minStock > 0 || $stockQty > 0)
        ? min(100, (int) round($stockQty / max($stockQty, $minStock, 1) * 100))
        : 100;
@endphp

<div style="max-width:1280px; margin:0 auto; padding:2rem 1.25rem 3rem;
            display:flex; flex-direction:column; gap:1.5rem;
            font-family:system-ui,sans-serif;">

    {{-- ================= HEADER ================= --}}
    <div style="display:flex; align-items:center; justify-content:space-between;
                flex-wrap:wrap; gap:1rem;">

        <div style="display:flex; align-items:center; gap:1rem;">
            <div style="width:52px; height:52px; flex-shrink:0;
                        background:linear-gradient(135deg,#6366f1,#4338ca);
                        border-radius:16px; display:flex; align-items:center;
                        justify-content:center; color:white;
                        box-shadow:0 6px 18px rgba(79,70,229,.28);">
                <x-heroicon-o-cube style="width:22px; height:22px;"/>
            </div>
            <div>
                <h1 style="font-size:22px; font-weight:800; color:#1e1b4b;
                           letter-spacing:-.4px; margin:0;">
                    {{-- ✅ e() sur le nom du produit --}}
                    {{ e($product->name) }}
                </h1>
                <p style="font-size:12px; color:#9ca3af; margin-top:3px;">
                    Historique des mouvements de stock
                </p>
            </div>
        </div>

        <a href="{{ route('admin.stock-movements.index') }}"
           style="display:flex; align-items:center; gap:7px; padding:9px 16px;
                  border-radius:12px; border:1.5px solid #e8e4ff;
                  font-size:13.5px; font-weight:500; color:#6b7280;
                  text-decoration:none; transition:.22s;">
            <x-heroicon-o-arrow-left style="width:15px; height:15px;"/>
            Retour
        </a>

    </div>


    {{-- ================= KPI CARDS ================= --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:1rem;">

        {{-- Stock actuel --}}
        <div style="background:#fff; border-radius:18px; padding:1.3rem 1.4rem;
                    border:1px solid #ede9fe; box-shadow:0 1px 12px rgba(79,70,229,.06);">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:.9rem;">
                <div style="width:36px; height:36px; border-radius:11px; background:#ede9fe;
                            color:#4f46e5; display:flex; align-items:center; justify-content:center;">
                    <x-heroicon-o-cube style="width:15px; height:15px;"/>
                </div>
                <span style="font-size:11px; font-weight:700; padding:3px 9px; border-radius:99px;
                             background:#ede9fe; color:#4f46e5;">Stock</span>
            </div>
            <div style="font-size:36px; font-weight:800; color:#1e1b4b; line-height:1;
                        margin-bottom:4px; letter-spacing:-1px;"
                 class="spx-count" data-target="{{ $stockQty }}">0</div>
            <div style="font-size:11px; color:#9ca3af; text-transform:uppercase;
                        letter-spacing:.6px; font-weight:500; margin-bottom:.85rem;">
                Stock actuel
            </div>
            <div style="height:4px; background:#f3f4f6; border-radius:99px; overflow:hidden;">
                <div style="height:100%; width:{{ $stockPct }}%; border-radius:99px;
                            background:linear-gradient(90deg,#a5b4fc,#4f46e5); transition:width 1.5s ease;">
                </div>
            </div>
        </div>

        {{-- Stock minimum --}}
        <div style="background:#fff; border-radius:18px; padding:1.3rem 1.4rem;
                    border:1px solid #ede9fe; box-shadow:0 1px 12px rgba(79,70,229,.06);">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:.9rem;">
                <div style="width:36px; height:36px; border-radius:11px; background:#fef3c7;
                            color:#d97706; display:flex; align-items:center; justify-content:center;">
                    <x-heroicon-o-exclamation-triangle style="width:15px; height:15px;"/>
                </div>
                <span style="font-size:11px; font-weight:700; padding:3px 9px; border-radius:99px;
                             background:#fef3c7; color:#d97706;">Minimum</span>
            </div>
            <div style="font-size:36px; font-weight:800; color:#1e1b4b; line-height:1;
                        margin-bottom:4px; letter-spacing:-1px;"
                 class="spx-count" data-target="{{ $minStock }}">0</div>
            <div style="font-size:11px; color:#9ca3af; text-transform:uppercase;
                        letter-spacing:.6px; font-weight:500; margin-bottom:.85rem;">
                Stock minimum
            </div>
            <div style="height:4px; background:#f3f4f6; border-radius:99px; overflow:hidden;">
                <div style="height:100%; width:50%; border-radius:99px;
                            background:linear-gradient(90deg,#fcd34d,#d97706);">
                </div>
            </div>
        </div>

        {{-- Statut --}}
        <div style="background:#fff; border-radius:18px; padding:1.3rem 1.4rem;
                    border:1px solid #ede9fe; box-shadow:0 1px 12px rgba(79,70,229,.06);">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:.9rem;">
                <div style="width:36px; height:36px; border-radius:11px; background:#ede9fe;
                            color:#4f46e5; display:flex; align-items:center; justify-content:center;">
                    <x-heroicon-o-signal style="width:15px; height:15px;"/>
                </div>
                {{-- ✅ Whitelist — pas de $statusColor depuis DB directement dans style --}}
                <span style="font-size:11px; font-weight:700; padding:3px 9px; border-radius:99px;
                             background:{{ $safeStockStatus === 'rupture' ? '#ffe4e6' : ($safeStockStatus === 'faible' ? '#fef3c7' : '#d1fae5') }};
                             color:{{ $safeStockStatus === 'rupture' ? '#e11d48' : ($safeStockStatus === 'faible' ? '#d97706' : '#059669') }};">
                    Statut
                </span>
            </div>
            <div style="font-size:24px; font-weight:800; color:#1e1b4b; line-height:1; margin-bottom:4px;">
                {{ $statusLabel }}
            </div>
            <div style="font-size:11px; color:#9ca3af; text-transform:uppercase;
                        letter-spacing:.6px; font-weight:500; margin-bottom:.85rem;">
                État du stock
            </div>
            <div style="height:4px; background:#f3f4f6; border-radius:99px; overflow:hidden;">
                <div style="height:100%; border-radius:99px;
                            width:{{ $safeStockStatus === 'rupture' ? '5' : ($safeStockStatus === 'faible' ? '35' : '85') }}%;
                            background:{{ $safeStockStatus === 'rupture' ? '#e11d48' : ($safeStockStatus === 'faible' ? '#d97706' : '#059669') }};">
                </div>
            </div>
        </div>

        {{-- Nb mouvements --}}
        <div style="background:#fff; border-radius:18px; padding:1.3rem 1.4rem;
                    border:1px solid #ede9fe; box-shadow:0 1px 12px rgba(79,70,229,.06);">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:.9rem;">
                <div style="width:36px; height:36px; border-radius:11px; background:#f3e8ff;
                            color:#7c3aed; display:flex; align-items:center; justify-content:center;">
                    <x-heroicon-o-arrows-right-left style="width:15px; height:15px;"/>
                </div>
                <span style="font-size:11px; font-weight:700; padding:3px 9px; border-radius:99px;
                             background:#f3e8ff; color:#7c3aed;">Historique</span>
            </div>
            <div style="font-size:36px; font-weight:800; color:#1e1b4b; line-height:1;
                        margin-bottom:4px; letter-spacing:-1px;"
                 class="spx-count" data-target="{{ $movements->total() }}">0</div>
            <div style="font-size:11px; color:#9ca3af; text-transform:uppercase;
                        letter-spacing:.6px; font-weight:500; margin-bottom:.85rem;">
                Total mouvements
            </div>
            <div style="height:4px; background:#f3f4f6; border-radius:99px; overflow:hidden;">
                <div style="height:100%; width:100%; border-radius:99px;
                            background:linear-gradient(90deg,#c4b5fd,#7c3aed);">
                </div>
            </div>
        </div>

    </div>


    {{-- ================= TABLE ================= --}}
    <div style="background:#fff; border-radius:18px; border:1px solid #ede9fe;
                box-shadow:0 1px 12px rgba(79,70,229,.06); overflow:hidden;">

        <div style="display:flex; align-items:center; justify-content:space-between;
                    padding:.9rem 1.4rem; border-bottom:1px solid #f5f3ff; background:#fafafe;">
            <div style="display:flex; align-items:center; gap:.5rem;">
                <x-heroicon-o-clock style="width:15px; height:15px; color:#9ca3af;"/>
                <span style="font-size:13px; font-weight:700; color:#1e1b4b;">Historique complet</span>
            </div>
            <span style="font-size:12px; font-weight:700; padding:3px 11px; border-radius:99px;
                         background:linear-gradient(135deg,#ede9fe,#ddd6fe); color:#4f46e5;">
                {{ $movements->total() }} mouvement{{ $movements->total() > 1 ? 's' : '' }}
            </span>
        </div>

        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:13.5px;">

                <thead>
                <tr>
                    <th style="padding:.75rem 1rem .75rem 1.4rem; background:#fafafe; font-size:11px;
                               font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.6px;
                               border-bottom:1px solid #f3f0ff; text-align:left; white-space:nowrap;">
                        Type
                    </th>
                    <th style="padding:.75rem 1rem; background:#fafafe; font-size:11px; font-weight:700;
                               color:#9ca3af; text-transform:uppercase; letter-spacing:.6px;
                               border-bottom:1px solid #f3f0ff; text-align:center;">
                        Quantité
                    </th>
                    <th style="padding:.75rem 1rem; background:#fafafe; font-size:11px; font-weight:700;
                               color:#9ca3af; text-transform:uppercase; letter-spacing:.6px;
                               border-bottom:1px solid #f3f0ff; text-align:center;">
                        Avant
                    </th>
                    <th style="padding:.75rem 1rem; background:#fafafe; font-size:11px; font-weight:700;
                               color:#9ca3af; text-transform:uppercase; letter-spacing:.6px;
                               border-bottom:1px solid #f3f0ff; text-align:center;">
                        Après
                    </th>
                    <th style="padding:.75rem 1rem; background:#fafafe; font-size:11px; font-weight:700;
                               color:#9ca3af; text-transform:uppercase; letter-spacing:.6px;
                               border-bottom:1px solid #f3f0ff; text-align:center;">
                        Référence
                    </th>
                    <th style="padding:.75rem 1.4rem .75rem 1rem; background:#fafafe; font-size:11px;
                               font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.6px;
                               border-bottom:1px solid #f3f0ff; text-align:right;">
                        Date
                    </th>
                </tr>
                </thead>

                <tbody>
                @forelse($movements as $m)
                @php
                    $mType = $m->type ?? '';
                    $badgeStyle = match($mType) {
                        'purchase' => 'background:#d1fae5; color:#059669;',
                        'sale'     => 'background:#ffe4e6; color:#e11d48;',
                        'return'   => 'background:#ede9fe; color:#4f46e5;',
                        default    => 'background:#fef3c7; color:#d97706;',
                    };
                    $typeLabel = match($mType) {
                        'purchase'   => 'Entrée',
                        'sale'       => 'Sortie',
                        'return'     => 'Retour',
                        'adjustment' => 'Ajustement',
                        default      => e($mType),
                    };
                    $stockAfter  = (float) ($m->stock_after  ?? 0);
                    $stockBefore = (float) ($m->stock_before ?? 0);
                    $afterStyle  = $stockAfter > $stockBefore
                        ? 'color:#059669; background:#d1fae5;'
                        : ($stockAfter < $stockBefore
                            ? 'color:#e11d48; background:#ffe4e6;'
                            : 'color:#6b7280; background:#f3f4f6;');
                @endphp
                <tr style="border-bottom:1px solid #faf9ff; transition:.18s;"
                    onmouseover="this.style.background='#faf7ff'"
                    onmouseout="this.style.background=''">

                    <td style="padding:.85rem 1rem .85rem 1.4rem;">
                        <span style="display:inline-flex; align-items:center; gap:5px;
                                     padding:4px 11px; border-radius:99px;
                                     font-size:12px; font-weight:600; {{ $badgeStyle }}">
                            {{ $typeLabel }}
                        </span>
                    </td>

                    <td style="padding:.85rem 1rem; text-align:center; font-weight:700; color:#1e1b4b;">
                        {{ number_format($m->quantity ?? 0, 2) }}
                    </td>

                    <td style="padding:.85rem 1rem; text-align:center; color:#9ca3af;">
                        {{ number_format($stockBefore, 2) }}
                    </td>

                    <td style="padding:.85rem 1rem; text-align:center;">
                        <span style="display:inline-block; font-weight:700; padding:2px 8px;
                                     border-radius:8px; font-size:13px; {{ $afterStyle }}">
                            {{ number_format($stockAfter, 2) }}
                            {{ $stockAfter > $stockBefore ? '↑' : ($stockAfter < $stockBefore ? '↓' : '') }}
                        </span>
                    </td>

                    <td style="padding:.85rem 1rem; text-align:center; color:#9ca3af; font-size:12.5px;">
                        {{ e($m->reference ?? '—') }}
                    </td>

                    <td style="padding:.85rem 1.4rem .85rem 1rem; text-align:right;">
                        <div style="font-weight:500; color:#374151; font-size:13px;">
                            {{ $m->created_at?->format('d/m/Y') ?? '-' }}
                        </div>
                        <div style="font-size:11px; color:#9ca3af; margin-top:1px;">
                            {{ $m->created_at?->format('H:i') ?? '' }}
                        </div>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding:4rem 1rem; text-align:center;">
                        <div style="display:flex; flex-direction:column; align-items:center; gap:.75rem;">
                            <div style="width:52px; height:52px; border-radius:16px; background:#f5f3ff;
                                        color:#c4b5fd; display:flex; align-items:center; justify-content:center;">
                                <x-heroicon-o-inbox style="width:22px; height:22px;"/>
                            </div>
                            <p style="font-weight:600; color:#6b7280; font-size:14px; margin:0;">
                                Aucun mouvement trouvé
                            </p>
                            <p style="font-size:12px; color:#9ca3af; margin:0;">
                                Ce produit n'a encore aucun mouvement enregistré
                            </p>
                        </div>
                    </td>
                </tr>
                @endforelse
                </tbody>

            </table>
        </div>

        @if($movements->hasPages())
        <div style="padding:.9rem 1.4rem; border-top:1px solid #f5f3ff; background:#fafafe;">
            {{ $movements->withQueryString()->links() }}
        </div>
        @endif

    </div>

</div>

@else
<div class="flex flex-col items-center justify-center py-24 text-gray-400">
    <x-heroicon-o-lock-closed class="w-12 h-12 mb-4 text-gray-300"/>
    <p class="text-lg font-medium">Accès non autorisé</p>
</div>
@endcan


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.spx-count').forEach(el => {
        // ✅ parseInt avec radix 10
        const target = parseInt(el.dataset.target, 10) || 0;
        if (!target) { el.textContent = '0'; return; }
        let t0  = null;
        const dur  = 1300;
        const ease = p => 1 - Math.pow(1 - p, 4);
        const run  = ts => {
            if (!t0) t0 = ts;
            const p = Math.min((ts - t0) / dur, 1);
            el.textContent = Math.floor(ease(p) * target).toLocaleString('fr-FR');
            if (p < 1) requestAnimationFrame(run);
            else el.textContent = target.toLocaleString('fr-FR');
        };
        setTimeout(() => requestAnimationFrame(run), 250);
    });
});
</script>
@endpush

</x-app-layout>