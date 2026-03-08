@props([
    'title' => '',
    'value' => 0,
    'color' => 'gray'
])

@php
$colors = [
    'gray' => 'bg-white text-gray-900',
    'green' => 'bg-green-50 text-green-700',
    'blue' => 'bg-blue-50 text-blue-700',
];
@endphp

<div {{ $attributes->merge([
    'class' => ($colors[$color] ?? $colors['gray']) . ' p-6 rounded-2xl shadow'
]) }}>

    <p class="text-xs uppercase opacity-70">
        {{ $title }}
    </p>

    <p class="text-2xl font-bold mt-2">
        {{ $value }}
    </p>

</div>