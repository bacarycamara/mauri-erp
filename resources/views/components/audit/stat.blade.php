@props([
    'title' => '',
    'value' => 0,
    'color' => 'gray',
    'icon'  => 'information-circle'
])

<div class="bg-{{ $color }}-50 p-6 rounded-2xl shadow hover:shadow-md transition">

    <div class="flex justify-between items-center">
        <span class="text-sm font-semibold text-{{ $color }}-700">
            {{ $title }}
        </span>

        <x-dynamic-component
            :component="'heroicon-o-'.$icon"
            class="w-6 h-6 text-{{ $color }}-600"/>
    </div>

    <p class="text-2xl font-bold text-{{ $color }}-700 mt-3">
        {{ $value }}
    </p>

</div>