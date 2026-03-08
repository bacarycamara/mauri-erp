@props(['title','icon','routes','links'])

@php
    $isActive = false;

    foreach($routes as $r){
        if(request()->routeIs($r)) $isActive = true;
    }

    $heroIcon = 'heroicon-o-' . $icon;
@endphp

<div x-data="{ sub: {{ $isActive ? 'true' : 'false' }} }">

    {{-- MAIN BUTTON --}}
    <button @click="sub = !sub"
        class="sidebar-link w-full flex justify-between items-center">

        <div class="flex items-center gap-3">
            <x-dynamic-component :component="$heroIcon" class="w-5 h-5 shrink-0"/>
            {{-- Label visible si sidebar ouverte OU si mobile --}}
            <span x-show="open || isMobile" x-transition.opacity class="truncate">{{ $title }}</span>
        </div>

        {{-- Arrow uniquement si label visible --}}
        <x-heroicon-o-chevron-down
            x-show="open || isMobile"
            :class="sub ? 'rotate-180' : ''"
            class="h-4 w-4 transition-transform duration-300 shrink-0"/>
    </button>

    {{-- SUB MENU --}}
    <div x-show="sub && (open || isMobile)"
         x-transition
         class="pl-10 space-y-1 mt-1">

        @foreach($links as $link)
            @can($link['permission'])
            <a href="{{ route($link['route']) }}"
               onclick="if(window.innerWidth < 1024) closeSidebar()"
               class="sidebar-sub {{ request()->routeIs(str_replace('.index', '.*', $link['route'])) ? 'sidebar-sub-active' : '' }}">
                {{ $link['label'] }}
            </a>
            @endcan
        @endforeach

    </div>

</div>