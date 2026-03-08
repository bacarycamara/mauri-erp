@props([
    'user' => null,
    'roles' => []
])

<div class="space-y-6">

    {{-- NAME --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Nom complet
        </label>

        <div class="relative">
            <x-heroicon-o-user class="w-4 h-4 absolute left-3 top-3 text-gray-400"/>
            <input type="text"
                   name="name"
                   value="{{ old('name', $user->name ?? '') }}"
                   class="w-full pl-9 pr-3 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500"
                   required>
        </div>

        @error('name')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>


    {{-- EMAIL --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Email
        </label>

        <div class="relative">
            <x-heroicon-o-envelope class="w-4 h-4 absolute left-3 top-3 text-gray-400"/>
            <input type="email"
                   name="email"
                   value="{{ old('email', $user->email ?? '') }}"
                   class="w-full pl-9 pr-3 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500"
                   required>
        </div>

        @error('email')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>


    {{-- PHONE --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Téléphone
        </label>

        <div class="relative">
            <x-heroicon-o-phone class="w-4 h-4 absolute left-3 top-3 text-gray-400"/>
            <input type="text"
                   name="phone"
                   value="{{ old('phone', $user->phone ?? '') }}"
                   class="w-full pl-9 pr-3 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
        </div>
    </div>


    {{-- ROLE --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Rôle
        </label>

        <select name="role"
                class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                required>

            <option value="">-- Sélectionner un rôle --</option>

            @foreach($roles as $role)
                <option value="{{ $role->name }}"
                    {{ old('role', $user?->roles->first()?->name) == $role->name ? 'selected' : '' }}>
                    {{ $role->name }}
                </option>
            @endforeach

        </select>

        @error('role')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>


    {{-- PASSWORD --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Mot de passe
        </label>

        <div class="relative">
            <x-heroicon-o-lock-closed class="w-4 h-4 absolute left-3 top-3 text-gray-400"/>
            <input type="password"
                   name="password"
                   class="w-full pl-9 pr-3 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500"
                   {{ $user ? '' : 'required' }}>
        </div>

        @error('password')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror

        @if($user)
            <p class="text-xs text-gray-400 mt-1">
                Laisser vide pour conserver le mot de passe actuel.
            </p>
        @endif
    </div>


    {{-- PASSWORD CONFIRMATION --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Confirmation mot de passe
        </label>

        <input type="password"
               name="password_confirmation"
               class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500">
    </div>

</div>