{{-- resources/views/admin/backups/index.blade.php --}}
<x-app-layout>
<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="p-3 bg-blue-100 rounded-2xl">
                <x-heroicon-o-archive-box-arrow-down class="w-7 h-7 text-blue-600"/>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Sauvegardes</h1>
                <p class="text-gray-500 text-sm">Gestion des backups de la base de données</p>
            </div>
        </div>

        {{-- BOUTON GÉNÉRER --}}
        <form method="POST" action="{{ route('admin.backups.create') }}">
            @csrf
            <button type="submit"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-medium transition shadow">
                <x-heroicon-o-plus class="w-5 h-5"/>
                Générer un backup
            </button>
        </form>
    </div>

    {{-- ALERTES --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center gap-2">
        <x-heroicon-o-check-circle class="w-5 h-5 shrink-0"/>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center gap-2">
        <x-heroicon-o-exclamation-circle class="w-5 h-5 shrink-0"/>
        {{ session('error') }}
    </div>
    @endif

    {{-- LISTE DES BACKUPS --}}
    <div class="bg-white rounded-3xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-6 py-3 font-semibold text-gray-600">Fichier</th>
                    <th class="text-left px-6 py-3 font-semibold text-gray-600">Taille</th>
                    <th class="text-left px-6 py-3 font-semibold text-gray-600">Date</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($files as $file)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 font-medium text-gray-700 flex items-center gap-2">
                        <x-heroicon-o-document class="w-4 h-4 text-blue-400"/>
                        {{ $file['name'] }}
                    </td>
                    <td class="px-6 py-4 text-gray-500">{{ $file['size'] }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $file['created'] }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2 justify-end">
                            {{-- TÉLÉCHARGER --}}
                            <a href="{{ route('admin.backups.download', $file['name']) }}"
                               class="inline-flex items-center gap-1 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                                <x-heroicon-o-arrow-down-tray class="w-4 h-4"/>
                                Télécharger
                            </a>
                            {{-- SUPPRIMER --}}
                            <form method="POST"
                                  action="{{ route('admin.backups.destroy', $file['name']) }}"
                                  onsubmit="return confirm('Supprimer ce backup ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center gap-1 bg-red-50 text-red-600 hover:bg-red-100 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                                    <x-heroicon-o-trash class="w-4 h-4"/>
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                        <x-heroicon-o-archive-box class="w-10 h-10 mx-auto mb-2 opacity-30"/>
                        <p>Aucun backup disponible. Générez votre premier backup !</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
</x-app-layout>