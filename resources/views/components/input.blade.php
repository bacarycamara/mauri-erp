@props([
    'label',
    'name',
    'type' => 'text',
    'value' => null,
    'required' => false,
    'step' => null
])

<div>
    <label class="block text-sm font-medium mb-1 text-gray-700">
        {{ $label }}
    </label>

    <input 
        type="{{ $type }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        @if($required) required @endif
        @if($step) step="{{ $step }}" @endif
        {{ $attributes->merge([
            'class' => 'w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none'
        ]) }}
    >

    @error($name)
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>