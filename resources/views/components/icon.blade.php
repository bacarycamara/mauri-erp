<div>
    <!-- It is not the man who has too little, but the man who craves more, that is poor. - Seneca -->
</div><x-dynamic-component
    :component="'heroicon-'.$style.'-'.$name"
    {{ $attributes->merge([
        'class' => 'icon-ui'
    ]) }}
/>