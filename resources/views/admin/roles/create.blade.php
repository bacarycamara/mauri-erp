<x-app-layout>

@can('create roles')

<div class="max-w-7xl mx-auto space-y-8"
     x-data="roleBuilder()"
     x-init="init()"
     x-cloak>

    {{-- ================= HEADER ================= --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-indigo-100 rounded-xl">
                <x-heroicon-o-lock-closed class="w-7 h-7 text-indigo-600"/>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Nouveau Rôle</h1>
                <p class="text-sm text-gray-500">Définir les permissions d'accès au système ERP</p>
            </div>
        </div>
        <a href="{{ route('admin.roles.index') }}"
           class="px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-100 transition text-sm">
            Retour
        </a>
    </div>


    {{-- ================= ERREURS ================= --}}
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-2xl text-sm">
        <p class="font-semibold mb-1">Veuillez corriger les erreurs :</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif


    {{-- ================= FORM ================= --}}
    <form action="{{ route('admin.roles.store') }}"
          method="POST"
          x-data="{ submitting: false }"
          @submit.prevent="submitting = true; $el.submit()"
          class="space-y-8">
        @csrf

        {{-- NOM DU RÔLE --}}
        <div class="bg-white p-6 rounded-3xl shadow-lg border border-gray-100">
            <label for="role_name" class="block text-sm font-semibold text-gray-700 mb-3">
                Nom du rôle <span class="text-red-500">*</span>
            </label>
            <input type="text"
                   id="role_name"
                   name="name"
                   value="{{ old('name') }}"
                   required
                   maxlength="100"
                   placeholder="Ex: Responsable Commercial"
                   class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 transition
                          @error('name') border-red-400 @enderror">
            @error('name')
            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>


        {{-- ================= PERMISSIONS ================= --}}
        <div class="bg-white p-6 rounded-3xl shadow-lg border border-gray-100 space-y-6">

            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-cog-6-tooth class="w-5 h-5 text-indigo-600"/>
                    <h2 class="text-lg font-semibold text-gray-800">Permissions</h2>
                </div>
                <button type="button"
                        @click="toggleAll()"
                        class="text-xs font-semibold text-indigo-600 hover:underline">
                    <span x-text="allChecked ? 'Tout désélectionner' : 'Tout sélectionner'"></span>
                </button>
            </div>

            @foreach($permissions as $module => $modulePermissions)
            @php
                // ✅ Sanitize module name pour usage dans Alpine (évite injection JS)
                $moduleKey = preg_replace('/[^a-zA-Z0-9_]/', '_', $module);
            @endphp

            <div class="border rounded-2xl overflow-hidden">

                {{-- MODULE HEADER --}}
                <div class="flex justify-between items-center p-4
                            bg-gray-50 hover:bg-indigo-50 cursor-pointer transition"
                     @click="toggleModule('{{ $moduleKey }}')">

                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-indigo-100 rounded-lg flex-shrink-0">
                            <x-heroicon-o-cube class="w-4 h-4 text-indigo-600"/>
                        </div>
                        <span class="font-semibold text-gray-700 capitalize">
                            {{ str_replace('_', ' ', $module) }}
                        </span>
                        <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full"
                              x-text="countModule('{{ $moduleKey }}')">
                        </span>
                    </div>

                    {{-- ✅ SVG inline — évite conflit Blade/Alpine sur :class avec heroicon --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                         stroke-width="1.5" stroke="currentColor"
                         class="w-5 h-5 text-gray-400 transition-transform duration-200"
                         :class="openModule === '{{ $moduleKey }}' ? 'rotate-180' : 'rotate-0'">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                    </svg>
                </div>

                {{-- MODULE BODY --}}
                <div x-show="openModule === '{{ $moduleKey }}'"
                     x-transition
                     x-cloak
                     class="p-4 grid md:grid-cols-4 gap-3">

                    @foreach($modulePermissions as $permission)
                    <label class="group flex items-center gap-3 bg-gray-50 hover:bg-indigo-50
                                  px-3 py-2 rounded-xl cursor-pointer transition">

                        <input type="checkbox"
                               name="permissions[]"
                               value="{{ $permission->name }}"
                               {{ in_array($permission->name, old('permissions', [])) ? 'checked' : '' }}
                               class="perm perm-{{ $moduleKey }} rounded border-gray-300
                                      text-indigo-600 focus:ring-indigo-500"
                               @change="updateCounts()">

                        <span class="text-sm text-gray-700 capitalize group-hover:text-indigo-700 transition">
                            {{-- ✅ Affiche l'action uniquement (ex: "view" de "view sales") --}}
                            {{ explode(' ', $permission->name)[0] ?? $permission->name }}
                        </span>

                    </label>
                    @endforeach

                </div>
            </div>

            @endforeach

        </div>


        {{-- SUBMIT --}}
        <div class="flex justify-end">
            <button type="submit"
                    :disabled="submitting"
                    class="bg-gradient-to-r from-indigo-600 to-indigo-700
                           text-white px-8 py-3 rounded-xl shadow-lg
                           hover:shadow-xl hover:scale-105 active:scale-95
                           transition flex items-center gap-2
                           disabled:opacity-50 disabled:cursor-not-allowed disabled:scale-100">
                <x-heroicon-o-check class="w-5 h-5"/>
                <span x-show="!submitting">Créer le rôle</span>
                <span x-show="submitting" x-cloak>Création...</span>
            </button>
        </div>

    </form>

</div>

@else
<div class="flex flex-col items-center justify-center py-24 text-gray-400">
    <x-heroicon-o-lock-closed class="w-12 h-12 mb-4 text-gray-300"/>
    <p class="text-lg font-medium">Accès non autorisé</p>
</div>
@endcan


@push('scripts')
<script>
function roleBuilder() {
    return {
        openModule: null,
        allChecked: false,

        init() {
            this.updateAllCheckedState();
        },

        toggleModule(module) {
            this.openModule = this.openModule === module ? null : module;
        },

        toggleAll() {
            const boxes = document.querySelectorAll('.perm');
            const newState = !this.allChecked;
            boxes.forEach(cb => cb.checked = newState);
            this.allChecked = newState;
        },

        countModule(module) {
            const boxes   = document.querySelectorAll('.perm-' + module);
            const checked = [...boxes].filter(cb => cb.checked).length;
            return checked + '/' + boxes.length;
        },

        updateCounts() {
            this.$nextTick(() => this.updateAllCheckedState());
        },

        updateAllCheckedState() {
            const boxes = document.querySelectorAll('.perm');
            this.allChecked = boxes.length > 0 && [...boxes].every(cb => cb.checked);
        }
    }
}
</script>
@endpush

</x-app-layout>