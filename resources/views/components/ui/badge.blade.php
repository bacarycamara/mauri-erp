@props([
'variant' => 'gray'
])

@php
$variants = [
'gray' => 'bg-gray-100 text-gray-700',
'success' => 'bg-green-100 text-green-700',
'danger' => 'bg-red-100 text-red-700',
'warning' => 'bg-yellow-100 text-yellow-700',
'info' => 'bg-blue-100 text-blue-700',
];
@endphp

<span {{ $attributes->merge([
'class' => 'ui-badge '.$variants[$variant]
]) }}>
{{ $slot }}
</span>