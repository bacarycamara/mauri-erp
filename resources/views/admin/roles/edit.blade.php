<x-app-layout>

@section('title','Modifier rôle')

<div class="max-w-7xl mx-auto space-y-8"
     x-data="roleEditor()"
     x-cloak>

{{-- ================= HEADER ================= --}}
<div class="flex items-center gap-4">
    <div class="p-3 bg-indigo-100 rounded-xl">
        <x-heroicon-o-lock-closed class="w-7 h-7 text-indigo-600"/>
    </div>

    <div>
        <h1 class="text-3xl font-bold text-gray-800">
            Modifier le rôle
        </h1>
        <p class="text-sm text-gray-500">
            {{ $role->name }}
        </p>
    </div>
</div>


{{-- ================= FORM UPDATE ================= --}}
<form action="{{ route('admin.roles.update',$role->id) }}"
      method="POST"
      class="space-y-8">
@csrf
@method('PUT')


{{-- ROLE NAME --}}
<div class="bg-white p-6 rounded-3xl shadow border border-gray-100">

    <label class="block text-sm font-semibold text-gray-700 mb-3">
        Nom du rôle
    </label>

    <input type="text"
           name="name"
           value="{{ old('name',$role->name) }}"
           class="w-full border rounded-xl px-4 py-3
                  focus:ring-2 focus:ring-indigo-500 transition"
           required>

    @error('name')
        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
    @enderror

</div>


{{-- ================= PERMISSIONS ================= --}}
<div class="bg-white p-6 rounded-3xl shadow border border-gray-100 space-y-6">

<div class="flex items-center gap-2">
    <x-heroicon-o-cog-6-tooth class="w-5 h-5 text-indigo-600"/>
    <h2 class="text-lg font-semibold text-gray-800">
        Permissions
    </h2>
</div>

@foreach($permissions as $module => $modulePermissions)

<div class="border rounded-2xl overflow-hidden">

{{-- MODULE HEADER --}}
<div class="flex justify-between items-center p-4
            bg-gray-50 hover:bg-indigo-50 cursor-pointer transition"
     @click="toggle('{{ $module }}')">

    <div class="flex items-center gap-3">
        <x-heroicon-o-cube class="w-5 h-5 text-indigo-500"/>

        <span class="font-semibold text-gray-700 capitalize">
            {{ str_replace('_',' ', $module) }}
        </span>
    </div>

    <div class="flex items-center gap-4">

        {{-- SELECT ALL --}}
        <button type="button"
            class="text-xs text-indigo-600 font-semibold"
            @click.stop="toggleAll('{{ $module }}')">
            Tout sélectionner
        </button>

        <x-heroicon-o-chevron-down
            class="w-5 h-5 text-gray-400 transition"
            x-bind:class="open==='{{ $module }}' ? 'rotate-180' : ''"/>
    </div>

</div>


{{-- MODULE BODY --}}
<div x-show="open==='{{ $module }}'"
     x-transition
     class="p-4 grid md:grid-cols-4 gap-4">

@foreach($modulePermissions as $permission)

<label class="flex items-center gap-3
               bg-gray-50 px-3 py-2 rounded-xl
               hover:bg-indigo-50 transition cursor-pointer">

<input type="checkbox"
       name="permissions[]"
       value="{{ $permission->name }}"
       class="perm-{{ $module }}
              rounded border-gray-300
              text-indigo-600 focus:ring-indigo-500"
       {{ in_array($permission->name,$rolePermissions) ? 'checked' : '' }}>

<span class="text-sm text-gray-700 capitalize">
    {{ explode(' ', $permission->name)[0] }}
</span>

</label>

@endforeach

</div>

</div>

@endforeach

</div>


{{-- ================= ACTIONS ================= --}}
<div class="flex justify-between items-center">

{{-- DELETE (extérieur au form update) --}}
@can('delete roles')
@if($role->name !== 'Super Admin')

<button type="button"
        @click="confirmDelete=true"
        class="bg-red-600 text-white px-6 py-3 rounded-xl
               hover:bg-red-700 transition shadow flex items-center gap-2">

    <x-heroicon-o-trash class="w-5 h-5"/>
    Supprimer
</button>

@endif
@endcan


<button
    class="bg-indigo-600 text-white px-6 py-3 rounded-xl
           hover:bg-indigo-700 transition shadow-lg
           hover:shadow-xl flex items-center gap-2">

    <x-heroicon-o-pencil-square class="w-5 h-5"/>
    Mettre à jour
</button>

</div>

</form>


{{-- ================= DELETE FORM (SEPARÉ) ================= --}}
@if($role->name !== 'Super Admin')
<form x-show="confirmDelete"
      x-transition.opacity
      method="POST"
      action="{{ route('admin.roles.destroy',$role->id) }}"
      class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">

@csrf
@method('DELETE')

<div @click.away="confirmDelete=false"
     class="bg-white p-6 rounded-2xl shadow-xl w-96 space-y-4">

<h3 class="text-lg font-semibold text-gray-800">
Supprimer ce rôle ?
</h3>

<p class="text-sm text-gray-500">
Cette action est irréversible.
</p>

<div class="flex justify-end gap-3">

<button type="button"
        @click="confirmDelete=false"
        class="px-4 py-2 rounded-lg bg-gray-100">
Annuler
</button>

<button class="px-4 py-2 rounded-lg bg-red-600 text-white">
Supprimer
</button>

</div>

</div>

</form>
@endif

</div>


{{-- ================= ALPINE ================= --}}
<script>
function roleEditor(){
    return{
        open:null,
        confirmDelete:false,

        toggle(module){
            this.open=this.open===module?null:module;
        },

        toggleAll(module){
            let boxes=document.querySelectorAll('.perm-'+module);
            let allChecked=[...boxes].every(cb=>cb.checked);
            boxes.forEach(cb=>cb.checked=!allChecked);
        }
    }
}
</script>

</x-app-layout>