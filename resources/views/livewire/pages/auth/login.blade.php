<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function login()
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        // ✅ Redirection propre Laravel 12
        return redirect()->intended(route('dashboard'));
    }
};
?>

<div class="w-full">

    {{-- ================= HEADER ================= --}}
    <div class="text-center mb-8">

        {{-- ✅ LOGO APPLICATION (PAS CLIENT) --}}
        <img
            src="{{ asset('images/maurierp-logo.png') }}"
            alt="MauriERP"
            class="h-16 mx-auto mb-4 object-contain"
        >

        <h1 class="text-2xl font-bold text-gray-800">
            MauriERP
        </h1>

        <p class="text-sm text-gray-500 mt-1">
            Connectez-vous à votre espace sécurisé
        </p>

    </div>


    {{-- ================= FORM ================= --}}
    <form wire:submit.prevent="login" class="space-y-6">

        {{-- EMAIL --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Adresse email
            </label>

            <input
                wire:model.defer="form.email"
                type="email"
                required
                autofocus
                autocomplete="username"
                class="w-full px-4 py-3 rounded-xl border border-gray-300
                       focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                       transition shadow-sm"
                placeholder="exemple@entreprise.com"
            >

            @error('form.email')
                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
            @enderror
        </div>


        {{-- PASSWORD --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Mot de passe
            </label>

            <input
                wire:model.defer="form.password"
                type="password"
                required
                autocomplete="current-password"
                class="w-full px-4 py-3 rounded-xl border border-gray-300
                       focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                       transition shadow-sm"
                placeholder="••••••••"
            >

            @error('form.password')
                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
            @enderror
        </div>


        {{-- REMEMBER --}}
        <div class="flex items-center text-sm">
            <label class="flex items-center gap-2 cursor-pointer">
                <input
                    wire:model="form.remember"
                    type="checkbox"
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <span class="text-gray-600">Se souvenir de moi</span>
            </label>
        </div>


        {{-- BUTTON LOGIN --}}
        <button
            type="submit"
            wire:loading.attr="disabled"
            class="w-full py-3 rounded-xl text-white font-semibold
                   bg-gradient-to-r from-indigo-600 to-indigo-700
                   hover:from-indigo-700 hover:to-indigo-800
                   shadow-lg transition duration-300
                   cursor-pointer active:scale-95
                   disabled:opacity-60 disabled:cursor-not-allowed"
        >

            {{-- NORMAL TEXT --}}
            <span wire:loading.remove wire:target="login">
                Se connecter
            </span>

            {{-- LOADING STATE --}}
            <span wire:loading wire:target="login"
                  class="flex items-center justify-center gap-2">

                <svg class="animate-spin h-5 w-5 text-white"
                     xmlns="http://www.w3.org/2000/svg"
                     fill="none"
                     viewBox="0 0 24 24">
                    <circle class="opacity-25"
                            cx="12" cy="12" r="10"
                            stroke="currentColor"
                            stroke-width="4"></circle>
                    <path class="opacity-75"
                          fill="currentColor"
                          d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>

                Connexion...
            </span>

        </button>

    </form>


    {{-- ================= FOOTER ================= --}}
    <div class="mt-8 text-center text-xs text-gray-400">
        © {{ date('Y') }} MauriERP — Propulsé par Bacary Camara
    </div>

</div>