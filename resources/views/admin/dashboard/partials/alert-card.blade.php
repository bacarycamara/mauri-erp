<div class="bg-{{ $color }}-50 p-4 rounded-xl border border-{{ $color }}-200
            shadow-sm flex justify-between items-center
            hover:shadow-md transition">

    <div>
        <p class="text-xs text-{{ $color }}-700">
            {{ $title }}
        </p>

        <p class="text-xl font-bold text-{{ $color }}-800">
            {{ $value ?? 0 }}
        </p>
    </div>

    <x-dynamic-component
        :component="'heroicon-o-'.$icon"
        class="w-6 h-6 text-{{ $color }}-500"
    />

</div>