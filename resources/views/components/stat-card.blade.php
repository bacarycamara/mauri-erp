@props([
    'title',
    'value' => 0,
    'icon' => 'chart-bar',
    'color' => 'white'
])

@php
$bg = match($color) {
    'green' => 'bg-green-50',
    'gray'  => 'bg-gray-100',
    default => 'bg-white'
};

$text = match($color) {
    'green' => 'text-green-700',
    'gray'  => 'text-gray-700',
    default => 'text-gray-900'
};
@endphp

<div class="{{ $bg }} p-6 rounded-2xl shadow hover:shadow-xl transition">

    <div class="flex items-center justify-between">
        <p class="text-xs uppercase text-gray-500">
            {{ $title }}
        </p>

        <x-dynamic-component
            :component="'heroicon-o-'.$icon"
            class="w-5 h-5 text-gray-400"/>
    </div>

    <p class="text-2xl font-bold mt-2 {{ $text }}">
        {{ $value }}
    </p>

</div>