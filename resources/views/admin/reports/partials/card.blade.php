<a href="{{ $route }}"
   class="group bg-white p-6 rounded-2xl shadow-md hover:shadow-2xl
          transition-all duration-300 hover:-translate-y-2 border border-gray-100">

    <div class="icon-wrapper bg-{{ $color }}-100 text-{{ $color }}-600">
        <x-dynamic-component :component="'heroicon-o-'.$icon" class="w-7 h-7"/>
    </div>

    <h2 class="font-semibold text-lg text-gray-800 mb-2">
        {{ $title }}
    </h2>

    <p class="text-sm text-gray-500 leading-relaxed">
        {{ $desc }}
    </p>

</a>

<style>
.icon-wrapper{
padding:16px;
border-radius:14px;
margin-bottom:18px;
display:flex;
align-items:center;
justify-content:center;
transition:.3s;
}
.group:hover .icon-wrapper{
transform:scale(1.12) rotate(3deg);
}
</style>