<x-app-layout>

@section('title','Créer un rôle')

<div class="max-w-7xl mx-auto space-y-8"
     x-data="roleBuilder()"
     x-init="init()"
     x-cloak>

    {{-- ================= HEADER ================= --}}
    <div class="flex items-center gap-4 opacity-0 translate-y-3"
         x-init="$nextTick(()=> $el.classList.remove('opacity-0','translate-y-3'))"
         class="transition duration-500">

        <div class="p-3 bg-indigo-100 rounded-xl">
            <x-heroicon-o-lock-closed class="w-7 h-7 text-indigo-600"/>
        </div>

        <div>
            <h1 class="text-3xl font-bold text-gray-800">
                Nouveau Rôle
            </h1>
            <p class="text-sm text-gray-500">
                Définir les permissions d'accès au système ERP
            </p>
        </div>
    </div>


    {{-- ================= FORM ================= --}}
    <form action="{{ route('admin.roles.store') }}"
          method="POST"
          class="space-y-8">
        @csrf


        {{-- ROLE NAME --}}
        <div class="bg-white p-6 rounded-3xl shadow-lg border border-gray-100">

            <label class="block text-sm font-semibold text-gray-700 mb-3">
                Nom du rôle
            </label>

            <input type="text"
                   name="name"
                   value="{{ old('name') }}"
                   class="w-full border rounded-xl px-4 py-3
                          focus:ring-2 focus:ring-indigo-500 transition"
                   placeholder="Ex: Responsable Commercial"
                   required>

            @error('name')
                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
            @enderror

        </div>


        {{-- ================= PERMISSIONS ================= --}}
        <div class="bg-white p-6 rounded-3xl shadow-lg border border-gray-100 space-y-6">

            {{-- HEADER --}}
            <div class="flex justify-between items-center">

                <div class="flex items-center gap-2">
                    <x-heroicon-o-cog-6-tooth class="w-5 h-5 text-indigo-600"/>
                    <h2 class="text-lg font-semibold text-gray-800">
                        Permissions
                    </h2>
                </div>

                <button type="button"
                        @click="toggleAll()"
                        class="text-xs font-semibold text-indigo-600 hover:underline">
                    Tout sélectionner
                </button>
            </div>


            {{-- MODULES --}}
            @foreach($permissions as $module => $modulePermissions)

            <div class="border rounded-2xl overflow-hidden">

                {{-- MODULE HEADER --}}
                <div class="flex justify-between items-center p-4
                            bg-gray-50 hover:bg-indigo-50 cursor-pointer transition"
                     @click="toggleModule('{{ $module }}')">

                    <div class="flex items-center gap-3">

                        <div class="p-2 bg-indigo-100 rounded-lg">
                            <x-heroicon-o-cube class="w-4 h-4 text-indigo-600"/>
                        </div>

                        <span class="font-semibold text-gray-700 capitalize">
                            {{ str_replace('_',' ', $module) }}
                        </span>

                        {{-- COUNT --}}
                        <span class="text-xs bg-indigo-100 text-indigo-700
                                     px-2 py-1 rounded-full"
                              x-text="countModule('{{ $module }}')">
                        </span>
                    </div>

                    {{--  CORRECTION ICI --}}
                    <x-heroicon-o-chevron-down
                        class="w-5 h-5 text-gray-400 transition"
                        x-bind:class="openModule === '{{ $module }}' ? 'rotate-180' : ''"/>
                </div>


                {{-- MODULE BODY --}}
                <div x-show="openModule === '{{ $module }}'"
                     x-transition
                     class="p-4 grid md:grid-cols-4 gap-3">

                    @foreach($modulePermissions as $permission)

                    <label class="group flex items-center gap-3
                                   bg-gray-50 hover:bg-indigo-50
                                   px-3 py-2 rounded-xl cursor-pointer transition">

                        <input type="checkbox"
                               name="permissions[]"
                               value="{{ $permission->name }}"
                               class="perm perm-{{ $module }}
                                      rounded border-gray-300
                                      text-indigo-600 focus:ring-indigo-500"
                               @change="updateCounts()">

                        <span class="text-sm text-gray-700 capitalize
                                     group-hover:text-indigo-700 transition">
                            {{ explode(' ', $permission->name)[0] }}
                        </span>

                    </label>

                    @endforeach

                </div>
            </div>

            @endforeach

        </div>


        {{-- SUBMIT --}}
        <div class="flex justify-end">

            <button
                class="bg-gradient-to-r from-indigo-600 to-indigo-700
                       text-white px-8 py-3 rounded-xl
                       shadow-lg hover:shadow-xl
                       hover:scale-105 active:scale-95
                       transition flex items-center gap-2">

                <x-heroicon-o-check class="w-5 h-5"/>
                Créer le rôle
            </button>

        </div>

    </form>

</div>


{{-- ================= ALPINE ================= --}}
<script>
function roleBuilder(){
    return {

        openModule:null,

        init(){},

        toggleModule(module){
            this.openModule =
                this.openModule === module ? null : module;
        },

        toggleAll(){
            const boxes=document.querySelectorAll('.perm');
            const allChecked=[...boxes].every(cb=>cb.checked);

            boxes.forEach(cb=>cb.checked=!allChecked);
        },

        countModule(module){
            const boxes=document.querySelectorAll('.perm-'+module);
            const checked=[...boxes].filter(cb=>cb.checked).length;
            return checked+'/'+boxes.length;
        },

        updateCounts(){
            this.$nextTick(()=>{});
        }
    }
}
</script>

</x-app-layout>