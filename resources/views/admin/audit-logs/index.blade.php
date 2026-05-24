<x-app-layout>

@can('view audit_logs')

<div class="space-y-10" x-data="{ openLog: null }">

    {{-- ================= HEADER ================= --}}
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-6">
        <div class="flex items-center gap-4">
            <div class="p-4 bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-2xl shadow-lg">
                <x-heroicon-o-clipboard-document-list class="w-8 h-8 text-white"/>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-800 tracking-tight">Journal d'audit</h1>
                <p class="text-gray-500 text-sm">
                    Surveillance complète des actions ERP
                    @if(isset($total))
                        &middot;
                        <span class="font-semibold text-indigo-600">
                            {{ number_format($total) }}
                        </span> entrées
                    @endif
                </p>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            @can('export audit_logs')
            <a href="{{ route('admin.audit-logs.export', request()->query()) }}"
               class="btn-success">
                <x-heroicon-o-arrow-down-tray class="w-5 h-5"/>
                Export CSV
            </a>
            @endcan

            @can('delete audit_logs')
            <form method="POST"
                  action="{{ route('admin.audit-logs.clear') }}"
                  onsubmit="return confirm('Supprimer TOUS les logs ? Action irréversible.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger">
                    <x-heroicon-o-trash class="w-5 h-5"/>
                    Clear
                </button>
            </form>
            @endcan
        </div>
    </div>


    {{-- ================= STATS ================= --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <x-audit.stat color="green"  title="Créations"     :value="$stats['created'] ?? 0" icon="plus-circle"/>
        <x-audit.stat color="blue"   title="Modifications" :value="$stats['updated'] ?? 0" icon="pencil-square"/>
        <x-audit.stat color="red"    title="Suppressions"  :value="$stats['deleted'] ?? 0" icon="trash"/>
        <x-audit.stat color="indigo" title="Connexions"    :value="$stats['login']   ?? 0" icon="arrow-right-on-rectangle"/>
    </div>


    {{-- ================= FILTRES ================= --}}
    <div class="card">
        <form method="GET" class="grid md:grid-cols-6 gap-4">

            <input type="text"
                   name="search"
                   value="{{ e(request('search')) }}"
                   placeholder="Recherche..."
                   maxlength="100"
                   class="input col-span-2">

            <select name="action" class="input">
                <option value="">Toutes les actions</option>
                @foreach($actions as $action)
                <option value="{{ e($action) }}"
                        @selected(request('action') === $action)>
                    {{ ucfirst($action) }}
                </option>
                @endforeach
            </select>

            <select name="user_id" class="input">
                <option value="">Tous les utilisateurs</option>
                @foreach($users as $user)
                <option value="{{ $user->id }}"
                        @selected(request('user_id') == $user->id)>
                    {{ e($user->name) }}
                </option>
                @endforeach
            </select>

            <input type="date"
                   name="from"
                   value="{{ request('from') }}"
                   class="input">

            <input type="date"
                   name="to"
                   value="{{ request('to') }}"
                   class="input">

            <div class="flex gap-2 col-span-2 md:col-span-6">
                <button type="submit" class="btn-primary">Filtrer</button>
                @if(request()->hasAny(['search','action','user_id','from','to']))
                <a href="{{ route('admin.audit-logs.index') }}" class="btn-secondary">
                    Reset
                </a>
                @endif
            </div>

        </form>
    </div>


    {{-- ================= TIMELINE ================= --}}
    <div class="card">
        <div class="relative border-l-2 border-gray-200 pl-8 space-y-10">

        @forelse($logs as $log)
        @php
            $logId    = (int) $log->id; // cast int — sécurité Alpine
            $dotColor = match($log->action) {
                'created' => 'bg-green-500',
                'updated' => 'bg-blue-500',
                'deleted' => 'bg-red-500',
                'login'   => 'bg-indigo-500',
                default   => 'bg-gray-400',
            };
        @endphp

        <div class="relative group">

            <div class="timeline-dot {{ $dotColor }}"></div>

            <div class="timeline-card">

                <div class="flex justify-between items-start gap-4">

                    <div class="flex items-center gap-3 flex-wrap">

                        @switch($log->action)
                            @case('created') <x-heroicon-o-plus-circle              class="icon-green"/>  @break
                            @case('updated') <x-heroicon-o-pencil-square            class="icon-blue"/>   @break
                            @case('deleted') <x-heroicon-o-trash                    class="icon-red"/>    @break
                            @case('login')   <x-heroicon-o-arrow-right-on-rectangle class="icon-indigo"/> @break
                            @default         <x-heroicon-o-information-circle        class="icon-gray"/>
                        @endswitch

                        {{-- ✅ statusBadge généré côté model — pas de XSS ici --}}
                        {!! $log->action_badge !!}

                        <span class="text-sm text-gray-600 font-medium">
                            {{ e($log->user?->name ?? 'Système') }}
                        </span>

                        @if($log->ip_address)
                        <span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full font-mono">
                            {{-- ✅ IP affichée en lecture seule, jamais cliquable --}}
                            {{ $log->ip_address }}
                        </span>
                        @endif

                    </div>

                    <span class="text-xs text-gray-400 whitespace-nowrap shrink-0">
                        {{ $log->formatted_date }}
                    </span>

                </div>

                <div class="mt-3 text-sm text-gray-500">
                    {{ e($log->model_name ?? '-') }}
                    @if($log->model_id)
                    <span class="text-gray-400">#{{ (int) $log->model_id }}</span>
                    @endif
                </div>

                @if($log->has_changes)
                <div class="mt-4">

                    <button type="button"
                            x-on:click="openLog === {{ $logId }} ? openLog = null : openLog = {{ $logId }}"
                            class="text-indigo-600 text-sm font-medium hover:underline flex items-center gap-1">
                        <span x-text="openLog === {{ $logId }} ? 'Masquer les détails' : 'Voir les détails'"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 transition-transform duration-200" :class="openLog === {{ $logId }} ? 'rotate-180' : 'rotate-0'"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>

                    </button>

                    <div x-show="openLog === {{ $logId }}"
                         x-collapse
                         x-cloak
                         class="mt-4">
                        <div class="grid md:grid-cols-2 gap-4 text-xs">
                            <div>
                                <p class="text-gray-400 font-semibold mb-2 uppercase tracking-wide">Avant</p>
                                {{-- ✅ json_encode encodé puis e() pour éviter XSS dans le pre --}}
                                <pre class="json-box">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                            <div>
                                <p class="text-gray-400 font-semibold mb-2 uppercase tracking-wide">Après</p>
                                <pre class="json-box">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                    </div>

                </div>
                @endif

            </div>
        </div>

        @empty
        <div class="text-center py-16">
            <x-heroicon-o-clipboard-document-list class="w-12 h-12 text-gray-300 mx-auto mb-3"/>
            <p class="text-gray-400 font-medium">Aucun log disponible</p>
            @if(request()->hasAny(['search','action','user_id','from','to']))
            <a href="{{ route('admin.audit-logs.index') }}"
               class="text-indigo-500 text-sm mt-2 inline-block hover:underline">
                Effacer les filtres
            </a>
            @endif
        </div>
        @endforelse

        </div>
    </div>


    {{-- ================= PAGINATION ================= --}}
    <div class="pt-2">
        {{ $logs->withQueryString()->links() }}
    </div>

</div>

@else
<div class="flex flex-col items-center justify-center py-24 text-gray-400">
    <x-heroicon-o-lock-closed class="w-12 h-12 mb-4 text-gray-300"/>
    <p class="text-lg font-medium">Accès non autorisé</p>
</div>
@endcan


<style>
.card              { background:#fff; padding:1.5rem; border-radius:1rem; border:1px solid #eee; box-shadow:0 1px 3px rgba(0,0,0,.05); }
.input             { border:1px solid #ddd; border-radius:.75rem; padding:.5rem .75rem; width:100%; }
.btn-primary       { background:#4f46e5; color:#fff; padding:.5rem 1.2rem; border-radius:.75rem; transition:.2s; }
.btn-primary:hover { background:#4338ca; }
.btn-secondary     { background:#f3f4f6; color:#374151; padding:.5rem 1.2rem; border-radius:.75rem; transition:.2s; }
.btn-secondary:hover { background:#e5e7eb; }
.btn-success       { background:#16a34a; color:#fff; padding:.6rem 1.2rem; border-radius:.75rem; display:flex; align-items:center; gap:.5rem; transition:.2s; }
.btn-success:hover { background:#15803d; }
.btn-danger        { background:#dc2626; color:#fff; padding:.6rem 1.2rem; border-radius:.75rem; display:flex; align-items:center; gap:.5rem; transition:.2s; }
.btn-danger:hover  { background:#b91c1c; }
.timeline-dot      { position:absolute; left:-14px; top:12px; width:24px; height:24px; border-radius:9999px; display:flex; align-items:center; justify-content:center; }
.timeline-card     { background:#f9fafb; padding:1.5rem; border-radius:.75rem; transition:.25s; }
.timeline-card:hover { box-shadow:0 8px 20px rgba(0,0,0,.06); }
.json-box          { background:#fff; border:1px solid #eee; padding:1rem; border-radius:.75rem; overflow:auto; max-height:300px; white-space:pre-wrap; word-break:break-all; }
.icon-green        { width:24px; height:24px; color:#16a34a; }
.icon-blue         { width:24px; height:24px; color:#2563eb; }
.icon-red          { width:24px; height:24px; color:#dc2626; }
.icon-indigo       { width:24px; height:24px; color:#4f46e5; }
.icon-gray         { width:24px; height:24px; color:#6b7280; }
</style>

</x-app-layout>