@props([
    'user'  => null,
    'roles' => []
])

@php
    $u       = $user;
    $isEdit  = $u !== null;
    $current = old('role', $u?->roles->first()?->name ?? '');
@endphp

<div class="space-y-6">

    {{-- NOM COMPLET --}}
    <div>
        <label for="user_name"
               class="block text-sm font-medium text-gray-700 mb-1">
            Nom complet <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <x-heroicon-o-user class="w-4 h-4 absolute left-3 top-3 text-gray-400 pointer-events-none"/>
            <input type="text"
                   id="user_name"
                   name="name"
                   value="{{ old('name', $u?->name ?? '') }}"
                   required
                   maxlength="150"
                   class="w-full pl-9 pr-3 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500
                          @error('name') border-red-400 @enderror">
        </div>
        @error('name')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>


    {{-- EMAIL --}}
    <div>
        <label for="user_email"
               class="block text-sm font-medium text-gray-700 mb-1">
            Email <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <x-heroicon-o-envelope class="w-4 h-4 absolute left-3 top-3 text-gray-400 pointer-events-none"/>
            <input type="email"
                   id="user_email"
                   name="email"
                   value="{{ old('email', $u?->email ?? '') }}"
                   required
                   maxlength="200"
                   class="w-full pl-9 pr-3 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500
                          @error('email') border-red-400 @enderror">
        </div>
        @error('email')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>


    {{-- TÉLÉPHONE --}}
    <div>
        <label for="user_phone"
               class="block text-sm font-medium text-gray-700 mb-1">
            Téléphone
        </label>
        <div class="relative">
            <x-heroicon-o-phone class="w-4 h-4 absolute left-3 top-3 text-gray-400 pointer-events-none"/>
            <input type="text"
                   id="user_phone"
                   name="phone"
                   value="{{ old('phone', $u?->phone ?? '') }}"
                   maxlength="30"
                   class="w-full pl-9 pr-3 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
        </div>
    </div>


    {{-- RÔLE --}}
    <div>
        <label for="user_role"
               class="block text-sm font-medium text-gray-700 mb-1">
            Rôle <span class="text-red-500">*</span>
        </label>
        <select id="user_role"
                name="role"
                required
                class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500
                       @error('role') border-red-400 @enderror">
            <option value="">-- Sélectionner un rôle --</option>
            @foreach($roles as $role)
            <option value="{{ $role->name }}"
                    @selected($current === $role->name)>
                {{-- ✅ e() sur le nom du rôle --}}
                {{ e($role->name) }}
            </option>
            @endforeach
        </select>
        @error('role')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>


    {{-- MOT DE PASSE --}}
    <div>
        <label for="user_password"
               class="block text-sm font-medium text-gray-700 mb-1">
            Mot de passe
            @if(!$isEdit)<span class="text-red-500">*</span>@endif
        </label>
        <div class="relative">
            <x-heroicon-o-lock-closed class="w-4 h-4 absolute left-3 top-3 text-gray-400 pointer-events-none"/>
            <input type="password"
                   id="user_password"
                   name="password"
                   autocomplete="{{ $isEdit ? 'new-password' : 'new-password' }}"
                   class="w-full pl-9 pr-3 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500
                          @error('password') border-red-400 @enderror"
                   @if(!$isEdit) required @endif>
        </div>
        @error('password')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
        @if($isEdit)
        <p class="text-xs text-gray-400 mt-1">
            Laisser vide pour conserver le mot de passe actuel.
        </p>
        @endif
    </div>


    {{-- CONFIRMATION MOT DE PASSE --}}
    <div>
        <label for="user_password_confirmation"
               class="block text-sm font-medium text-gray-700 mb-1">
            Confirmation mot de passe
            @if(!$isEdit)<span class="text-red-500">*</span>@endif
        </label>
        <div class="relative">
            <x-heroicon-o-lock-closed class="w-4 h-4 absolute left-3 top-3 text-gray-400 pointer-events-none"/>
            <input type="password"
                   id="user_password_confirmation"
                   name="password_confirmation"
                   autocomplete="new-password"
                   class="w-full pl-9 pr-3 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
        </div>
    </div>

</div>