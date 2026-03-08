<a href="{{ route($item['route']) }}"
   class="sidebar-link {{ $item['active'] }}"
>
    <span class="text-lg">{{ $item['icon'] }}</span>

    <span x-show="open" x-transition.opacity.duration.200ms>
        {{ $item['label'] }}
    </span>
</a>