@props([
    'title' => '',
    'value' => 0,
    'color' => 'yellow',
    'icon' => 'exclamation-circle'
])

@php
$colors = [
    'yellow' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
    'red' => 'bg-red-50 border-red-200 text-red-800',
    'green' => 'bg-green-50 border-green-200 text-green-800',
];
@endphp

<div class="p-6 rounded-2xl border shadow-sm flex justify-between items-center
            {{ $colors[$color] ?? $colors['yellow'] }}">

    <div>
        <p class="text-sm">{{ $title }}</p>
        <p class="text-2xl font-bold">{{ $value }}</p>
    </div>

    <x-dynamic-component :component="'heroicon-o-'.$icon"
        class="w-8 h-8 opacity-70"/>

</div>