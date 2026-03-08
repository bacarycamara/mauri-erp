@props([
    'variant' => 'primary',
    'type' => 'button'
])

@php
$variants = [
'primary' => 'bg-indigo-600 text-white hover:bg-indigo-700',
'secondary' => 'border text-gray-700 hover:bg-gray-100',
'danger' => 'bg-red-600 text-white hover:bg-red-700',
'success' => 'bg-green-600 text-white hover:bg-green-700',
];
@endphp

<button type="{{ $type }}"
{{ $attributes->merge([
'class' =>
'ui-btn '.$variants[$variant]
]) }}>
    {{ $slot }}
</button>