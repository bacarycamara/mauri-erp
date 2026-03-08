@props([
    'title' => '',
    'value' => 0,
    'icon' => 'chart-bar'
])

<div class="bg-white p-5 rounded-2xl shadow flex justify-between items-center">

    <div>
        <p class="text-sm text-gray-500">{{ $title }}</p>
        <p class="text-xl font-bold text-gray-800">
            {{ number_format($value ?? 0,2) }}
            {{ company()?->currency }}
        </p>
    </div>

    <div class="p-3 bg-indigo-100 rounded-xl">
        <x-dynamic-component :component="'heroicon-o-'.$icon"
            class="w-6 h-6 text-indigo-600"/>
    </div>

</div>