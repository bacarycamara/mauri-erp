<x-app-layout>

@can('edit roles')

<div class="max-w-7xl mx-auto space-y-8"
     x-data="roleEditor()"
     x-init="init()"
     x-cloak>

    {{-- ================= HEADER ================= --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-indigo-100 rounded-xl">
                <x-heroicon-o-lock-closed class="w-7 h-7 text-indigo-600"/>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Modifier le rôle</h1>
                <p class="text-sm text-gray-500">{{ e($role->name) }}</p>
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


    {{-- ================= FORM UPDATE ================= --}}
    <form action="{{ route('admin.roles.update', $role->id) }}"
          method="POST"
          x-data="{ submitting: false }"
          @submit.prevent="submitting = true; $el.submit()"
          class="space-y-8">
        @csrf
        @method('PUT')

        {{-- NOM DU RÔLE --}}
        <div class="bg-white p-6 rounded-3xl shadow border border-gray-100">
            <label for="role_name" class="block text-sm font-semibold text-gray-700 mb-3">
                Nom du rôle <span class="text-red-500">*</span>
            </label>
            <input type="text"
                   id="role_name"
                   name="name"
                   value="{{ old('name', $role->name) }}"
                   required
                   maxlength="100"
                   class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 transition
                          @error('name') border-red-400 @enderror">
            @error('name')
            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>


        {{-- ================= PERMISSIONS ================= --}}
        <div class="bg-white p-6 rounded-3xl shadow border border-gray-100 space-y-6">

            <div class="flex items-center gap-2">
                <x-heroicon-o-cog-6-tooth class="w-5 h-5 text-indigo-600"/>
                <h2 class="text-lg font-semibold text-gray-800">Permissions</h2>
            </div>

            @foreach($permissions as $module => $modulePermissions)
            @php
                // ✅ Sanitize module key pour usage Alpine/CSS
                $moduleKey = preg_replace('/[^a-zA-Z0-9_]/', '_', $module);
            @endphp

            <div class="border rounded-2xl overflow-hidden">

                {{-- MODULE HEADER --}}
                <div class="flex justify-between items-center p-4
                            bg-gray-50 hover:bg-indigo-50 cursor-pointer transition"
                     @click="toggle('{{ $moduleKey }}')">

                    <div class="flex items-center gap-3">
                        <x-heroicon-o-cube class="w-5 h-5 text-indigo-500 flex-shrink-0"/>
                        <span class="font-semibold text-gray-700 capitalize">
                            {{ str_replace('_', ' ', $module) }}
                        </span>
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="button"
                                class="text-xs text-indigo-600 font-semibold hover:underline"
                                @click.stop="toggleAll('{{ $moduleKey }}')">
                            Tout sélectionner
                        </button>

                        {{-- ✅ SVG inline — évite conflit Blade/Alpine sur :class avec heroicon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                             stroke-width="1.5" stroke="currentColor"
                             class="w-5 h-5 text-gray-400 transition-transform duration-200"
                             :class="open === '{{ $moduleKey }}' ? 'rotate-180' : 'rotate-0'">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                        </svg>
                    </div>

                </div>

                {{-- MODULE BODY --}}
                <div x-show="open === '{{ $moduleKey }}'"
                     x-transition
                     x-cloak
                     class="p-4 grid md:grid-cols-4 gap-4">

                    @foreach($modulePermissions as $permission)
                    <label class="flex items-center gap-3 bg-gray-50 px-3 py-2
                                  rounded-xl hover:bg-indigo-50 transition cursor-pointer">

                        <input type="checkbox"
                               name="permissions[]"
                               value="{{ $permission->name }}"
                               class="perm-{{ $moduleKey }} rounded border-gray-300
                                      text-indigo-600 focus:ring-indigo-500"
                               {{ in_array($permission->name, old('permissions', $rolePermissions ?? [])) ? 'checked' : '' }}>

                        <span class="text-sm text-gray-700 capitalize">
                            {{ explode(' ', $permission->name)[0] ?? $permission->name }}
                        </span>

                    </label>
                    @endforeach

                </div>

            </div>
            @endforeach

        </div>


        {{-- ================= ACTIONS ================= --}}
        <div class="flex justify-between items-center flex-wrap gap-4">

            @can('delete roles')
            @if($role->name !== 'Super Admin')
            <button type="button"
                    @click="confirmDelete = true"
                    class="inline-flex items-center gap-2 bg-red-600 text-white px-6 py-3
                           rounded-xl hover:bg-red-700 transition shadow text-sm">
                <x-heroicon-o-trash class="w-5 h-5"/>
                Supprimer
            </button>
            @else
            <span></span>
            @endif
            @endcan

            @cannot('delete roles')
            <span></span>
            @endcannot

            <button type="submit"
                    :disabled="submitting"
                    class="inline-flex items-center gap-2 bg-indigo-600 text-white px-6 py-3
                           rounded-xl hover:bg-indigo-700 transition shadow-lg text-sm
                           disabled:opacity-50 disabled:cursor-not-allowed">
                <x-heroicon-o-pencil-square class="w-5 h-5"/>
                <span x-show="!submitting">Mettre à jour</span>
                <span x-show="submitting" x-cloak>Mise à jour...</span>
            </button>

        </div>

    </form>


    {{-- ================= MODAL SUPPRESSION ================= --}}
    @can('delete roles')
    @if($role->name !== 'Super Admin')
    <div x-show="confirmDelete"
         x-transition.opacity
         x-cloak
         class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">

        <div @click.away="confirmDelete = false"
             @click.stop
             class="bg-white p-6 rounded-2xl shadow-xl w-96 space-y-4">

            <h3 class="text-lg font-semibold text-gray-800">Supprimer ce rôle ?</h3>
            <p class="text-sm text-gray-500">
                Le rôle <strong>{{ e($role->name) }}</strong> sera définitivement supprimé.
                Cette action est irréversible.
            </p>

            {{-- ✅ Form suppression séparé du form update --}}
            <form method="POST"
                  action="{{ route('admin.roles.destroy', $role->id) }}">
                @csrf
                @method('DELETE')

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button"
                            @click="confirmDelete = false"
                            class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition text-sm">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-4 py-2 rounded-xl bg-red-600 text-white hover:bg-red-700 transition text-sm">
                        Supprimer
                    </button>
                </div>

            </form>

        </div>
    </div>
    @endif
    @endcan

</div>

@else
<div class="flex flex-col items-center justify-center py-24 text-gray-400">
    <x-heroicon-o-lock-closed class="w-12 h-12 mb-4 text-gray-300"/>
    <p class="text-lg font-medium">Accès non autorisé</p>
</div>
@endcan


@push('scripts')
<script>
function roleEditor() {
    return {
        open:          null,
        confirmDelete: false,

        init() {},

        toggle(module) {
            this.open = this.open === module ? null : module;
        },

        toggleAll(module) {
            const boxes      = document.querySelectorAll('.perm-' + module);
            const allChecked = [...boxes].every(cb => cb.checked);
            boxes.forEach(cb => cb.checked = !allChecked);
        }
    }
}
</script>
@endpush

</x-app-layout>