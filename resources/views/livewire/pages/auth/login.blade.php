<?php
use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();
        $this->form->authenticate();
        Session::regenerate();

        // ✅ Sans navigate:true — redirection HTTP classique
        $this->redirectIntended(route('dashboard', absolute: false));
    }
};
?>

<div class="w-full">
    <div class="text-center mb-8">
        <img src="{{ asset('images/maurierp-logo.png') }}"
             alt="MauriERP"
             class="h-16 mx-auto mb-4 object-contain"
             onerror="this.style.display='none'">
        <h1 class="text-2xl font-bold text-gray-800">MauriERP</h1>
        <p class="text-sm text-gray-500 mt-1">Connectez-vous à votre espace sécurisé</p>
    </div>

    @if(session('status'))
    <div class="mb-4 text-sm text-green-600 bg-green-50 border border-green-200 px-4 py-3 rounded-xl">
        {{ session('status') }}
    </div>
    @endif

    <form wire:submit="login" class="space-y-6">

        <div>
            <label for="login_email" class="block text-sm font-medium text-gray-700 mb-1">
                Adresse email
            </label>
            <input wire:model="form.email"
                   id="login_email"
                   type="email"
                   required
                   autofocus
                   autocomplete="username"
                   maxlength="255"
                   placeholder="exemple@entreprise.com"
                   class="w-full px-4 py-3 rounded-xl border border-gray-300
                          focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                          transition shadow-sm">
            @error('form.email')
            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="login_password" class="block text-sm font-medium text-gray-700 mb-1">
                Mot de passe
            </label>
            <input wire:model="form.password"
                   id="login_password"
                   type="password"
                   required
                   autocomplete="current-password"
                   placeholder="••••••••"
                   class="w-full px-4 py-3 rounded-xl border border-gray-300
                          focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                          transition shadow-sm">
            @error('form.password')
            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center text-sm">
            <label class="flex items-center gap-2 cursor-pointer">
                <input wire:model="form.remember"
                       id="login_remember"
                       type="checkbox"
                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <span class="text-gray-600">Se souvenir de moi</span>
            </label>
        </div>

        <button type="submit"
                wire:loading.attr="disabled"
                class="w-full py-3 rounded-xl text-white font-semibold
                       bg-gradient-to-r from-indigo-600 to-indigo-700
                       hover:from-indigo-700 hover:to-indigo-800
                       shadow-lg transition duration-300
                       cursor-pointer active:scale-95
                       disabled:opacity-60 disabled:cursor-not-allowed">
            <span wire:loading.remove wire:target="login">Se connecter</span>
            <span wire:loading wire:target="login"
                  class="flex items-center justify-center gap-2">
                <svg class="animate-spin h-5 w-5 text-white"
                     xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10"
                            stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                Connexion...
            </span>
        </button>

    </form>

    <div class="mt-8 text-center text-xs text-gray-400">
        © {{ date('Y') }} MauriERP — Propulsé par Bacary Camara
    </div>
</div>