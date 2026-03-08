@props([
    'title' => '',
    'value' => 0,
    'color' => 'blue',
    'icon' => 'chart-bar'
])

@php
$colors = [
    'blue' => 'from-blue-500 to-blue-600',
    'indigo' => 'from-indigo-500 to-indigo-600',
    'red' => 'from-red-500 to-red-600',
    'green' => 'from-green-500 to-green-600',
];
@endphp

<div class="bg-gradient-to-br {{ $colors[$color] ?? $colors['blue'] }}
            text-white p-6 rounded-3xl shadow-lg
            hover:scale-105 transition">

    <div class="flex justify-between items-center opacity-90">
        <span class="text-sm uppercase">{{ $title }}</span>

        <x-dynamic-component :component="'heroicon-o-'.$icon"
            class="w-5 h-5"/>
    </div>

    <p class="text-3xl font-bold mt-4">
        {{ $value }} {{ company()?->currency }}
    </p>

</div>