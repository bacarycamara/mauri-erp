@props([
'label' => null,
'type' => 'text',
'name'
])

<div class="ui-field">

@if($label)
<label class="ui-label">{{ $label }}</label>
@endif

<input
type="{{ $type }}"
name="{{ $name }}"
{{ $attributes->merge([
'class' => 'ui-input'
]) }}
>

@error($name)
<p class="ui-error">{{ $message }}</p>
@enderror

</div>