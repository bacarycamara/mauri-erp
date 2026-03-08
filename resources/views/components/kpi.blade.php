@php
$colors = [
    'indigo' => 'bg-indigo-100 text-indigo-600',
    'blue' => 'bg-blue-100 text-blue-600',
    'green' => 'bg-green-100 text-green-600',
    'yellow' => 'bg-yellow-100 text-yellow-600',
];

$colorClass = $colors[$color] ?? $colors['indigo'];
@endphp

<div class="bg-white border rounded-3xl p-6 shadow-sm
            transition hover:shadow-xl hover:-translate-y-1">

    <div class="flex justify-between items-center">

        <div>
            <p class="text-sm text-gray-500">{{ $label }}</p>
            <p class="text-2xl font-bold mt-1">{{ $value }}</p>
        </div>

        <div class="p-3 rounded-xl {{ $colorClass }}">
            <x-dynamic-component
                :component="'heroicon-o-'.$icon"
                class="w-6 h-6"/>
        </div>

    </div>

</div>